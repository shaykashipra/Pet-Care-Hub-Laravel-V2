<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'fname'      => $this->fname,
            'lname'      => $this->lname,
            'full_name'  => $this->fname . ' ' . $this->lname,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'image'      => $this->image,
            'city'       => $this->city,
            'province'   => $this->province,
            'street'     => $this->street,
            'postal_code'=> $this->postal_code,
            'is_admin'   => (bool) $this->is_admin,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
