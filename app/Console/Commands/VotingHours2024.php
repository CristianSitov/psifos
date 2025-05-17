<?php

namespace App\Console\Commands;

use App\Models\VotingHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VotingHours2024 extends Command
{
    protected $signature = 'app:fetch:hours:2024';
    protected $description = 'Fetch hours';

    public function handle()
    {
        $session = 'prezidentiale24112024';
        $timestamp = time();
        $url = "https://prezenta.roaep.ro/{$session}/data/json/simpv/presence/hours.json?_={$timestamp}";
        $this->info('Fetching data from the AEP...');

        $response = Http::get($url);

        if ($response->ok()) {
            $data = $response->json();

            foreach ($data as $item) {
                $item['year'] = $session;

                VotingHour::updateOrCreate(
                    ['key' => $item['key'], 'year' => $session], // Match based on a unique column
                    $item // Fillable attributes
                );
            }

            $this->info('Data successfully saved to the database.');
        } else {
            $this->error('Failed to fetch data from the AEP. Status: ' . $response->status());
        }
    }
}
