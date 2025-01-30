<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'participants_db';

    public function company()
    {
        return $this->belongsTo('App\Models\DB\Company');
    }

    public function division()
    {
        return $this->belongsTo('App\Models\DB\Division');
    }

    public function departement()
    {
        return $this->belongsTo('App\Models\DB\Departement');
    }

    public function position()
    {
        return $this->belongsTo('App\Models\DB\DbPosition', 'position_id');
    }


}
