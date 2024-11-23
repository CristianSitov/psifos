<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingHour extends Model
{
    protected $table = 'voting_hours';

    protected $fillable = [
        'name',
        'key',
        'timestamp',
    ];
}
