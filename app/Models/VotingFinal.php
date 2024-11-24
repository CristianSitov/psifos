<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingFinal extends Model
{
    protected $table = 'voting_finals';

    protected $fillable = [
        'candidate',
        'votes',
    ];
}
