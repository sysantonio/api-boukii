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
        $totalHours = 0;

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

        if ($course->course_type == 1) {
            // Cursos de tipo 1
            foreach ($dates as $courseDate) {
                foreach ($courseDate->courseSubgroups as $subgroup) {
                    $bookings = $subgroup->bookingUsers()->where('status', 1)->get();
                    $totalBookings += $bookings->count();
                    $totalPlaces += $subgroup->max_participants;
                    $totalAvailablePlaces += max(0, $subgroup->max_participants - $bookings->count());
                    $nwds = MonitorNwd::where('start_date', $courseDate->date)
                        ->whereIn('monitor_id', collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                        ->get();

                    $bookingUsers = BookingUser::whereIn('monitor_id', collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                        ->where('date', $courseDate->date)->get();

                    $season = Season::whereDate('start_date', '<=', $courseDate->date) // Fecha de inicio menor o igual a hoy
                    ->whereDate('end_date', '>=', $courseDate->date)   // Fecha de fin mayor o igual a hoy
                    ->first();

                    $totalIntervals = 0;

                    foreach ($bookingUsers as $bookingUser) {
                        $start = strtotime($bookingUser->hour_start);
                        $end = strtotime($bookingUser->hour_end);
                        $durationInSeconds = $this->convertDurationToSeconds($this->calculateDuration($bookingUser->hour_start, $bookingUser->hour_end));

                        if ($start && $end) {
                            while ($start < $end) {
                                $totalIntervals++;
                                $start += $durationInSeconds;
                            }
                        } else {
                            $totalIntervals = 5;
                        }

                        $totalPlaces -= $totalIntervals;
                        $totalAvailablePlaces -= $totalIntervals;
                        $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                        $totalHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    }

                    foreach ($nwds as $nwd) {
                        $start = strtotime($nwd->start_time ?? $season->hour_start);
                        $end = strtotime($nwd->end_time ?? $season->hour_end);
                        $durationInSeconds = $this->convertDurationToSeconds($this->calculateDuration($nwd->start_time ?? $season->hour_start, $nwd->end_time ?? $season->hour_end));

                        if ($start && $end) {
                            while ($start < $end) {
                                $totalIntervals++;
                                $start += $durationInSeconds;
                            }
                        } else {
                            $totalIntervals = 5;
                        }

                        $totalPlaces -= $totalIntervals;
                        $totalAvailablePlaces -= $totalIntervals;
                        $totalHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                        $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    }
                    if($bookings->count()){
                        foreach ($bookings as $booking) {
                            $start = strtotime($booking->hour_start);
                            $end = strtotime($booking->hour_end);
                            $durationInSeconds = $this->convertDurationToSeconds(
                                $this->calculateDuration($booking->hour_start, $booking->hour_end));

                            if ($start && $end) {
                                while ($start < $end) {
                                    $totalIntervals++;
                                    $start += $durationInSeconds;
                                }
                            } else {
                                $totalIntervals = 5;
                            }

                            $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                        }
                    }
                }

            }
        } else {
            // Cursos de tipo 2
            $totalIntervals = 0;

            foreach ($dates as $courseDate) {
                $bookings = $courseDate->bookingUsers()->where('status', 1)->get();
                $totalBookings += $bookings->count();


                $nwds = MonitorNwd::where('start_date', $courseDate->date)
                    ->where('user_nwd_subtype_id', 2)
                    ->whereIn('monitor_id', collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                    ->get();

                $bookingUsers = BookingUser::whereIn('monitor_id', collect($monitorsGrouped[$course->sport_id])->pluck('id'))
                    ->where('date', $courseDate->date)->get();

                $season = Season::whereDate('start_date', '<=', $courseDate->date) // Fecha de inicio menor o igual a hoy
                ->whereDate('end_date', '>=', $courseDate->date)   // Fecha de fin mayor o igual a hoy
                ->first();
                // Si es flexible, contar los intervalos disponibles
                if ($course->is_flexible && $course->price_range) {
                    foreach ($course->price_range as $price) {
                        foreach ($price as $participants => $priceValue) {
                            $priceValue = str_replace(',', '.', $priceValue);
                            if (is_numeric($priceValue)) {
                                // Obtener la duración del intervalo en segundos
                                $intervalInSeconds = $this->convertDurationRangeToSeconds($price['intervalo']);
                                $start = strtotime($courseDate->hour_min);
                                $end = strtotime($courseDate->hour_max);
                                if ($start && $end) {
                                    while ($start < $end) {
                                        $totalIntervals++;
                                        $start += $intervalInSeconds;
                                    }
                                } else {
                                    $totalIntervals = 5;
                                }
                                // Calcular el número de intervalos disponibles
                                $totalAvailablePlaces += $totalIntervals * $monitorsForSport;
                                $totalPlaces += $totalIntervals * $monitorsForSport;
                                $totalHours += $totalIntervals * $this->convertSecondsToHours($intervalInSeconds);
                                $totalAvailableHours += $totalIntervals * $this->convertSecondsToHours($intervalInSeconds);
                                // Romper el bucle una vez que se encuentre un precio numérico
                                break 2;
                            }
                        }
                    }
                } else {
                    // Si no es flexible, calcular el número de intervalos disponibles en función de la duración del curso
                    $start = strtotime($courseDate->hour_min);
                    $end = strtotime($courseDate->hour_max);
                    $durationInSeconds = $this->convertDurationToSeconds($course->duration); // Convertir la duración a segundos
                    if ($start && $end) {
                        while ($start < $end) {
                            $totalIntervals++;
                            $start += $durationInSeconds;
                        }
                    } else {
                        $totalIntervals = 5;
                    }

                    $totalAvailablePlaces += $totalIntervals * $monitorsForSport;
                    $totalPlaces += $totalIntervals * $monitorsForSport;
                    $totalHours += $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    $totalAvailableHours += $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                }
                $totalIntervals = 0;
                foreach ($bookingUsers as $bookingUser) {
                    $start = strtotime($bookingUser->hour_start);
                    $end = strtotime($bookingUser->hour_end);
                    $durationInSeconds = $this->convertDurationToSeconds($this->calculateDuration($bookingUser->hour_start, $bookingUser->hour_end));

                    if ($start && $end) {
                        while ($start < $end) {
                            $totalIntervals++;
                            $start += $durationInSeconds;
                        }
                    } else {
                        $totalIntervals = 5;
                    }

                    $totalPlaces -= $totalIntervals;
                    $totalAvailablePlaces -= $totalIntervals;
                    $totalHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                }
                foreach ($nwds as $nwd) {
                    $start = strtotime($nwd->hour_start ?? $season->hour_start);
                    $end = strtotime($nwd->end_time ?? $season->hour_end);
                    $durationInSeconds = $this->convertDurationToSeconds($this->calculateDuration($nwd->start_time ?? $season->hour_start, $nwd->end_time ?? $season->hour_end));

                    if ($start && $end) {
                        while ($start < $end) {
                            $totalIntervals++;
                            $start += $durationInSeconds;
                        }
                    } else {
                        $totalIntervals = 5;
                    }

                    $totalPlaces -= $totalIntervals;
                    $totalAvailablePlaces -= $totalIntervals;
                    $totalHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                }

                if($bookings->count()){
                    foreach ($bookings as $booking) {
                        $start = strtotime($booking->hour_start);
                        $end = strtotime($booking->hour_end);
                        $durationInSeconds = $this->convertDurationToSeconds(
                            $this->calculateDuration($booking->hour_start, $booking->hour_end));

                        if ($start && $end) {
                            while ($start < $end) {
                                $totalIntervals++;
                                $start += $durationInSeconds;
                            }
                        } else {
                            $totalIntervals = 5;
                        }

                        $totalAvailableHours -= $totalIntervals * $this->convertSecondsToHours($durationInSeconds);
                    }
                }
            }

            $totalAvailablePlaces = max(0, $totalAvailablePlaces - $totalBookings);
        }

        return [
            'total_reservations' => $totalBookings,
            'total_available_places' => $totalAvailablePlaces,
            'total_places' => $totalPlaces,
            'total_available_hours' => $totalAvailableHours,
            'total_hours' => $totalHours
        ];
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
            // Si el formato es "Xh Ymin" o "Xh", convertirlo a segundos
            preg_match('/(\d+)h(?: (\d+)min)?/', $duration, $matches);
            if (!empty($matches[1])) {
                $hours = intval($matches[1]);
                $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Si no hay minutos, establecer en 0
                return ($hours * 3600) + ($minutes * 60);
            }
        } elseif (strpos($duration, 'min') !== false) {
            // Si el formato es solo "Ymin", convertirlo a segundos
            preg_match('/(\d+)min/', $duration, $matches);
            if (!empty($matches[1])) {
                $minutes = intval($matches[1]);
                return $minutes * 60;
            }
        }

        // Si no se pudo convertir, devolver 0 segundos
        return 0;
    }

    public function getGroupedMonitors($schoolId)
    {
        $totalMonitors = Monitor::with(['sports', 'monitorSportsDegrees.monitorSportAuthorizedDegrees.degree'])
            ->whereHas('monitorsSchools', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->where('active_school', 1);
            })->get();

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
