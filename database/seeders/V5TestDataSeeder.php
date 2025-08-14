<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\V5\Models\Season;
use App\Models\School;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseSubgroup;
use App\Models\CourseDate;
use App\Models\Monitor;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Payment;
use App\Models\Sport;
use App\Models\Degree;
use App\Models\ClientsSchool;
use App\Models\MonitorsSchool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

/**
 * V5 Professional Test Data Seeder - T1.1.1 Sprint Implementation
 * 
 * Generates comprehensive realistic Swiss ski school data for ESS Veveyse (School ID: 2)
 * - 50+ Swiss clients with realistic data
 * - 15+ ski/snowboard courses with CHF pricing
 * - 200+ bookings distributed over 6 months 
 * - 8+ monitors with specializations
 * - Realistic financial data for dashboard testing
 * 
 * This professional seeder implements the full T1.1.1 sprint requirements
 */
class V5TestDataSeeder extends Seeder
{
    private const SCHOOL_ID = 2; // ESS Veveyse
    private const CURRENT_SEASON_NAME = '2024-2025';
    private const PREVIOUS_SEASON_NAME = '2023-2024';
    
    // Enhanced Swiss realistic data
    private $swissNames = [
        'male' => [
            'Hans Müller', 'Pierre Dubois', 'Marco Rossi', 'Thomas Weber', 'François Martin',
            'Giovanni Bianchi', 'Klaus Schmidt', 'Jean-Claude Favre', 'Stefan Meier', 'Luca Ferrari',
            'Alain Roux', 'Andreas Keller', 'Fabio Romano', 'Philippe Bovey', 'Markus Graf',
            'Antonio Moretti', 'Daniel Wenger', 'Pascal Monnier', 'Roberto Silva', 'Michel Berset',
            'Olivier Gremaud', 'Nicolas Pache', 'Laurent Kolly', 'Christophe Magnin', 'Julien Castella',
            'Stéphane Rohrbasser', 'Alexandre Pochon', 'Vincent Seydoux', 'Patrice Charrière', 'Didier Egger'
        ],
        'female' => [
            'Sophie Dubois', 'Maria Rossi', 'Claudia Weber', 'Isabelle Martin', 'Anna Müller',
            'Francesca Bianchi', 'Nicole Schmidt', 'Catherine Favre', 'Julia Meier', 'Elena Ferrari',
            'Sylvie Roux', 'Barbara Keller', 'Valentina Romano', 'Chantal Bovey', 'Sandra Graf',
            'Lucia Moretti', 'Petra Wenger', 'Monique Monnier', 'Carla Silva', 'Marie Berset',
            'Nathalie Perrin', 'Sabrina Comte', 'Virginie Delay', 'Corinne Pittet', 'Amélie Dorsaz',
            'Céline Gremaud', 'Fabienne Pache', 'Véronique Kolly', 'Muriel Magnin', 'Caroline Castella'
        ]
    ];
    
    private $swissCities = [
        'Vevey' => '1800', 'Montreux' => '1820', 'Châtel-St-Denis' => '1618', 
        'Bulle' => '1630', 'Romont' => '1680', 'Fribourg' => '1700',
        'Lausanne' => '1000', 'Yverdon' => '1400', 'Payerne' => '1530',
        'Estavayer-le-Lac' => '1470', 'Gruyères' => '1663', 'Broc' => '1636'
    ];
    
    private $courseData = [
        'ski' => [
            ['name' => 'Ski débutant - Première fois sur les pistes', 'price' => 85, 'duration' => 2],
            ['name' => 'Ski intermédiaire - Perfectionnement technique', 'price' => 95, 'duration' => 2.5],
            ['name' => 'Ski avancé - Technique experte', 'price' => 110, 'duration' => 3],
            ['name' => 'Ski hors-piste - Aventure en poudreuse', 'price' => 140, 'duration' => 4],
            ['name' => 'Cours privé ski - Leçon personnalisée', 'price' => 180, 'duration' => 2],
            ['name' => 'Ski enfants (4-8 ans) - Jardin des neiges', 'price' => 75, 'duration' => 1.5],
            ['name' => 'Ski ados (9-16 ans) - Progression rapide', 'price' => 90, 'duration' => 2.5],
            ['name' => 'Ski compétition - Préparation courses', 'price' => 120, 'duration' => 3]
        ],
        'snowboard' => [
            ['name' => 'Snowboard débutant - Premiers virages', 'price' => 85, 'duration' => 2],
            ['name' => 'Snowboard freestyle - Park et tricks', 'price' => 100, 'duration' => 2.5],
            ['name' => 'Snowboard freeride - Hors-piste', 'price' => 130, 'duration' => 3.5],
            ['name' => 'Cours privé snowboard - Coaching personnel', 'price' => 170, 'duration' => 2],
            ['name' => 'Snowboard enfants - Apprentissage ludique', 'price' => 80, 'duration' => 1.5],
            ['name' => 'Snowboard perfectionnement - Technique avancée', 'price' => 105, 'duration' => 3],
            ['name' => 'Snowboard cross - Style dynamique', 'price' => 115, 'duration' => 2.5]
        ]
    ];
    
    private $monitorProfiles = [
        ['name' => 'Jean-Marc Pellaton', 'specialization' => 'ski', 'level' => 'expert', 'experience' => 15],
        ['name' => 'Sylvie Marthe', 'specialization' => 'ski', 'level' => 'expert', 'experience' => 12],
        ['name' => 'David Crausaz', 'specialization' => 'snowboard', 'level' => 'expert', 'experience' => 10],
        ['name' => 'Natacha Overney', 'specialization' => 'both', 'level' => 'expert', 'experience' => 18],
        ['name' => 'Marc Gremaud', 'specialization' => 'ski', 'level' => 'advanced', 'experience' => 8],
        ['name' => 'Caroline Ducrest', 'specialization' => 'ski', 'level' => 'advanced', 'experience' => 6],
        ['name' => 'Fabien Monney', 'specialization' => 'snowboard', 'level' => 'advanced', 'experience' => 7],
        ['name' => 'Virginie Repond', 'specialization' => 'both', 'level' => 'advanced', 'experience' => 9],
        ['name' => 'Olivier Bays', 'specialization' => 'ski', 'level' => 'intermediate', 'experience' => 4],
        ['name' => 'Sarah Kolly', 'specialization' => 'snowboard', 'level' => 'intermediate', 'experience' => 3]
    ];

    public function run()
    {
        $this->command->info('🎿 V5 Professional Test Data Seeder - T1.1.1 Sprint');
        $this->command->info('📊 Generating comprehensive data for ESS Veveyse...');
        
        DB::beginTransaction();
        
        try {
            // Verify prerequisites
            $this->verifyPrerequisites();
            
            // Create seasons
            $currentSeason = $this->createSeasons();
            $this->command->info('✅ Seasons created');
            
            // Create 50+ Swiss clients
            $clients = $this->createProfessionalClients(55);
            $this->command->info("✅ Created {$clients->count()} Swiss clients");
            
            // Create 8+ professional monitors
            $monitors = $this->createProfessionalMonitors();
            $this->command->info("✅ Created {$monitors->count()} monitors with specializations");
            
            // Create 15+ courses with CHF pricing
            $courses = $this->createProfessionalCourses();
            $this->command->info("✅ Created {$courses->count()} courses with Swiss pricing");
            
            // Generate 200+ bookings over 6 months
            $bookings = $this->createRealisticBookings($clients, $courses, 220);
            $this->command->info("✅ Generated {$bookings->count()} bookings over 6 months");
            
            // Generate financial data
            $this->generateFinancialData($bookings);
            $this->command->info('✅ Financial data generated');
            
            // Display comprehensive summary
            $this->displayProfessionalSummary($currentSeason);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    private function verifyPrerequisites()
    {
        $school = School::find(self::SCHOOL_ID);
        if (!$school) {
            throw new \Exception('School ID 2 (ESS Veveyse) not found!');
        }
        
        // Verify sports exist
        $skiSport = Sport::where('name', 'like', '%ski%')->first();
        $snowboardSport = Sport::where('name', 'like', '%snowboard%')->first();
        
        if (!$skiSport || !$snowboardSport) {
            $this->command->warn('Sports may not be properly configured');
        }
        
        $this->command->info("✅ School verified: {$school->name}");
    }
    
    private function createSeasons()
    {
        // Current season (2024-2025)
        $currentSeason = Season::updateOrCreate([
            'school_id' => self::SCHOOL_ID,
            'name' => self::CURRENT_SEASON_NAME
        ], [
            'start_date' => '2024-12-01',
            'end_date' => '2025-04-30',
            'is_active' => true,
            'hour_start' => '08:00',
            'hour_end' => '17:00'
        ]);
        
        // Previous season for comparison
        Season::updateOrCreate([
            'school_id' => self::SCHOOL_ID,
            'name' => self::PREVIOUS_SEASON_NAME
        ], [
            'start_date' => '2023-12-01',
            'end_date' => '2024-04-30',
            'is_active' => false,
            'hour_start' => '08:00',
            'hour_end' => '17:00'
        ]);
        
        return $currentSeason;
    }
    
    private function createProfessionalClients($count)
    {
        $faker = Faker::create('fr_CH');
        $clients = collect();
        
        $allNames = array_merge($this->swissNames['male'], $this->swissNames['female']);
        $cities = array_keys($this->swissCities);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $allNames[array_rand($allNames)];
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Dupont';
            $city = $cities[array_rand($cities)];
            $postalCode = $this->swissCities[$city];
            
            $client = Client::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '@' . $faker->randomElement([
                    'gmail.com', 'bluewin.ch', 'sunrise.ch', 'swissonline.ch', 'hotmail.com',
                    'outlook.com', 'protonmail.ch', 'gmx.ch'
                ])),
                'phone' => '+41 ' . $faker->randomElement(['21', '22', '24', '26', '27', '79']) . ' ' . $faker->numerify('### ## ##'),
                'address' => $faker->streetAddress,
                'city' => $city,
                'cp' => $postalCode,
                'country' => 365, // Switzerland
                'birth_date' => $faker->dateTimeBetween('-65 years', '-4 years')->format('Y-m-d'),
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now()
            ]);
            
            // Associate client with school
            ClientsSchool::updateOrCreate([
                'client_id' => $client->id,
                'school_id' => self::SCHOOL_ID
            ], [
                'accepted_at' => $faker->dateTimeBetween('-1 year', 'now')
            ]);
            
            $clients->push($client);
        }
        
        return $clients;
    }
    
    private function createProfessionalMonitors()
    {
        $faker = Faker::create('fr_CH');
        $monitors = collect();
        
        foreach ($this->monitorProfiles as $profile) {
            $nameParts = explode(' ', $profile['name']);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Monitor';
            
            $monitor = Monitor::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '@boukii-monitors.ch'),
                'phone' => '+41 ' . $faker->randomElement(['76', '77', '78', '79']) . ' ' . $faker->numerify('### ## ##'),
                'birth_date' => $faker->dateTimeBetween('-45 years', '-20 years')->format('Y-m-d'),
                'address' => $faker->streetAddress,
                'city' => array_rand($this->swissCities),
                'cp' => $faker->randomElement(array_values($this->swissCities)),
                'country' => 365,
                'avs' => $faker->numerify('756.####.####.##'),
                'work_license' => 'B',
                'bank_details' => 'CH' . $faker->numerify('## #### #### #### ####'),
                'children' => $faker->numberBetween(0, 3),
                'created_at' => $faker->dateTimeBetween('-3 years', 'now'),
                'updated_at' => now()
            ]);
            
            // Associate monitor with school
            MonitorsSchool::updateOrCreate([
                'monitor_id' => $monitor->id,
                'school_id' => self::SCHOOL_ID
            ]);
            
            $monitors->push($monitor);
        }
        
        return $monitors;
    }
    
    private function createProfessionalCourses()
    {
        $faker = Faker::create();
        $courses = collect();
        
        // Get sports (assuming they exist)
        $skiSport = Sport::where('name', 'like', '%ski%')->first() ?? Sport::first();
        $snowboardSport = Sport::where('name', 'like', '%snowboard%')->first() ?? Sport::first();
        
        foreach ($this->courseData as $sportType => $courseList) {
            $sport = $sportType === 'ski' ? $skiSport : $snowboardSport;
            
            foreach ($courseList as $courseInfo) {
                // Generate course dates for winter season (December to April)
                $startDate = $faker->dateTimeBetween('2024-12-01', '2025-04-30')->format('Y-m-d');
                $endDate = $faker->dateTimeBetween($startDate, '2025-04-30')->format('Y-m-d');
                
                $course = Course::create([
                    'name' => $courseInfo['name'],
                    'short_description' => substr($courseInfo['name'], 0, 100),
                    'description' => $faker->paragraph(3),
                    'sport_id' => $sport ? $sport->id : 1,
                    'school_id' => self::SCHOOL_ID,
                    'price' => $courseInfo['price'],
                    'currency' => 'CHF',
                    'max_participants' => $faker->numberBetween(6, 12),
                    'course_type' => 0, // Group course
                    'is_flexible' => false,
                    'confirm_attendance' => true,
                    'active' => true,
                    'online' => true,
                    'duration' => $courseInfo['duration'],
                    'date_start' => $startDate,
                    'date_end' => $endDate,
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now()
                ]);
                
                // Create course dates for the next 6 months
                $this->createCourseDates($course, $faker);
                
                $courses->push($course);
            }
        }
        
        return $courses;
    }
    
    private function createCourseDates($course, $faker)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonths(6);
        
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create course dates on weekends
            if ($currentDate->isWeekend()) {
                CourseDate::create([
                    'course_id' => $course->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'hour_start' => $faker->randomElement(['09:00', '10:00', '14:00', '15:00']),
                    'hour_end' => $faker->randomElement(['11:00', '12:00', '16:00', '17:00']),
                    'active' => true
                ]);
            }
            
            $currentDate->addDay();
        }
    }
    
    private function createRealisticBookings($clients, $courses, $count)
    {
        $faker = Faker::create();
        $bookings = collect();
        
        for ($i = 0; $i < $count; $i++) {
            $client = $clients->random();
            $course = $courses->random();
            
            // Get a random course date
            $courseDate = CourseDate::where('course_id', $course->id)
                ->where('date', '>=', now())
                ->inRandomOrder()
                ->first();
                
            if (!$courseDate) continue;
            
            $hasInsurance = $faker->boolean(30); // 30% chance of insurance
            $insurancePrice = $hasInsurance ? $course->price * 0.1 : 0;
            $totalPrice = $course->price + $insurancePrice;
            
            $booking = Booking::create([
                'school_id' => self::SCHOOL_ID,
                'client_main_id' => $client->id,
                'price_total' => $totalPrice,
                'has_cancellation_insurance' => $hasInsurance,
                'price_cancellation_insurance' => $insurancePrice,
                'currency' => 'CHF',
                'paid_total' => $faker->boolean(80) ? $totalPrice : $faker->randomFloat(2, 0, $totalPrice),
                'paid' => $faker->boolean(75),
                'attendance' => $faker->boolean(90),
                'payrexx_refund' => false,
                'notes' => $faker->optional(0.3)->sentence(),
                'paxes' => $faker->numberBetween(1, 3),
                'status' => $faker->randomElement([0, 1]), // 0 = pending, 1 = confirmed
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now()
            ]);
            
            // Create BookingUser entry
            BookingUser::create([
                'school_id' => self::SCHOOL_ID,
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'course_id' => $course->id,
                'course_date_id' => $courseDate->id,
                'price' => $course->price,
                'currency' => 'CHF',
                'accepted' => $faker->boolean(85),
                'status' => 1,
                'created_at' => $booking->created_at,
                'updated_at' => now()
            ]);
            
            $bookings->push($booking);
        }
        
        return $bookings;
    }
    
    private function generateFinancialData($bookings)
    {
        $faker = Faker::create();
        
        foreach ($bookings as $booking) {
            if ($booking->paid && $booking->paid_total > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'school_id' => self::SCHOOL_ID,
                    'amount' => $booking->paid_total,
                    'status' => 'completed',
                    'notes' => 'Test payment - Generated by V5TestDataSeeder',
                    'payrexx_reference' => $faker->optional()->numerify('TXN-#########'),
                    'payrexx_transaction' => $faker->optional()->uuid(),
                    'created_at' => $booking->created_at,
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function displayProfessionalSummary($season)
    {
        $stats = [
            'clients' => Client::whereHas('clientsSchools', function($q) {
                $q->where('school_id', self::SCHOOL_ID);
            })->count(),
            'monitors' => Monitor::whereHas('monitorsSchools', function($q) {
                $q->where('school_id', self::SCHOOL_ID);
            })->count(),
            'courses' => Course::where('school_id', self::SCHOOL_ID)->count(),
            'bookings' => Booking::where('school_id', self::SCHOOL_ID)->count(),
            'revenue' => Booking::where('school_id', self::SCHOOL_ID)->sum('paid_total'),
            'seasons' => Season::where('school_id', self::SCHOOL_ID)->count()
        ];
        
        $this->command->info('');
        $this->command->info('🎿 === V5 PROFESSIONAL DATA SUMMARY - T1.1.1 ===');
        $this->command->info('🏫 School: ESS Veveyse (ID: ' . self::SCHOOL_ID . ')');
        $this->command->info("⛷️  Current Season: {$season->name}");
        $this->command->info('─────────────────────────────────────────────');
        $this->command->info("👥 Swiss Clients: {$stats['clients']}");
        $this->command->info("🎿 Professional Monitors: {$stats['monitors']}");
        $this->command->info("📚 Courses (Ski/Snowboard): {$stats['courses']}");
        $this->command->info("📊 Bookings (6 months): {$stats['bookings']}");
        $this->command->info("💰 Total Revenue: CHF " . number_format($stats['revenue'], 2));
        $this->command->info("📅 Seasons: {$stats['seasons']}");
        $this->command->info('─────────────────────────────────────────────');
        $this->command->info('✅ T1.1.1 Sprint Requirements COMPLETED!');
        $this->command->info('📈 Dashboard-ready with realistic Swiss data');
        $this->command->info('🎯 Ready for V5 development and testing');
        $this->command->info('');
    }
    
}