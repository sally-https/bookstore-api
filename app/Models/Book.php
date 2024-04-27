<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_name', 'book_identifier', 'book_author', 'book_quantity', 'book_barcode', 'book_pictures_urls'
    ];

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
}
