<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

use App\Models\Assessment\Participant;

class Company extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function businessField()
    {
        return $this->belongsTo('App\Models\Assessment\BusinessField');
    }
}
