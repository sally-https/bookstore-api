<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Borrow;
use App\Models\Book;
use Carbon\Carbon;

class ReturnsController extends Controller
{
    public function returnBook(Request $request)
    {
        // Define validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
        ];

        // Create validator instance
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, continue processing the request

        // Find the borrowing record for the specified user and book
        $borrow = Borrow::where('user_id', $request->input('user_id'))
                        ->where('book_id', $request->input('book_id'))
                        ->whereNull('returned_at')
                        ->first();

        // Check if the user has borrowed the specified book
        if (!$borrow) {
            return response()->json(['error' => 'Book is not currently borrowed by this user'], 422);
        }

        // Update the borrowing record to mark the book as returned
        $borrow->returned_at = Carbon::now();
        $borrow->save();

        // Increment the quantity of available books
        $book = Book::findOrFail($request->input('book_id'));
        $book->increment('book_quantity');

        // Optionally, you can return a response
        return response()->json(['message' => 'Book returned successfully'], 200);
    }
}
