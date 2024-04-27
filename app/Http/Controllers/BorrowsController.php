<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Borrow;
use App\Models\Book;
use Carbon\Carbon;

class BorrowsController extends Controller
{
    public function borrowBook(Request $request)
    {
        // Define validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'return_deadline' => 'required|date_format:Y-m-d H:i:s|after:now',
        ];

        // Create validator instance
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, continue processing the request

        // Find the book by ID
        $book = Book::findOrFail($request->input('book_id'));

        // Check if there are enough available books to borrow
        if ($book->book_quantity < 1) {
            return response()->json(['error' => 'No available books to borrow'], 422);
        }

        // Store the borrowing information in the database
        $borrow = new Borrow();
        $borrow->user_id = $request->input('user_id');
        $borrow->book_id = $book->id;
        $borrow->return_deadline = $request->input('return_deadline');
        $borrow->created_at = Carbon::now();
        $borrow->updated_at = Carbon::now();
        $borrow->save();

        // Reduce the quantity of available books
        $book->decrement('book_quantity');

        // Optionally, you can return a response
        return response()->json(['message' => 'Book borrowed successfully'], 201);
    }
}
