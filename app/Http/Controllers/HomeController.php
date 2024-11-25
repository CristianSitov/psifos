<?php

namespace App\Http\Controllers;

use App\Console\Commands\VotingResults;
use App\Models\VotingFinal;
use App\Models\VotingResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function status()
    {
        $results = VotingResult::query()
            ->select([
                DB::raw("CASE
                        WHEN voting_results.key = 'now' AND voting_results.year = '2019' THEN 'not'
                        WHEN voting_results.key = 'now' THEN voting_results.key
                        WHEN DATE_FORMAT(voting_results.key, '%Y-%m-%d') IN ('2019-11-10', '2024-11-24') THEN DATE_FORMAT(voting_results.key, '%H-%i')
                    END AS the_hour"),
                DB::raw("CASE
                        WHEN voting_results.key = 'now' AND voting_results.year = '2019' THEN 'not'
                        WHEN voting_results.key = 'now' THEN voting_results.key
                        WHEN DATE_FORMAT(voting_results.key, '%Y-%m-%d') IN ('2019-11-10', '2024-11-24') THEN DATE_FORMAT(voting_results.key, '%Y-%m-%d')
                        ELSE NULL
                    END AS eligible"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 2019 THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2019"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 2024 THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2024"),
            ])
            ->groupBy('the_hour')
            ->havingNotNull('eligible')
            ->orderBy('the_hour', 'ASC')
            ->get()
            ->map(function ($result) {
                $electorsCount2019 = 18_217_156;
                $electorsCount2024 = 18_008_480;

                $result->the_presence_2019_percent = $result->the_presence_2019 / $electorsCount2019 * 100;
                $result->the_presence_2024_percent = $result->the_presence_2024 / $electorsCount2024 * 100;

                return $result;
            });

        // find 'now' value
        $nowValue = $results->where('the_hour', 'now')->first();
        // find the first null value in 2024
        $processedResults = $results
            ->each(function ($item) use ($nowValue) {
                if ($item->the_presence_2024 === null) {
                    $item->the_presence_2024 = $nowValue->the_presence_2024;
                    $item->the_presence_2024_percent = $nowValue->the_presence_2024_percent;

                    return $item;
                }
            })
            ->reject(function ($item) {
                return in_array($item->the_hour, ['now', 'not']);
            });

        $previousValue = null;
        $finals = VotingFinal::query()
            ->orderBy('votes', 'DESC')
            ->get()
            ->map(function ($item) use (&$previousValue) {
                $difference = $previousValue !== null ? $item->votes - $previousValue : null;
                $previousValue = $item->votes;
                $item->difference = abs($difference);

                return $item;
            });

        $totalSum = VotingResult::query()
            ->where('year', '=', 2024)
            ->where('key', '=', 'now')
            ->sum('LT');
        $finalSum = VotingFinal::sum('votes');

        return response()->json([
            'presence' => $processedResults,
            'finals' => $finals,
            'totals' => [
                'total' => (int) $totalSum,
                'final' => (int) $finalSum,
            ],
        ]);
    }
}
