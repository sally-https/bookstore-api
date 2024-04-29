<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Borrow;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function userDashboardInfo()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Count number of books borrowed by the user
        $borrowedBooksCount = Borrow::where('user_id', $user->id)->count();

        // Count number of books pending return by the user
        $pendingReturnCount = Borrow::where('user_id', $user->id)
            ->whereNull('returned_at')
            ->count();

        // Count number of books returned by the user
        $returnedCount = $borrowedBooksCount - $pendingReturnCount;

        // Get info of the book with the most upcoming return deadline
        $mostUpcomingDeadlineBook = Borrow::where('user_id', $user->id)
            ->whereNull('returned_at')
            ->orderBy('return_deadline', 'asc')
            ->first();


        // Return all data as JSON response
        return response()->json([
            'borrowed_books_count' => $borrowedBooksCount,
            'pending_return_count' => $pendingReturnCount,
            'returned_count' => $returnedCount,
            'most_upcoming_deadline_book' => [
                'title' => $mostUpcomingDeadlineBook ? $mostUpcomingDeadlineBook->book->book_name : null,
                'return_deadline' => $mostUpcomingDeadlineBook ? $mostUpcomingDeadlineBook->return_deadline : null,
            ],
        ]);
    }

    public function bookLendingDetails()
{
    // Get the authenticated user
    $user = Auth::user();

    // Get all books borrowed by the user
    $borrowedBooks = Borrow::where('user_id', $user->id)
        ->whereNull('returned_at')
        ->with('book')
        ->get();

    // Prepare the lending details
    $lendingDetails = $borrowedBooks->map(function ($borrow) {
        $book = $borrow->book;
        return [
            'id' => $borrow->id,
            'title' => $book->book_name,
            'borrowDate' => $borrow->created_at,
            'returnDeadline' => Carbon::parse($borrow->return_deadline)->addHour()->format('Y-m-d H:i:s'),
            'status' => $this->getBookStatus($borrow->return_deadline),
        ];
    });

    // Return the lending details as JSON response
    return response()->json($lendingDetails);
}

private function getBookStatus($returnDeadline)
{
    $today = Carbon::now();
    $deadline = Carbon::parse($returnDeadline);

    if ($today > $deadline) {
        return 'Overdue';
    } elseif ($today < $deadline) {
        return 'Pending';
    } else {
        return 'Returned';
    }
}
}
