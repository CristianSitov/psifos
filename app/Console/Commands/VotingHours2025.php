<?php

namespace App\Console\Commands;

use App\Models\VotingHour;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class VotingHours2025 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:hours:2025';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch hours';

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle(): void
    {
        sleep(random_int(1, 10));
        logger()->info(date('Y-m-d H:i:s') . ' // Fetching voting results for 2025...');

//        $session = 'prezidentiale04052025';
        $session = 'prezidentiale18052025';
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
