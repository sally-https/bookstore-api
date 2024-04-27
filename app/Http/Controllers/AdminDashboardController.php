<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Book;
use App\Models\Borrow;
use DB;

class AdminDashboardController extends Controller
{
    public function dashboardInfo()
    {
        // Count number of users
        $userCount = User::count();

        // Count number of books
        $bookCount = Book::count();

        // Count number of borrowed books
        $borrowedBookCount = Borrow::whereNotNull('returned_at')->count();

        // Count number of books not returned after due date
        $overdueBorrowCount = Borrow::whereNull('returned_at')
            ->where('return_deadline', '<', now())
            ->count();

        // Count quantity of books available for borrow
        $availableBookQuantity = Book::sum('book_quantity') - $borrowedBookCount;

        // Find the most borrowed book
        $mostBorrowedBook = Book::leftJoin('borrows', 'books.id', '=', 'borrows.book_id')
            ->select('books.*', DB::raw('COUNT(borrows.book_id) as borrow_count'))
            ->groupBy('books.id')
            ->orderByDesc('borrow_count')
            ->first();

        // Return all data as JSON response
        return response()->json([
            'user_count' => $userCount,
            'book_count' => $bookCount,
            'borrowed_book_count' => $borrowedBookCount,
            'overdue_borrow_count' => $overdueBorrowCount,
            'available_book_quantity' => $availableBookQuantity,
            'most_borrowed_book' => $mostBorrowedBook,
        ]);
    }
}
