<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Mail\BlankMailer;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\Course;
use App\Models\EmailLog;
use App\Models\Monitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MailController extends AppBaseController
{
    /**
     * @OA\Post(
     *      path="/admin/mails/send",
     *      summary="Send Mail",
     *      tags={"Admin"},
     *      description="Send emails to clients and/or monitors based on provided criteria.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Request body for sending emails.",
     *          @OA\JsonContent(
     *              required={"start_date", "end_date", "subject", "body"},
     *              @OA\Property(property="start_date", type="string", format="date", description="Start date for filtering courses."),
     *              @OA\Property(property="end_date", type="string", format="date", description="End date for filtering courses."),
     *              @OA\Property(property="course_ids", type="array", description="Array of course IDs to filter courses.",
     *                  @OA\Items(type="integer")
     *              ),
     *              @OA\Property(property="subject", type="string", description="Subject of the email to send."),
     *              @OA\Property(property="body", type="string", description="Body content of the email to send."),
     *              @OA\Property(property="monitors", type="boolean", description="Flag to send emails to monitors (default is false)."),
     *              @OA\Property(property="clients", type="boolean", description="Flag to send emails to clients (default is false).")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  description="Array of email addresses that received the email.",
     *                  @OA\Items(type="string")
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean"),
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Error message if the request is invalid.",
     *              ),
     *          ),
     *      )
     * )
     */
    public function sendMail(Request $request): \Illuminate\Http\JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'body' => 'required',
            'course_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 400);
        }


        $school = $this->getSchool($request);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $courseIds = $request->input('course_ids');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $sendToMonitors = $request->input('monitors', false);
        $sendToClients = $request->input('clients', false);

        // Inicializar una lista de correos únicos
        $uniqueEmails = [];

        if ($courseIds) {
            // Buscar cursos por IDs
            $courses = Course::whereIn('id', $courseIds)->get();
        } elseif ($startDate && $endDate) {
            // Buscar cursos por rango de fechas
            $courses = Course::whereHas('courseDates', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })->get();
        } else {
            return $this->sendError('No dates or ids provided');
        }

        foreach ($courses as $course) {
            if ($sendToClients) {
                // Buscar booking_users únicos relacionados con el curso y dentro del rango de fechas
                $bookingUsers = BookingUser::whereIn('booking_id', function ($query) use ($course,
                    $startDate, $endDate) {
                    $query->select('id')
                        ->from('bookings')
                        ->where('course_id', $course->id)
                        ->whereBetween('date', [$startDate, $endDate]);
                })->distinct('client_id')->get();

                foreach ($bookingUsers as $bookingUser) {
                    $client = Client::find($bookingUser->client_id);
                    if ($client && !in_array($client->email, $uniqueEmails)) {
                        // Agregar el correo del cliente a la lista
                        $uniqueEmails[] = $client->email;
                    }
                }
            }

            if ($sendToMonitors) {
                // Buscar monitores relacionados con el curso y dentro del rango de fechas
                $monitors = Monitor::whereIn('id', function ($query) use ($course, $startDate, $endDate) {
                    $query->select('monitor_id')
                        ->from('course_subgroups')
                        ->where('course_id', $course->id)
                        ->whereNotNull('monitor_id')
                        ->whereIn('course_date_id', function ($subQuery) use ($startDate, $endDate) {
                            $subQuery->select('id')
                                ->from('course_dates')
                                ->whereBetween('date', [$startDate, $endDate]);
                        });
                })->distinct('email')->get();

                foreach ($monitors as $monitor) {
                    if (!in_array($monitor->email, $uniqueEmails)) {
                        // Agregar el correo del monitor a la lista
                        $uniqueEmails[] = $monitor->email;
                    }
                }

                // Buscar monitores en los booking users dentro del rango de fechas
                $monitorBookingUsers = BookingUser::whereIn('booking_id', function ($query)
                use ($startDate, $endDate, $course) {
                    $query->select('booking_id')
                        ->from('bookings')
                        ->where('course_id', $course->id)
                        ->whereBetween('date', [$startDate, $endDate]);
                })->whereNotNull('monitor_id')->distinct('monitor_id')->get();

                foreach ($monitorBookingUsers as $monitorUser) {
                    $monitor = Monitor::find($monitorUser->monitor_id);
                    if ($monitor && !in_array($monitor->email, $uniqueEmails)) {
                        // Agregar el correo del monitor a la lista
                        $uniqueEmails[] = $monitor->email;
                    }
                }
            }
        }

        // Enviar el correo a los correos únicos
        if (!empty($uniqueEmails)) {
            $blankMailer = new BlankMailer($subject, $body, $uniqueEmails, [], $school);
            Mail::to($uniqueEmails)->send($blankMailer);
            EmailLog::create([
                'school_id' => $school->id,
                'date' => Carbon::today(),
                'from' => 'booking@boukii.ch',
                'to' =>  implode(', ', $uniqueEmails),
                'subject' => $subject,
                'body' => $body
            ]);
            return $this->sendResponse($uniqueEmails, 'Correo enviado correctamente');
        }

        return $this->sendError('Emails not found');

    }
}
