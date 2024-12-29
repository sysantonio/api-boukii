<?php

namespace App\Traits;

use App\Models\BookingUser;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\Season;

trait Utils
{
    public function getCourseAvailability($course, $monitorsGrouped, $startDate = null, $endDate = null)
    {
        if (!$course) {
            return null; // o manejar como prefieras
        }

        $totalBookings = 0;
        $totalAvailablePlaces = 0;
        $totalPlaces = 0;
        $totalAvailableHours = 0;
        $totalPlacesHours = 0;
        $totalBookingsPlaces = 0;
        $totalBookingsHours = 0;
        $totalHours = 0;
        $totalHoursAvailable = 0;

        // Si el curso es de tipo 2, buscamos el número de monitores para el deporte del curso
        if ($course->course_type == 2 && isset($monitorsGrouped[$course->sport_id])) {
            $monitorsForSport = count($monitorsGrouped[$course->sport_id]);
        } else {
            $monitorsForSport = 1; // Si no hay monitores, consideramos al menos 1
        }

        $dates = $course->courseDates;

        if ($startDate && $endDate) {
            $dates = $dates->whereBetween('date', [$startDate, $endDate]);
        }

        // Cursos de tipo 1
        if ($course->course_type == 1) {
            if ($course->is_flexible) {
                foreach ($dates as $courseDate) {
                    foreach ($courseDate->courseSubgroups as $subgroup) {
                        $bookings = $subgroup->bookingUsers()->where('status', 1)->get();
                        $totalBookingsPlaces += $bookings->count();

                        $hoursTotalDate = $this->convertSecondsToHours(
                                $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                            ) * $subgroup->max_participants;
                        $hoursTotalBooked = $this->convertSecondsToHours(
                                $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                            ) * $totalBookingsPlaces;
                        $totalHoursAvailable = $hoursTotalDate - $hoursTotalBooked;
                        $totalAvailableHours += $totalHoursAvailable;
                        $totalPlaces += $subgroup->max_participants;
                        $totalAvailablePlaces += max(0, $subgroup->max_participants - $totalBookingsPlaces);
                    }
                }
            } else {
                // Verificar si $dates no está vacío
                if (!empty($dates) && isset($dates[0]->courseSubgroups[0])) {
                    $bookings = $course->bookingUsers()->where('status', 1)->get();
                    $totalBookingsPlaces += $bookings->count();

                    $hoursTotalDate = $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($dates[0]->hour_start, $dates[0]->hour_end)
                        ) * $dates[0]->courseSubgroups[0]->max_participants * count($dates[0]->courseSubgroups);
                    $hoursTotalBooked = $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($dates[0]->hour_start, $dates[0]->hour_end)
                        ) * $totalBookingsPlaces;
                    $totalHoursAvailable = $hoursTotalDate - $hoursTotalBooked;
                    $totalAvailableHours += $totalHoursAvailable;
                    $totalHours += $hoursTotalDate;
                    $totalPlaces += $dates[0]->courseSubgroups[0]->max_participants * count($dates[0]->courseSubgroups);
                    $totalAvailablePlaces += max(0, $totalPlaces - $totalBookingsPlaces);
                } else {
                    // Manejo de error: $dates está vacío o no tiene subgrupos
                    // Puedes registrar un log, lanzar una excepción o asignar valores por defecto.

                    $totalHours += 0;
                    $totalPlaces += 0;
                    $totalAvailablePlaces += 0;
                    $totalHoursAvailable += 0;
                }
            }


        }
        else {
            // Cursos de tipo 2

            $totalPlacesPerHour = $monitorsForSport *
                (1 / $this->convertSecondsToHours(
                    $this->convertDurationToSeconds($course->duration)));


            foreach ($dates as $courseDate) {
                $bookings = $courseDate->bookingUsers()->where('status', 1)->get();
                $totalBookings += $bookings->count();

                $nwds = MonitorNwd::where('start_date', $courseDate->date)
                    ->where('user_nwd_subtype_id', 2)
                    ->whereIn('monitor_id', collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                    ->get();

                $bookingUsers = BookingUser::whereIn('monitor_id',
                    collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                    ->where('date', $courseDate->date)->where('course_id', $course->id) ->whereHas('booking', function ($query) {
                        $query->where('status', '!=', 2); // La Booking no debe tener status 2
                    })->where('status', 1)
                    ->get();

                $bookingUsersOtherCourses = BookingUser::whereIn('monitor_id',
                    collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                    ->whereHas('booking', function ($query) {
                        $query->where('status', '!=', 2); // La Booking no debe tener status 2
                    })->where('status', 1)
                    ->where('date', $courseDate->date)->where('course_id', '!=', $course->id)->get();

                $season = Season::whereDate('start_date', '<=', $courseDate->date) // Fecha de inicio menor o igual a hoy
                ->whereDate('end_date', '>=', $courseDate->date)   // Fecha de fin mayor o igual a hoy
                ->first();
                // Si es flexible, contar los intervalos disponibles
                if ($course->is_flexible && $course->price_range) {
                    $minDuration = null;
                    foreach ($course->price_range as $price) {
                        foreach ($price as $participants => $priceValue) {

                            $priceValue = str_replace(',', '.', $priceValue);
                            if (is_numeric($priceValue)) {

                                // Obtener la duración del intervalo en segundos
                                $intervalInSeconds = $this->convertDurationRangeToSeconds($price['intervalo']);
                               // $intervalInSeconds = $this->convertDurationRangeToSeconds("30min");
                                if(!$minDuration) {
                                    $minDuration = $intervalInSeconds;
                                    $totalPlacesPerHour = $monitorsForSport *
                                        (1 /  $this->convertSecondsToHours($minDuration)) * $course->max_participants;

                                    $placesTotalDate = $totalPlacesPerHour *
                                            $this->convertSecondsToHours(
                                                $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                                            ) * $course->max_participants;


                                    $hoursTotalDate = $monitorsForSport *
                                        $this->convertSecondsToHours(
                                            $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                                        ) * $course->max_participants;

                                    $totalAvailablePlaces += $placesTotalDate;
                                    $totalPlaces += $placesTotalDate;
                                    $totalHours += $hoursTotalDate;
                                    $totalPlacesHours += $hoursTotalDate;
                                    $totalAvailableHours += $hoursTotalDate;

                                    break 2;
                                }
/*                                if (is_null($durationInSeconds) || $intervalInSeconds < $durationInSeconds) {
                                    $durationInSeconds = $intervalInSeconds;
                                }
                                $start = strtotime($courseDate->hour_min);
                                $end = strtotime($courseDate->hour_max);
                                if ($start && $end) {
                                    while ($start < $end) {
                                        $totalIntervals++;
                                        $start += $intervalInSeconds;
                                    }
                                } else {
                                    $totalIntervals = 5;
                                }*/
                                // Calcular el número de intervalos disponibles
                /*                $totalAvailablePlaces += $totalIntervals * $monitorsForSport;
                                $totalPlaces += $totalIntervals * $monitorsForSport;
                                $totalHours += $totalIntervals * $this->convertSecondsToHours($intervalInSeconds);
                                $totalAvailableHours += $totalIntervals * $this->convertSecondsToHours($intervalInSeconds);*/
                                // Romper el bucle una vez que se encuentre un precio numérico

                            }
                        }
                    }
                }
                else {

                    $placesTotalDate = $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                        ) * $totalPlacesPerHour * $course->max_participants;

                    $hoursTotalDate =  $this->convertSecondsToHours(
                        $this->convertTimeRangeToSeconds($courseDate->hour_start, $courseDate->hour_end)
                    * $monitorsForSport * $course->max_participants);

                    $totalAvailablePlaces += $placesTotalDate;
                    $totalPlaces += $placesTotalDate;
                    $totalHours += $hoursTotalDate;
                    $totalPlacesHours += $hoursTotalDate;
                    $totalAvailableHours += $hoursTotalDate;

                }

                foreach ($bookingUsers as $bookingUser) {

                    $placesTotalDate = $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($bookingUser->hour_start, $bookingUser->hour_end)
                        );
                    $hoursTotalDate =  $this->convertSecondsToHours(
                        $this->convertTimeRangeToSeconds($bookingUser->hour_start, $bookingUser->hour_end));



                    $totalPlaces -= $placesTotalDate;
                    $totalAvailablePlaces -= $placesTotalDate;
                    $totalPlacesHours -= $hoursTotalDate;
                    $totalAvailableHours -= $hoursTotalDate;
                    $totalBookingsPlaces += $placesTotalDate;
                    $totalBookingsHours += $hoursTotalDate;

                }

                foreach ($nwds as $nwd) {
                    $start =$nwd->hour_start ?? $season->hour_start;
                    $end = $nwd->end_time ?? $season->hour_end;
                    $placesTotalDate = $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($start, $end)
                        );
                    $hoursTotalDate =  $this->convertSecondsToHours(
                        $this->convertTimeRangeToSeconds($start, $end));

                    $totalPlaces -= $placesTotalDate;
                    $totalAvailablePlaces -= $placesTotalDate;
                    $totalPlacesHours -= $hoursTotalDate;
                    $totalAvailableHours -= $hoursTotalDate;
                }

                if($bookingUsersOtherCourses->count()){
                    foreach ($bookingUsersOtherCourses as $bookingUser) {
                        $placesTotalDate = $this->convertSecondsToHours(
                                $this->convertTimeRangeToSeconds($bookingUser->hour_start, $bookingUser->hour_end)
                            ) ;
                        $hoursTotalDate =  $this->convertSecondsToHours(
                            $this->convertTimeRangeToSeconds($bookingUser->hour_start, $bookingUser->hour_end));
                       // dd($placesTotalDate);
                       // $totalPlaces -= $placesTotalDate;
                        $totalAvailablePlaces -= $placesTotalDate;
                        $totalPlacesHours -= $hoursTotalDate;
                        //$totalHours -= $hoursTotalDate;
                        $totalAvailableHours -= $hoursTotalDate;
                    }
                }
            }
        }

        return [
            'total_reservations_places' => $totalBookingsPlaces,
            'total_reservations_hours' => $totalBookingsHours,
            'total_available_places' => $totalAvailablePlaces,
            'total_places' => $totalPlaces,
            'total_places_hours' => $totalPlacesHours,
            'total_available_hours' => $totalAvailableHours,
            'total_hours' => $totalHours
        ];
    }

    private function convertTimeRangeToSeconds($startTime, $endTime)
    {
        // Convertir horas a segundos
        $startSeconds = strtotime($startTime) - strtotime("TODAY");
        $endSeconds = strtotime($endTime) - strtotime("TODAY");

        // Calcular la diferencia en segundos
        $intervalInSeconds = $endSeconds - $startSeconds;

        return $intervalInSeconds;
    }


    private function convertSecondsToHours($seconds)
    {
        return $seconds / 3600;
    }

    public function getMonitorAvailabilityForCourse($course, $startDate, $endDate)
    {
        $schoolId = $course->school_id;
        $courseId = $course->id;

        // Obtener la temporada actual
        $season = Season::whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->first();

        // Obtener reservas de monitores excluyendo el curso actual
        $monitorBookings = BookingUser::where('school_id', $schoolId)
            ->whereHas('course', function ($query) use($courseId) {
                $query->where('course_id', '!=', $courseId);
            })
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })->where('status', 1)
            ->where('monitor_id', '!=', null)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Obtener bloqueos (nwds) de monitores en el mismo intervalo de tiempo
        $monitorNwds = MonitorNwd::where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->where('monitor_id', '!=', null)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        return compact('monitorBookings', 'monitorNwds', 'season');
    }


    private function convertDurationToSeconds($duration)
    {
        if (strpos($duration, 'h') !== false) {
            // Si el formato es "Xh Ymin", convertirlo a segundos
            preg_match('/(\d+)h (\d+)min/', $duration, $matches);
            $hours = intval($matches[1]);
            $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Si no hay minutos, establecer en 0
            return ($hours * 3600) + ($minutes * 60);
        } elseif (strpos($duration, 'min') !== false) {
            // Si el formato es solo "Ymin", convertirlo a segundos
            preg_match('/(\d+)min/', $duration, $matches);
            $minutes = intval($matches[1]);
            return $minutes * 60;
        } else {
            // Si el formato es "HH:mm:ss", convertirlo a segundos
            $time = explode(':', $duration);
            $hours = intval($time[0]);
            $minutes = intval($time[1]);
            $seconds = intval($time[2]);
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }
    }

    private function calculateDuration($start, $end): ?string
    {
        if ($start && $end) {
            $startTime = \Carbon\Carbon::parse($start);
            $endTime = \Carbon\Carbon::parse($end);
            return $startTime->diff($endTime)->format('%H:%I:%S');
        }
        return null;
    }

    private function convertDurationRangeToSeconds($duration)
    {
        if (strpos($duration, 'h') !== false) {
            // Si el formato es "Xh Ymin" o "Xh Ym" o "Xh", convertirlo a segundos
            preg_match('/(\d+)h(?: (\d+)(?:min|m))?/', $duration, $matches);
            if (!empty($matches[1])) {
                $hours = intval($matches[1]);
                $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Si no hay minutos, establecer en 0
                return ($hours * 3600) + ($minutes * 60);
            }
        } elseif (strpos($duration, 'min') !== false || strpos($duration, 'm') !== false) {
            // Si el formato es solo "Ymin" o "Ym", convertirlo a segundos
            preg_match('/(\d+)(?:min|m)/', $duration, $matches);
            if (!empty($matches[1])) {
                $minutes = intval($matches[1]);
                return $minutes * 60;
            }
        }

        // Si no se pudo convertir, devolver 0 segundos
        return 0;
    }

    public function getGroupedMonitors($schoolId, $monitorId = null, $sportId = null)
    {
        $totalMonitors = Monitor::with(['monitorSportsDegrees.monitorSportAuthorizedDegrees.degree'])
            ->whereHas('monitorsSchools', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->where('active_school', 1);
            })
            ->when($monitorId, function ($query) use ($monitorId) {
                $query->where('id', $monitorId);
            })
            ->when($sportId, function ($query) use ($sportId) {
                $query->whereHas('monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($sportId) {
                    $query->where('sport_id', $sportId);
                });
            })
            ->get();

        $monitorsBySport = [];

        // Itera a través de los monitores
        foreach ($totalMonitors as $monitor) {
            // Itera a través de los deportes del monitor
            foreach ($monitor->sports as $sport) {
                // Agrupa por deporte
                $monitorsBySport[$sport->id][] = $monitor;
            }
        }

        return $monitorsBySport;
    }

}
