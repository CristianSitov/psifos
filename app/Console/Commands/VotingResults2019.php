<?php

namespace App\Console\Commands;

use App\Models\VotingCounty;
use App\Models\VotingHour;
use App\Models\VotingResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Random\RandomException;

class VotingResults2019 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:results:2019';

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
        sleep(random_int(1, 10));

        //        $session = 'prezidentiale10112019';
        $session = 'prezidentiale24112019';
        $ct = time();
        $hours = VotingHour::query()
            ->where('year', '=', $session)
            ->where('is_done', 0)
            ->get();

        foreach ($hours as $hour) {
            sleep(random_int(1, 10));

            $ts = $hour->key;
            $baseUrl = "https://prezenta.roaep.ro/{$session}/data/presence/json/presence_AB_{$ts}.json?_={$ct}";

            if ($hour->is_done === 0) {
                $this->info("Fetching data from the AEP for {$ts}...");

                $response = Http::get($baseUrl);

                if ($response->ok()) {
                    $data = collect($response->json());

                    $countiesResults = $data['county'];

                    foreach ($countiesResults as $countyResults) {
                        $countyCollection = collect($countyResults);

                        $ageRanges = $countyCollection->pull('age_ranges');

                        $insertableValues = $countyCollection->toArray();
                        $insertableValues = array_merge(
                            ['year' => $session],
                            ['key' => $ts],
                            $insertableValues,
                            $ageRanges,
                        );

                        VotingResult::updateOrCreate(
                            ['county_id' => $countyCollection['id_county'], 'key' => $ts, 'year' => $session],
                            $insertableValues
                        );
                    }

                    if ($hour->key !== 'now') {
                        $hour->is_done = 1;
                        $hour->save();
                    }
                }
            }
        }
    }
}
