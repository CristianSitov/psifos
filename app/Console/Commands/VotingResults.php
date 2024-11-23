<?php

namespace App\Console\Commands;

use App\Models\VotingCounty;
use App\Models\VotingHour;
use App\Models\VotingResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VotingResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch:results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ct = time();
        $hours = VotingHour::all();

        foreach ($hours as $hour) {
            $ts = $hour->key;
            $baseUrl = "https://prezenta.roaep.ro/prezidentiale24112024/data/json/simpv/presence/presence_{$ts}.json?_={$ct}";

            if ($hour->is_done === 0) {
                $this->info("Fetching data from the AEP for {$ts}...");

                $response = Http::get($baseUrl);

                if ($response->ok()) {
                    $data = collect($response->json());

                    $countiesResults = $data['county'];

                    foreach ($countiesResults as $countyResults) {
                        $countyCollection = collect($countyResults);

                        $countyData = $countyCollection->pull('county');
                        VotingCounty::updateOrCreate(
                            ['id' => $countyData['id']], // Match based on a unique column
                            $countyData // Fillable attributes
                        );

                        $ageRanges = $countyCollection->pull('age_ranges');

                        $insertableValues = $countyCollection->toArray();
                        $insertableValues = array_merge(
                            ['key' => $ts],
                            $insertableValues,
                            $ageRanges,
                        );

                        VotingResult::updateOrCreate($insertableValues);
                    }

                    $hour->update(['is_done' => 1]);
                }
            }
        }
    }
}
