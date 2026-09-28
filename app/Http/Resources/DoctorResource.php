<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'doctor_name'   => $this->doctor_name,
            'email'         => $this->email,
            'specialization'=> $this->specialization ?? null,
            'phone'         => $this->phone ?? null,
            'image'         => $this->image ?? null,
            'available_days'=> $this->available_days ?? null,
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
