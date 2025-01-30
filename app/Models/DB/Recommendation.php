<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $connection = 'mysql2';
    protected $guarded = [];
}
