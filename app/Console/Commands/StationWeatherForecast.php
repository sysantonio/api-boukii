<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StationWeatherForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Station:weatherForecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get weather forecast at all Stations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Models\Station::downloadAllAccuweatherData();
    }
}
