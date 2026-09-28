<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'doctor_id', 'pet_name', 'contact',
        'date', 'time', 'symptoms', 'category',
        'status', 'prescription', 'room_id',
    ];

    public function user() {
    return $this->belongsTo(User::class);
}
  public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

public function doctor() {
return $this->belongsTo(Doctor::class, 'doctor_id');
}

}
