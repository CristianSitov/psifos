<?php

namespace App\Console\Commands;

use App\Models\VotingHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VotingHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:hours';

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
        $url = "https://prezenta.roaep.ro/prezidentiale24112024/data/json/simpv/presence/hours.json?_={$timestamp}";
        $this->info('Fetching data from the AEP...');

        $response = Http::get($url);

        if ($response->ok()) {
            $data = collect($response->json())->sortBy('key');

            foreach ($data as $item) {
                if ($item['key'] !== 'now') {
                    VotingHour::updateOrCreate(
                        ['key' => $item['key']], // Match based on a unique column
                        $item // Fillable attributes
                    );
                }
            }

            $this->info('Data successfully saved to the database.');
        } else {
            $this->error('Failed to fetch data from the AEP. Status: ' . $response->status());
        }
    }
}
