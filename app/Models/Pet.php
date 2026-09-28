<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'pet_name', 'animal_type', 'age',
        'gender', 'color', 'coat_length', 'pet_image',
        'is_adopted', 'description',
    ];

        public function characteristics()
    {
        return $this->hasMany(Characteristic::class);
    }

    
  public function users()
{ 
    return $this->belongsTo(User::class,'user_id');

}


    public function favouritedBy() {
        return $this->belongsToMany(User::class, 'favourites')->withTimestamps();
    }
}
