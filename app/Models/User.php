<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table='users';
    protected $primaryKey = 'id';
    protected $hidden = ['password', 'remember_token'];
   
    public function pets()
    {
        return $this->hasMany(Pet::class,'user_id');
    }

public function favourites()
{
        return $this->belongsToMany(Pet::class, 'favourites')->withTimestamps();
}
 public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


    public function updateRememberToken($token)
    {
        $this->remember_token = $token;
        $this->save();
    }
    public function getRememberToken()
    {
        return $this->remember_token;
    }

}
