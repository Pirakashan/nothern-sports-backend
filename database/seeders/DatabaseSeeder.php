<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\District;
use App\Models\Facility;
use App\Models\PricingTable;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Create Districts ──────────────────────────────────
        $vavuniya = District::create([
            'name' => 'Vavuniya',
            'address' => 'Northern Province Sports Complex, Vavuniya, Sri Lanka',
            'contact' => '+94 24 222 3456',
            'working_hours' => '6:00 AM - 9:00 PM (Mon-Sun)',
        ]);

        $kilinochchi = District::create([
            'name' => 'Kilinochchi',
            'address' => 'District Sports Complex, Kilinochchi, Sri Lanka',
            'contact' => '+94 21 222 7890',
            'working_hours' => '6:00 AM - 8:00 PM (Mon-Sat)',
        ]);

        // ── 2. Create System Admin ──────────────────────────────
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@sportscomplex.lk',
            'phone' => '+94 77 123 4567',
            'password' => 'password123',
            'role' => 'system_admin',
            'district_id' => null,
        ]);

        // ── 3. Create Sub Admins ─────────────────────────────────
        User::create([
            'name' => 'Vavuniya Sub Admin',
            'email' => 'subadmin.vavuniya@sportscomplex.lk',
            'phone' => '+94 77 234 5678',
            'password' => 'password123',
            'role' => 'sub_admin',
            'district_id' => $vavuniya->id,
        ]);

        User::create([
            'name' => 'Kilinochchi Sub Admin',
            'email' => 'subadmin.kilinochchi@sportscomplex.lk',
            'phone' => '+94 77 345 6789',
            'password' => 'password123',
            'role' => 'sub_admin',
            'district_id' => $kilinochchi->id,
        ]);

        // ── 4. Create a Test User ────────────────────────────────
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@sportscomplex.lk',
            'phone' => '+94 77 456 7890',
            'password' => 'password123',
            'role' => 'user',
            'district_id' => null,
        ]);

        // ── 5. Create Facilities (Vavuniya) ──────────────────────
        $outdoorStadium = Facility::create([
            'district_id' => $vavuniya->id,
            'name' => 'Outdoor Stadium',
            'slug' => 'outdoor-stadium',
            'description' => 'State-of-the-art outdoor stadium for team sports, athletics, and large-scale events. Features professional-grade field with spectator seating for 5,000.',
            'image' => 'sports stadium field',
        ]);

        // Define Sports for Outdoor Stadium
        $outdoorSportsNames = ['Football', 'Rugby', 'Hockey', 'Elle', 'Kabbadi', 'Athletics', 'Cricket', 'NetBall', 'Other practice', 'Judo', 'Wrestling'];
        $outdoorSports = [];
        foreach ($outdoorSportsNames as $name) {
            $outdoorSports[$name] = Sport::create([
                'district_id' => $vavuniya->id,
                'facility_id' => $outdoorStadium->id,
                'name' => $name,
            ]);
        }

        // ── Competition / Meet / Tournament ──
        $compData = [
            [
                'event' => 'Outdoor Team Sports',
                'sports' => ['Football', 'Rugby', 'Hockey', 'Elle', 'Kabbadi'],
                'sports_text' => 'Football, Rugby, Hockey, Elle, Kabbadi',
                'prices' => [1760, 2820, 2820, 5630]
            ],
            [
                'event' => 'Athletic meet/Other',
                'sports' => ['Athletics'],
                'sports_text' => 'Athletic',
                'prices' => [2110, 3520, 3520, 5630]
            ]
        ];

        foreach ($compData as $data) {
            foreach ($data['sports'] as $sName) {
                PricingTable::create([
                    'district_id' => $vavuniya->id,
                    'sport_id' => $outdoorSports[$sName]->id,
                    'type' => 'competition',
                    'event_name' => $data['event'],
                    'sports_list' => $data['sports_text'],
                    'price_per_hour' => $data['prices'][0], // Default price
                    'price_gov_schools' => $data['prices'][0],
                    'price_club_institute' => $data['prices'][1],
                    'price_intl_schools' => $data['prices'][2],
                    'price_intl' => $data['prices'][3],
                ]);
            }
        }

        // ── Practices ──
        $practiceData = [
            [
                'event' => 'Plan A',
                'sports' => ['Football', 'Rugby', 'Hockey', 'Elle', 'Cricket', 'NetBall', 'Kabbadi'],
                'sports_text' => 'Football, Rugby, Hockey, Elle, Cricket, NetBall, Kabbadi',
                'prices' => [1410, 2120, 2120, 5630]
            ],
            [
                'event' => 'Other practice',
                'sports' => ['Other practice'],
                'sports_text' => 'Other practice',
                'prices' => [1410, 2820, 2820, 7040]
            ],
            [
                'event' => 'Hall - Judo/Wrestling & Other Martial Arts and Other Practices',
                'sports' => ['Judo', 'Wrestling'],
                'sports_text' => 'Judo, Wrestling, Other practice',
                'prices' => [710, 710, 710, 710]
            ],
            [
                'event' => 'Rehearsal',
                'sports' => ['Athletics'],
                'sports_text' => 'Athletic',
                'prices' => [1410, 2820, 2820, 5630]
            ]
        ];

        foreach ($practiceData as $data) {
            foreach ($data['sports'] as $sName) {
                PricingTable::create([
                    'district_id' => $vavuniya->id,
                    'sport_id' => $outdoorSports[$sName]->id,
                    'type' => 'practice',
                    'event_name' => $data['event'],
                    'sports_list' => $data['sports_text'],
                    'price_per_hour' => $data['prices'][0],
                    'price_gov_schools' => $data['prices'][0],
                    'price_club_institute' => $data['prices'][1],
                    'price_intl_schools' => $data['prices'][2],
                    'price_intl' => $data['prices'][3],
                ]);
            }
        }

        // ── Refundable Deposits ──
        // For deposits, the image shows rows per customer type.
        // We can store this as one or more rows. Let's create one row tied to the first sport.
        PricingTable::create([
            'district_id' => $vavuniya->id,
            'sport_id' => $outdoorSports['Football']->id,
            'type' => 'refundable_deposit',
            'event_name' => 'Refundable Deposits',
            'price_per_hour' => 0,
            'price_gov_schools' => 12000,
            'price_club_institute' => 24000,
            'price_intl_schools' => 24000,
            'price_intl' => 36000,
        ]);

        // (Other facilities remain unchanged or simplified)
        // ── 5.1 Vavuniya Other Facilities ──
        $vavuniyaOtherFacilities = [
            [
                'name' => 'Swimming Pool',
                'description' => 'Olympic-sized swimming pool with 8 lanes, heated water system, and professional timing equipment.',
                'image' => 'olympic swimming pool lanes',
                'comp' => [
                    ['event' => 'Swimming/Diving Meet', 'sports' => ['Swimming', 'Diving'], 'st' => 'Swimming, Diving', 'p' => [2000, 3000, 3000, 6000]],
                    ['event' => 'Water Polo Match', 'sports' => ['Water Polo'], 'st' => 'Water Polo', 'p' => [2500, 4000, 4000, 8000]],
                ],
                'practice' => [
                    ['event' => 'Regular Practice', 'sports' => ['Swimming', 'Diving', 'Water Polo'], 'st' => 'Swimming, Diving, Water Polo', 'p' => [1500, 2500, 2500, 5000]],
                ],
                'deposit' => [5000, 10000, 10000, 20000]
            ],
            [
                'name' => 'Indoor Stadium',
                'description' => 'Multi-purpose indoor stadium with climate control, professional lighting, and sound system.',
                'image' => 'indoor basketball court arena',
                'comp' => [
                    ['event' => 'Indoor Tournaments', 'sports' => ['Basketball', 'Volleyball', 'Badminton', 'Table Tennis', 'Futsal'], 'st' => 'Basketball, Volleyball, Badminton, Table Tennis, Futsal', 'p' => [1800, 3000, 3000, 6000]],
                ],
                'practice' => [
                    ['event' => 'Team Practice', 'sports' => ['Basketball', 'Volleyball', 'Badminton', 'Table Tennis', 'Futsal'], 'st' => 'Basketball, Volleyball, Badminton, Table Tennis, Futsal', 'p' => [1000, 2000, 2000, 4000]],
                ],
                'deposit' => [8000, 16000, 16000, 32000]
            ],
            [
                'name' => 'Basketball Court',
                'description' => 'Professional outdoor basketball court with high-traction surface and adjustable hoops.',
                'image' => 'basketball court outdoor hoops',
                'comp' => [
                    ['event' => 'Basketball Games', 'sports' => ['Basketball', '3x3 Basketball'], 'st' => 'Basketball, 3x3 Basketball', 'p' => [800, 1500, 1500, 3000]],
                ],
                'practice' => [
                    ['event' => 'General Practice', 'sports' => ['Basketball', '3x3 Basketball'], 'st' => 'Basketball, 3x3 Basketball', 'p' => [500, 1000, 1000, 2000]],
                ],
                'deposit' => [3000, 6000, 6000, 12000]
            ],
        ];

        foreach ($vavuniyaOtherFacilities as $fData) {
            $facility = Facility::create([
                'district_id' => $vavuniya->id,
                'name' => $fData['name'],
                'slug' => Str::slug($fData['name']),
                'description' => $fData['description'],
                'image' => $fData['image'],
            ]);

            $sports = [];
            foreach ($fData['comp'] as $c) {
                foreach ($c['sports'] as $sName) {
                    if (!isset($sports[$sName])) {
                        $sports[$sName] = Sport::create(['district_id' => $vavuniya->id, 'facility_id' => $facility->id, 'name' => $sName]);
                    }
                    PricingTable::create([
                        'district_id' => $vavuniya->id, 'sport_id' => $sports[$sName]->id, 'type' => 'competition',
                        'event_name' => $c['event'], 'sports_list' => $c['st'], 'price_per_hour' => $c['p'][0],
                        'price_gov_schools' => $c['p'][0], 'price_club_institute' => $c['p'][1], 'price_intl_schools' => $c['p'][2], 'price_intl' => $c['p'][3]
                    ]);
                }
            }
            foreach ($fData['practice'] as $p) {
                foreach ($p['sports'] as $sName) {
                    PricingTable::create([
                        'district_id' => $vavuniya->id, 'sport_id' => $sports[$sName]->id, 'type' => 'practice',
                        'event_name' => $p['event'], 'sports_list' => $p['st'], 'price_per_hour' => $p['p'][0],
                        'price_gov_schools' => $p['p'][0], 'price_club_institute' => $p['p'][1], 'price_intl_schools' => $p['p'][2], 'price_intl' => $p['p'][3]
                    ]);
                }
            }
            PricingTable::create([
                'district_id' => $vavuniya->id, 'sport_id' => reset($sports)->id, 'type' => 'refundable_deposit',
                'event_name' => 'Refundable Deposits', 'price_per_hour' => 0,
                'price_gov_schools' => $fData['deposit'][0], 'price_club_institute' => $fData['deposit'][1], 'price_intl_schools' => $fData['deposit'][2], 'price_intl' => $fData['deposit'][3]
            ]);
        }

        // ── 6. Create Facilities (Kilinochchi) ──────────────────
        $kilinochchiFacilities = [
            [
                'name' => 'Outdoor Stadium',
                'description' => 'District-level outdoor stadium for team sports and athletics events.',
                'image' => 'sports stadium field',
                'comp' => [
                    ['event' => 'Outdoor Team Sports', 'sports' => ['Football', 'Rugby', 'Hockey', 'Elle', 'Kabbadi'], 'st' => 'Football, Rugby, Hockey, Elle, Kabbadi', 'p' => [1500, 2500, 2500, 5000]],
                    ['event' => 'Athletic meet/Other', 'sports' => ['Athletics'], 'st' => 'Athletic', 'p' => [1800, 3000, 3000, 5000]]
                ],
                'practice' => [
                    ['event' => 'Plan A Practice', 'sports' => ['Football', 'Rugby', 'Hockey', 'Elle', 'Cricket', 'NetBall', 'Kabbadi'], 'st' => 'Football, Rugby, Hockey, Elle, Cricket, NetBall, Kabbadi', 'p' => [1200, 2000, 2000, 4000]],
                ],
                'deposit' => [10000, 20000, 20000, 30000]
            ],
            [
                'name' => 'Swimming Pool',
                'description' => 'District swimming pool with multiple lanes for competitive and recreational swimming.',
                'image' => 'olympic swimming pool lanes',
                'comp' => [
                    ['event' => 'Swimming/Diving Meet', 'sports' => ['Swimming', 'Diving'], 'st' => 'Swimming, Diving', 'p' => [1800, 2800, 2800, 5500]],
                ],
                'practice' => [
                    ['event' => 'Regular Practice', 'sports' => ['Swimming', 'Diving'], 'st' => 'Swimming, Diving', 'p' => [1300, 2200, 2200, 4500]],
                ],
                'deposit' => [4000, 8000, 8000, 16000]
            ],
            [
                'name' => 'Indoor Stadium',
                'description' => 'Indoor sports facility for badminton, volleyball, and other indoor sports.',
                'image' => 'indoor basketball court arena',
                'comp' => [
                    ['event' => 'Indoor Tournaments', 'sports' => ['Basketball', 'Volleyball', 'Badminton', 'Table Tennis', 'Futsal'], 'st' => 'Basketball, Volleyball, Badminton, Table Tennis, Futsal', 'p' => [1500, 2500, 2500, 5000]],
                ],
                'practice' => [
                    ['event' => 'General Practice', 'sports' => ['Basketball', 'Volleyball', 'Badminton', 'Table Tennis', 'Futsal'], 'st' => 'Basketball, Volleyball, Badminton, Table Tennis, Futsal', 'p' => [900, 1800, 1800, 3500]],
                ],
                'deposit' => [7000, 14000, 14000, 28000]
            ],
            [
                'name' => 'Basketball Court',
                'description' => 'Outdoor basketball court with professional-grade surface.',
                'image' => 'basketball court outdoor hoops',
                'comp' => [
                    ['event' => 'Basketball Games', 'sports' => ['Basketball', '3x3 Basketball'], 'st' => 'Basketball, 3x3 Basketball', 'p' => [700, 1300, 1300, 2500]],
                ],
                'practice' => [
                    ['event' => 'Practice Sessions', 'sports' => ['Basketball', '3x3 Basketball', 'Other practice'], 'st' => 'Basketball, 3x3 Basketball, Other practice', 'p' => [450, 900, 900, 1800]],
                ],
                'deposit' => [2500, 5000, 5000, 10000]
            ],
        ];

        foreach ($kilinochchiFacilities as $fData) {
            $facility = Facility::create([
                'district_id' => $kilinochchi->id,
                'name' => $fData['name'],
                'slug' => Str::slug($fData['name']) . '-kilinochchi',
                'description' => $fData['description'],
                'image' => $fData['image'],
            ]);

            $sports = [];
            foreach ($fData['comp'] as $c) {
                foreach ($c['sports'] as $sName) {
                    if (!isset($sports[$sName])) {
                        $sports[$sName] = Sport::create(['district_id' => $kilinochchi->id, 'facility_id' => $facility->id, 'name' => $sName]);
                    }
                    PricingTable::create([
                        'district_id' => $kilinochchi->id, 'sport_id' => $sports[$sName]->id, 'type' => 'competition',
                        'event_name' => $c['event'], 'sports_list' => $c['st'], 'price_per_hour' => $c['p'][0],
                        'price_gov_schools' => $c['p'][0], 'price_club_institute' => $c['p'][1], 'price_intl_schools' => $c['p'][2], 'price_intl' => $c['p'][3]
                    ]);
                }
            }
            if (isset($fData['practice'])) {
                foreach ($fData['practice'] as $p) {
                    foreach ($p['sports'] as $sName) {
                        if (!isset($sports[$sName])) {
                            $sports[$sName] = Sport::create(['district_id' => $kilinochchi->id, 'facility_id' => $facility->id, 'name' => $sName]);
                        }
                        PricingTable::create([
                            'district_id' => $kilinochchi->id, 'sport_id' => $sports[$sName]->id, 'type' => 'practice',
                            'event_name' => $p['event'], 'sports_list' => $p['st'], 'price_per_hour' => $p['p'][0],
                            'price_gov_schools' => $p['p'][0], 'price_club_institute' => $p['p'][1], 'price_intl_schools' => $p['p'][2], 'price_intl' => $p['p'][3]
                        ]);
                    }
                }
            }
            PricingTable::create([
                'district_id' => $kilinochchi->id, 'sport_id' => reset($sports)->id, 'type' => 'refundable_deposit',
                'event_name' => 'Refundable Deposits', 'price_per_hour' => 0,
                'price_gov_schools' => $fData['deposit'][0], 'price_club_institute' => $fData['deposit'][1], 'price_intl_schools' => $fData['deposit'][2], 'price_intl' => $fData['deposit'][3]
            ]);
        }

        // ── 7. Create Sample Bookings ────────────────────────────
        $outdoorStadium = Facility::where('name', 'Outdoor Stadium')
            ->where('district_id', $vavuniya->id)
            ->first();

        $footballSport = Sport::where('name', 'Football')
            ->where('district_id', $vavuniya->id)
            ->first();

        if ($outdoorStadium && $footballSport) {
            Booking::create([
                'user_id' => $testUser->id,
                'district_id' => $vavuniya->id,
                'facility_id' => $outdoorStadium->id,
                'sport_id' => $footballSport->id,
                'organization_type' => 'Government School',
                'event_type' => 'competition',
                'booking_date' => now()->addDays(7)->format('Y-m-d'),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'price' => 1760,
                'status' => 'confirmed',
            ]);

            Booking::create([
                'user_id' => $testUser->id,
                'district_id' => $vavuniya->id,
                'facility_id' => $outdoorStadium->id,
                'sport_id' => $footballSport->id,
                'organization_type' => 'Club',
                'event_type' => 'practice',
                'booking_date' => now()->addDays(10)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'price' => 1410,
                'status' => 'pending',
            ]);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('  System Admin: admin@sportscomplex.lk / password123');
        $this->command->info('  Sub Admin (Vavuniya): subadmin.vavuniya@sportscomplex.lk / password123');
        $this->command->info('  Sub Admin (Kilinochchi): subadmin.kilinochchi@sportscomplex.lk / password123');
        $this->command->info('  Test User: user@sportscomplex.lk / password123');
    }
}
