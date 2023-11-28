<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CourseFillStation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Courses2:fillStation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill new station field on courses';

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
        \App\Models\Course2::fillStationID();
    }
}
