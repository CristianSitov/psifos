<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingCounty extends Model
{
    protected $table = 'voting_counties';

    protected $fillable = [
        'id',
        'code',
        'name',
        'nce',
        'updated_at',
    ];
}
