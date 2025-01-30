<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

use App\Models\Assessment\Company;

class Participant extends Model
{
    protected $connection = 'mysql';

    public function position(){
        return $this->belongsTo('App\Models\Assessment\Position');
    }

    public function division(){
        return $this->belongsTo('App\Models\Assessment\Division');
    }

    public function departement(){
        return $this->belongsTo('App\Models\Assessment\Departement');
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
