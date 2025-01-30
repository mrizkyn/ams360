<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssesionResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->participant->name,
            'position' => $this->participant->position->name,
            'division' => $this->participant->division->name,
            'departement' => $this->participant->departement->name
        ];
    }
}
