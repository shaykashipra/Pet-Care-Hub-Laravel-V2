<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'pet_name'     => $this->pet_name,
            'contact'      => $this->contact,
            'date'         => $this->date,
            'time'         => $this->time,
            'symptoms'     => $this->symptoms,
            'category'     => $this->category,
            'status'       => $this->status,
            'prescription' => $this->prescription,
            'patient'      => $this->whenLoaded('patient', fn() => [
                'id'        => $this->patient->id,
                'full_name' => $this->patient->fname . ' ' . $this->patient->lname,
                'email'     => $this->patient->email,
            ]),
            'doctor'       => $this->whenLoaded('doctor', fn() => [
                'id'          => $this->doctor->id,
                'doctor_name' => $this->doctor->doctor_name,
                'email'       => $this->doctor->email,
            ]),
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
