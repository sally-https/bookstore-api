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
            'return_period.value' => 'required|numeric|min:1',
            'return_period.unit' => 'required|in:1,2,3,4', // 1 for seconds, 2 for minutes, 3 for hours, 4 for days
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

        // Calculate the return deadline based on the return period
        $returnPeriod = $request->input('return_period');
        $returnDeadline = null;

        switch ($returnPeriod['unit']) {
            case '1': // Seconds
                $returnDeadline = Carbon::now()->addSeconds($returnPeriod['value']);
                break;
            case '2': // Minutes
                $returnDeadline = Carbon::now()->addMinutes($returnPeriod['value']);
                break;
            case '3': // Hours
                $returnDeadline = Carbon::now()->addHours($returnPeriod['value']);
                break;
            case '4': // Days
                $returnDeadline = Carbon::now()->addDays($returnPeriod['value']);
                break;
        }

        // Store the borrowing information in the database
        $borrow = new Borrow();
        $borrow->user_id = $request->input('user_id');
        $borrow->book_id = $book->id;
        $borrow->return_deadline = $returnDeadline;
        $borrow->created_at = Carbon::now();
        $borrow->updated_at = Carbon::now();
        $borrow->save();

        // Reduce the quantity of available books
        $book->decrement('book_quantity');

        // Optionally, you can return a response
        return response()->json(['message' => 'Book borrowed successfully'], 201);
    }
}
