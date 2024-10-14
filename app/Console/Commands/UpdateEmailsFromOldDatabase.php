<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEmailsFromOldDatabase extends Command
{
    protected $signature = 'update:emails-from-old-db';
    protected $description = 'Actualizar correos electrónicos desde la base de datos antigua';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Obtener todos los usuarios de la base de datos antigua
        $oldUsers = DB::connection('old')->table('users')->get();

        foreach ($oldUsers as $oldUser) {
            // Extraer el prefijo del email (parte antes del @)
            $emailPrefix = explode('@', $oldUser->email)[0];

            // Buscar coincidencias en la nueva base de datos en las tablas User, Client y Monitor
            $userMatches = User::where('first_name', $oldUser->first_name)
                ->where('last_name', $oldUser->last_name)
                ->where('email', 'like', "{$emailPrefix}%")
                ->get();

            $clientMatches = Client::where('first_name', $oldUser->first_name)
                ->where('last_name', $oldUser->last_name)
                ->where('email', 'like', "{$emailPrefix}%")
                ->get();

            $monitorMatches = Monitor::where('first_name', $oldUser->first_name)
                ->where('last_name', $oldUser->last_name)
                ->where('email', 'like', "{$emailPrefix}%")
                ->get();

            // Actualizar el correo electrónico para todas las coincidencias de usuarios
            foreach ($userMatches as $userMatch) {
                $userMatch->update(['email' => $oldUser->email]);
                $this->info("Correo electrónico actualizado para User: {$userMatch->first_name} {$userMatch->last_name}");
            }

            // Actualizar el correo electrónico para todas las coincidencias de clientes
            foreach ($clientMatches as $clientMatch) {
                $clientMatch->update(['email' => $oldUser->email]);
                $this->info("Correo electrónico actualizado para Client: {$clientMatch->first_name} {$clientMatch->last_name}");
            }

            // Actualizar el correo electrónico para todas las coincidencias de monitores
            foreach ($monitorMatches as $monitorMatch) {
                $monitorMatch->update(['email' => $oldUser->email]);
                $this->info("Correo electrónico actualizado para Monitor: {$monitorMatch->first_name} {$monitorMatch->last_name}");
            }
        }

        $this->info('Actualización de correos electrónicos completada.');
    }
}

