<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyStudent extends Model
{
    use HasFactory;

       /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'personal_email',
        'verification_code',
        'expiration',
    ];

    /**
     * Get the user that owns the verification record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'personal_email', 'id');
    }
}
