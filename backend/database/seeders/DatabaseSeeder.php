<?php

namespace Database\Seeders;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SubscriptionPlanSeeder::class);

        // Platform admin (no firm)
        User::factory()->platformAdmin()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@legalcaseflow.test',
        ]);

        // A demo firm with a full staff roster
        $firm = Firm::factory()->create([
            'name' => 'Harlow & Reyes Legal',
            'plan' => 'professional',
            'seat_limit' => 15,
        ]);

        $owner = User::factory()->firmOwner()->for($firm)->create([
            'name' => 'Amara Harlow',
            'email' => 'owner@harlowreyes.test',
        ]);

        $firm->update(['owner_id' => $owner->id]);

        $lawyers = User::factory()->lawyer()->for($firm)->count(3)->create();
        User::factory()->paralegal()->for($firm)->count(2)->create();
        User::factory()->client()->for($firm)->count(5)->create();

        Client::factory()->for($firm)->count(9)->create(['created_by' => $owner->id]);
        Client::factory()->organization()->for($firm)->count(3)->create(['created_by' => $owner->id]);

        $allClients = Client::where('firm_id', $firm->id)->get();
        foreach ($allClients->take(6) as $i => $client) {
            CaseFile::factory()->forFirm($firm)->create([
                'client_id' => $client->id,
                'lead_lawyer_id' => $lawyers->random()->id,
                'status' => ['new', 'investigation', 'active', 'active', 'waiting', 'completed'][$i % 6],
                'created_by' => $owner->id,
            ]);
        }

        $this->command->info('Seeded platform admin: admin@legalcaseflow.test / Password!123');
        $this->command->info('Seeded firm owner: owner@harlowreyes.test / Password!123');
    }
}
