<?php

namespace App\Jobs;

use App\Models\CourseSubgroup;
use App\Models\BookingUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateMonitorForSubgroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Obtener todos los subgrupos con monitor_id asignado
        $subGroups = CourseSubgroup::whereNotNull('monitor_id')->get();

        foreach ($subGroups as $subGroup) {
            // Buscar todas las BookingUser relacionadas con el subgrupo
            $bookingUsers = BookingUser::where('course_subgroup_id', $subGroup->id)->get();

            foreach ($bookingUsers as $bookingUser) {
                // Actualizar el monitor_id de cada BookingUser
                $bookingUser->monitor_id = $subGroup->monitor_id;
                $bookingUser->save();
            }
        }
    }
}
