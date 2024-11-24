<?php

namespace App\Console\Commands;

use App\Models\VotingHour;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class VotingHours2019 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:hours:2019';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timestamp = time();
        $url = "https://prezenta.roaep.ro/prezidentiale10112019/data/presence/json/hours.json?_={$timestamp}";
        $this->info('Fetching data from the AEP...');

        $response = Http::get($url);

        if ($response->ok()) {
            $data = $response->json()['hours'];

            foreach ($data as $item) {
                $item['year'] = 2019;
                $item['name'] = $item['label'];
                $item['key'] = $item['value'];
                $item['timestamp'] = $item['value'] === 'now'
                    ? 'now'
                    : Carbon::createFromFormat('Y-m-d_H-i', $item['value']);

                VotingHour::updateOrCreate(
                    ['key' => $item['key']], // Match based on a unique column
                    $item // Fillable attributes
                );
            }

            $this->info('Data successfully saved to the database.');
        } else {
            $this->error('Failed to fetch data from the AEP. Status: ' . $response->status());
        }
    }
}
