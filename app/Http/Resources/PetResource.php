<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'pet_name'         => $this->pet_name,
            'animal_type'      => $this->animal_type,
            'age'              => $this->age,
            'gender'           => $this->gender,
            'color'            => $this->color,
            'coat_length'      => $this->coat_length,
            'pet_image'        => $this->pet_image,
            'is_adopted'       => (bool) $this->is_adopted,
            'description'      => $this->description,
            'owner'            => $this->whenLoaded('users', fn() => [
                'id'        => $this->users->id,
                'full_name' => $this->users->fname . ' ' . $this->users->lname,
                'email'     => $this->users->email,
            ]),
            'characteristics'  => $this->whenLoaded('characteristics', fn() =>
                $this->characteristics->map(fn($c) => ['id' => $c->id])
            ),
            'created_at'       => $this->created_at?->toDateTimeString(),
            'updated_at'       => $this->updated_at?->toDateTimeString(),
        ];
    }
}
