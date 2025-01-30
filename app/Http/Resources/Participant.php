<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Participant extends JsonResource
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
            'identity_number' => $this->identity_number,
            'name' => $this->name,
            'position' => $this->position->name,
            'division' => $this->division->name,
            'departement' => $this->departement->name
        ];
    }
}
