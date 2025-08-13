<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\V5\Models\Season;
use App\Models\School;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

/**
 * V5 Professional Test Data Seeder - Simplified Version
 * 
 * Generates realistic Swiss ski school data for ESS Veveyse (School ID: 2)
 * Adapted to work with existing database structure
 * - Create seasons for School ID 2
 * - Add Swiss clients using existing structure
 * - Generate basic financial data for dashboard testing
 * 
 * Note: This is a simplified version working with existing DB structure
 */
class V5TestDataSeeder extends Seeder
{
    private const SCHOOL_ID = 2; // ESS Veveyse
    private const CURRENT_SEASON_NAME = '2024-2025';
    private const PREVIOUS_SEASON_NAME = '2023-2024';
    
    // Swiss realistic data
    private $swissNames = [
        'male' => [
            'Hans Müller', 'Pierre Dubois', 'Marco Rossi', 'Thomas Weber', 'François Martin',
            'Giovanni Bianchi', 'Klaus Schmidt', 'Jean-Claude Favre', 'Stefan Meier', 'Luca Ferrari',
            'Alain Roux', 'Andreas Keller', 'Fabio Romano', 'Philippe Bovey', 'Markus Graf',
            'Antonio Moretti', 'Daniel Wenger', 'Pascal Monnier', 'Roberto Silva', 'Michel Berset'
        ],
        'female' => [
            'Sophie Dubois', 'Maria Rossi', 'Claudia Weber', 'Isabelle Martin', 'Anna Müller',
            'Francesca Bianchi', 'Nicole Schmidt', 'Catherine Favre', 'Julia Meier', 'Elena Ferrari',
            'Sylvie Roux', 'Barbara Keller', 'Valentina Romano', 'Chantal Bovey', 'Sandra Graf',
            'Lucia Moretti', 'Petra Wenger', 'Monique Monnier', 'Carla Silva', 'Marie Berset',
            'Nathalie Perrin', 'Sabrina Comte', 'Virginie Delay', 'Corinne Pittet', 'Amélie Dorsaz'
        ]
    ];
    
    private $courseNames = [
        'ski' => [
            'Ski débutant - Première fois sur les pistes',
            'Ski intermédiaire - Perfectionnement technique',
            'Ski avancé - Technique experte',
            'Ski hors-piste - Aventure en poudreuse',
            'Cours privé ski - Leçon personnalisée',
            'Ski enfants (4-8 ans) - Jardin des neiges',
            'Ski ados (9-16 ans) - Progression rapide',
            'Ski compétition - Préparation courses'
        ],
        'snowboard' => [
            'Snowboard débutant - Premiers virages',
            'Snowboard freestyle - Park et tricks',
            'Snowboard freeride - Hors-piste',
            'Cours privé snowboard - Coaching personnel',
            'Snowboard enfants - Apprentissage ludique',
            'Snowboard perfectionnement - Technique avancée',
            'Snowboard cross - Style dynamique'
        ]
    ];
    
    private $monitorNames = [
        ['name' => 'Jean-Marc Pellaton', 'specialization' => 'ski', 'level' => 'expert'],
        ['name' => 'Sylvie Marthe', 'specialization' => 'ski', 'level' => 'expert'],
        ['name' => 'David Crausaz', 'specialization' => 'snowboard', 'level' => 'expert'],
        ['name' => 'Natacha Overney', 'specialization' => 'both', 'level' => 'expert'],
        ['name' => 'Marc Gremaud', 'specialization' => 'ski', 'level' => 'advanced'],
        ['name' => 'Caroline Ducrest', 'specialization' => 'ski', 'level' => 'advanced'],
        ['name' => 'Fabien Monney', 'specialization' => 'snowboard', 'level' => 'advanced'],
        ['name' => 'Virginie Repond', 'specialization' => 'both', 'level' => 'advanced'],
        ['name' => 'Olivier Bays', 'specialization' => 'ski', 'level' => 'intermediate'],
        ['name' => 'Sarah Kolly', 'specialization' => 'snowboard', 'level' => 'intermediate']
    ];

    public function run()
    {
        $this->command->info('🎿 Generating V5 Test Data for ESS Veveyse...');
        
        DB::beginTransaction();
        
        try {
            // Verify school exists
            $school = School::find(self::SCHOOL_ID);
            if (!$school) {
                $this->command->error('School ID 2 (ESS Veveyse) not found!');
                return;
            }
            
            $this->command->info("✅ School found: {$school->name}");
            
            // Create seasons
            $currentSeason = $this->createSeasons();
            $this->command->info('✅ Seasons created');
            
            // Create simplified clients for School ID 2
            $clients = $this->createSimplifiedClients(25);
            $this->command->info("✅ Created {$clients->count()} clients");
            
            // Display current data for dashboard testing
            $this->displayCurrentData($currentSeason);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
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
        
        // Previous season (2023-2024) for comparison data
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
    
    private function createSimplifiedClients($count)
    {
        $faker = Faker::create('fr_CH'); // Swiss French locale
        $clients = collect();
        
        // Combine male and female names
        $allNames = array_merge($this->swissNames['male'], $this->swissNames['female']);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $allNames[array_rand($allNames)];
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Dupont';
            
            // Use existing client structure: birth_date, cp (instead of postal_code), etc.
            $client = Client::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '@' . $faker->randomElement(['gmail.com', 'bluewin.ch', 'sunrise.ch', 'swissonline.ch'])),
                'phone' => '+41 ' . $faker->randomElement(['21', '22', '24', '26', '27']) . ' ' . $faker->numerify('### ## ##'),
                'address' => $faker->streetAddress,
                'city' => $faker->randomElement(['Vevey', 'Montreux', 'Lausanne', 'Fribourg', 'Bulle', 'Romont', 'Châtel-St-Denis']),
                'cp' => $faker->randomElement(['1800', '1820', '1000', '1700', '1630', '1680', '1618']),
                'country' => 365, // Switzerland ID
                'birth_date' => $faker->dateTimeBetween('-60 years', '-6 years')->format('Y-m-d'),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now()
            ]);
            
            $clients->push($client);
        }
        
        return $clients;
    }
    
    private function displayCurrentData($season)
    {
        // Count existing data to show dashboard potential
        $totalClients = DB::table('clients')->count();
        $totalSeasons = DB::table('seasons')->where('school_id', self::SCHOOL_ID)->count();
        
        $this->command->info('');
        $this->command->info('🎿 === ESS VEVEYSE DATA STATUS ===');
        $this->command->info("👥 Total Clients: {$totalClients}");
        $this->command->info("📅 Seasons for School {self::SCHOOL_ID}: {$totalSeasons}");
        $this->command->info("⛷️  Current Season: {$season->name}");
        $this->command->info("🏫 School: ESS Veveyse (ID: " . self::SCHOOL_ID . ")");
        $this->command->info('');
        $this->command->info('✅ Basic data ready for V5 Dashboard development!');
        $this->command->info('💡 Note: This seeder worked with existing DB structure');
    }
    
}