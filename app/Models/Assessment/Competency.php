<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

use App\Models\Assessment\KeyBehavior;

class Competency extends Model
{
  protected $connection = 'mysql';

  public function behavior()
  {
    return $this->hasMany(KeyBehavior::class, 'competence_id', 'id');
  }

  public function developmentSource()
  {
    return $this->hasOne('App\Models\Assessment\DevelopmentSource', 'competence_id', 'id');
  }
  
  public function developmentActivity()
  {
    return $this->hasOne('App\Models\Assessment\DevelopmentActivity', 'competence_id', 'id');
  }
}
