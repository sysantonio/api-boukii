<?php

namespace App\Exports;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CourseDetailsExport implements FromView
{
    use Exportable;

    private $courseId;

    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    public function view(): View
    {
        // Obtener el curso con datos relacionados
        $course = Course::with([
            'courseDates.courseGroups.bookingUsers.client',
            'courseDates.courseGroups.bookingUsers.bookingUserExtras.courseExtra',
            'courseDates.courseGroups.bookingUsers.booking.clientMain',
            'courseDates.courseGroups.degree',
        ])->findOrFail($this->courseId);

        // Retorna la vista con los datos
        return view('exports.course_details', compact('course'));
    }

    // Aplicar estilos básicos a la hoja de cálculo
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true); // Negrita para el encabezado
        $sheet->getStyle('A:K')->getAlignment()->setHorizontal('center'); // Centrar el texto
    }
}
