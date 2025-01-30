<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class ItemRecommendation extends Model
{
    protected $connection = 'mysql2';
    protected $guarded = [];

    public function recommendation(){
        return $this->belongsTo('App\Models\DB\Recommendation');
    }
}
