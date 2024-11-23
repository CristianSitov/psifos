<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Votes extends Controller
{
    public function varsta()
    {
        $results2024 = $this->getVotes();
        $results2020 = $this->getVotes('_2020');

        return view('varsta', [
            'results2024' => $results2024,
            'results2020' => $results2020,
        ]);
    }

    /**
     * @param string $year
     * @return Collection
     */
    public function getVotes(string $year = ''): Collection
    {
        $ages = range(18, 100); // Age range from 18 to 120
        $selectStatements = [];

        foreach ($ages as $age) {
            $selectStatements[] = DB::raw("SUM(`Barbati $age`) AS `man_$age`");
            $selectStatements[] = DB::raw("SUM(`Femei $age`) AS `woman_$age`");
        }

        $dbResults = DB::table("votes$year")
            ->select($selectStatements)
            ->where('UAT', '=', 'MUNICIPIUL TIMIŞOARA')
            ->groupBy('UAT')
            ->first();

        $results = collect();

        foreach ($ages as $age) {
            $results->push([
                'age' => $age,
                'votes' => $dbResults->{'man_' . $age} + $dbResults->{'woman_' . $age}
            ]);
        }

        return $results;
    }
}
