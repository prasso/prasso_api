<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

class PersonalAccessToken extends Model
{
    use HasFactory;
    use HasTimestamps;

    protected $fillable = ['user_id', 'third_party_token'];

    public function user() {
        return $this->belongsTo(User::class, 'tokenable_id');
    }
}
