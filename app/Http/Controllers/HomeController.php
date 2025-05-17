<?php

namespace App\Http\Controllers;

use App\Models\VotingFinal;
use App\Models\VotingResult;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelCte\Query\Builder;

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
     * @return Factory|View|Application|\Illuminate\View\View
     */
    public function index()
    {
        return view('home');
    }

    public function status()
    {
        $sourceQuery = VotingResult::query()
            ->select([
                DB::raw("voting_results.key AS the_hour"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 'prezidentiale10112019' THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2019_1"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 'prezidentiale24112019' THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2019_2"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 'prezidentiale24112024' THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2024_1"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 'prezidentiale04052025' THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2025_1"),
                DB::raw("CAST(SUM(CASE
                        WHEN voting_results.year = 'prezidentiale18052025' THEN voting_results.LT
                    END) AS UNSIGNED) AS the_presence_2025_2"),
            ])
            ->groupBy('the_hour')
            ->orderBy('the_hour', 'ASC');

        $processedResults = (new Builder(DB::connection()))
            ->withExpression('source', $sourceQuery)
            ->select([
                DB::raw("DATE_FORMAT(STR_TO_DATE(REPLACE(the_hour, '_', ' '), '%Y-%m-%d %H-%i'), '%W_%H-00') AS day_hour_key"),
                DB::raw("MAX(CASE WHEN the_presence_2019_1 IS NOT NULL THEN the_presence_2019_1 END) AS the_presence_2019_1"),
                DB::raw("MAX(CASE WHEN the_presence_2019_2 IS NOT NULL THEN the_presence_2019_2 END) AS the_presence_2019_2"),
                DB::raw("MAX(CASE WHEN the_presence_2024_1 IS NOT NULL THEN the_presence_2024_1 END) AS the_presence_2024_1"),
                DB::raw("MAX(CASE WHEN the_presence_2025_1 IS NOT NULL THEN the_presence_2025_1 END) AS the_presence_2025_1"),
                DB::raw("MAX(CASE WHEN the_presence_2025_2 IS NOT NULL THEN the_presence_2025_2 END) AS the_presence_2025_2"),
            ])
            ->from('source')
            ->groupBy('day_hour_key')
            ->orderByRaw("FIELD(LEFT(day_hour_key, LOCATE('_', day_hour_key)-1), 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Monday'), CAST(SUBSTRING_INDEX(day_hour_key, '_', -1) AS UNSIGNED)")
            ->havingRaw("`day_hour_key` IS NOT NULL
   AND LEFT(day_hour_key, LOCATE('_', day_hour_key) - 1) NOT IN ('Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Monday')")
//            ->ddRawSql();
            ->get()
            ->map(function ($result) {
                $electorsCount2019_1 = 18_217_156;
                $electorsCount2019_2 = 18_217_411;
                $electorsCount2024_1 = 18_008_480;
                $electorsCount2025_1 = 17_988_031;
                $electorsCount2025_2 = 17_988_218;

                $result->the_presence_2019_1_percent = is_null($result->the_presence_2019_1)
                    ? null
                    : $result->the_presence_2019_1 / $electorsCount2019_1 * 100;
                $result->the_presence_2019_2_percent = is_null($result->the_presence_2019_2)
                    ? null
                    : $result->the_presence_2019_2 / $electorsCount2019_2 * 100;
                $result->the_presence_2024_1_percent = is_null($result->the_presence_2024_1)
                    ? null
                    : $result->the_presence_2024_1 / $electorsCount2024_1 * 100;
                $result->the_presence_2025_1_percent = is_null($result->the_presence_2025_1)
                    ? null
                    : $result->the_presence_2025_1 / $electorsCount2025_1 * 100;
                $result->the_presence_2025_2_percent = is_null($result->the_presence_2025_2)
                    ? null
                    : $result->the_presence_2025_2 / $electorsCount2025_2 * 100;

                return $result;
            });

//        $previousValue = null;
//        $finals = VotingFinal::query()
//            ->orderBy('votes', 'DESC')
//            ->get()
//            ->map(function ($item) use (&$previousValue) {
//                $difference = $previousValue !== null ? $item->votes - $previousValue : null;
//                $previousValue = $item->votes;
//                $item->difference = abs($difference);
//
//                return $item;
//            });

//        $totalSum = VotingResult::query()
//            ->where('year', '=', 2025)
//            ->where('key', '=', 'now')
//            ->sum('LT');
//        $finalSum = VotingFinal::sum('votes');

        return response()->json([
            'presence' => $processedResults,
//            'finals' => $finals,
//            'totals' => [
//                'total' => (int) $totalSum,
//                'final' => (int) $finalSum,
//            ],
        ]);
    }
}
