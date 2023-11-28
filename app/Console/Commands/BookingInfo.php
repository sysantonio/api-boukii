<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BookingInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Bookings2:bookingInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'We send reservation information 24 hours before the course starts';

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
        \App\Models\Bookings2::bookingInfo24h();
    }

}