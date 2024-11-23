<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingResult extends Model
{
    protected $table = 'voting_results';

    protected $fillable = [
        "key",
        "county_id",
        "initial_count_lp",
        "initial_count_lc",
        "precincts_count",
        "LP",
        "LS",
        "LSC",
        "UM",
        "LT",
        "presence",
        "medium_u",
        "medium_r",
        "men_18_24",
        "men_25_34",
        "men_35_44",
        "men_45_64",
        "men_65+",
        "women_18_24",
        "women_25_34",
        "women_35_44",
        "women_45_64",
        "women_65+",
    ];
}
