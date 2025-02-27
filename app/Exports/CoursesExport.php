<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class CoursesExport implements FromView
{
    use Exportable;
    protected $courses;

    public function __construct($courses)
    {
        $this->courses = $courses;
    }

    public function view(): View
    {
        return view('exports.courses_payments', [
            'courses' => $this->courses
        ]);
    }
}

