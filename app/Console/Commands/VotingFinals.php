<?php

namespace App\Console\Commands;

use App\Models\VotingCounty;
use App\Models\VotingFinal;
use App\Models\VotingHour;
use App\Models\VotingResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Random\RandomException;

class VotingFinals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:finals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws RandomException
     */
    public function handle(): void
    {
        $ct = time();

        sleep(random_int(1, 10));

        $baseUrl = "https://prezenta.roaep.ro/prezidentiale24112024/data/json/sicpv/pv/pv_aggregated.json?_=1732490497686?_={$ct}";

        $this->info("Fetching data from the AEP...");

        $response = Http::get($baseUrl);

        if ($response->ok()) {
            $data = collect($response->json());

            $resultsA = collect($data['scopes']['CNTRY']['PRSD']['RO']['candidates']);
            $resultsB = collect($data['scopes']['CNTRY']['PRSD_C']['RO']['candidates']);

            foreach ($resultsA as $k => $result) {
                VotingFinal::updateOrCreate(
                    ['candidate' => $result['candidate']],
                    [
                        'candidate' => $result['candidate'],
                        'votes' => $resultsA[$k]['votes'] + $resultsB[$k]['votes'],
                    ]
                );
            }
        }
    }
}
