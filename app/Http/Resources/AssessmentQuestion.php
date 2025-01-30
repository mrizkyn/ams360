<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentQuestion extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->key_behavior_id,
            'competency' => $this->keyBehavior->competence->name,
            'key_behavior' => $this->keyBehavior->description,
            'type' => $this->project->type,
            'scale' => $this->project->scale
        ];
    }
}
