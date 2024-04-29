<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use Carbon\Carbon;
use Picqer\Barcode\BarcodeGeneratorPNG; // Import the BarcodeGeneratorPNG class
use Illuminate\Support\Facades\Storage; // Import the Storage facade

class BooksController extends Controller
{
    public function storeBook(Request $request)
{
    // Define validation rules
    $rules = [
        'book_name' => 'required|string|max:255',
        'book_quantity' => 'required|integer|min:1',
        'book_pictures_urls' => 'required|array',
    ];

    // Create validator instance
    $validator = Validator::make($request->all(), $rules);

    // Check if validation fails
    if ($validator->fails()) {
        // Return validation errors
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Validation passed, continue processing the request
    // Generate a unique identifier for the book (you can adjust this based on your needs)
    $bookIdentifier = sprintf("%04d", mt_rand(1000, 9999));

    // Generate a barcode containing the book identifier
    $barcode = $this->generateBarcode($bookIdentifier);

    // Store the book information in the database
    $book = new Book();
    $book->book_name = $request->input('book_name');
    $book->book_author = $request->input('book_author'); // Assuming you'll add author to the request
    $book->book_quantity = $request->input('book_quantity');
    $book->book_barcode = $barcode; // Store the barcode image data in the database
    $book->book_pictures_urls = json_encode($request->input('book_pictures_urls'));
    $book->book_identifier = $bookIdentifier; // Store the book identifier in the database
    $book->created_at = Carbon::now();
    $book->updated_at = Carbon::now();
    $book->save();

    // Optionally, you can return a response
    return response()->json(['message' => 'Book added successfully'], 201);
}

// Helper method to generate a barcode containing the book identifier
private function generateBarcode($bookIdentifier)
{
    $generator = new BarcodeGeneratorPNG(); // Create an instance of the BarcodeGeneratorPNG class
    $barcode = $generator->getBarcode($bookIdentifier, $generator::TYPE_CODE_128); // Generate a barcode using the book identifier

    return $barcode; // Return the barcode image data
}


public function userLibraryInfo()
{
    $books = Book::withCount('borrows')
        ->orderBy('borrows_count', 'desc')
        ->get();

    if ($books->isEmpty()) {
        return response()->json(['message' => 'No books found'], 404);
    }

    $formattedBooks = $books->map(function ($book) {
        $barcodeBase64 = null;
        if ($book->book_barcode) {
            $barcodeBase64 = base64_encode($book->book_barcode);
        }

        return [
            'id' => $book->id,
            'book_name' => $book->book_name,
            'book_identifier' => $book->book_identifier,
            'book_author' => $book->book_author,
            'book_quantity' => $book->book_quantity,
            'book_barcode' => $barcodeBase64,
            'book_pictures_urls' => json_decode($book->book_pictures_urls),
            'borrows_count' => $book->borrows_count,
        ];
    });

    // Return the books as a JSON response
    return response()->json(['books' => $formattedBooks], 200);
}
}
