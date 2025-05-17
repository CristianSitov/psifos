<?php

namespace App\Console\Commands;

use App\Models\VotingHour;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class VotingHours2019 extends Command
{
    protected $signature = 'app:fetch:hours:2019';
    protected $description = 'Fetch hours';

    public function handle()
    {
//        $session = 'prezidentiale10112019';
        $session = 'prezidentiale24112019';
        $timestamp = time();
        $url = "https://prezenta.roaep.ro/{$session}/data/presence/json/hours.json?_={$timestamp}";
        $this->info('Fetching data from the AEP...');

        $response = Http::get($url);

        if ($response->ok()) {
            $data = $response->json()['hours'];

            foreach ($data as $item) {
                if ($item['value'] === 'now') {
                    continue;
                }

                $item['year'] = $session;
                $item['name'] = $item['label'];
                $item['key'] = $item['value'];
                $item['timestamp'] = Carbon::createFromFormat('Y-m-d_H-i', $item['value']);

                VotingHour::updateOrCreate(
                    ['key' => $item['key'], 'year' => $session],
                    $item
                );
            }

            $this->info('Data successfully saved to the database.');
        } else {
            $this->error('Failed to fetch data from the AEP. Status: ' . $response->status());
        }
    }
}
