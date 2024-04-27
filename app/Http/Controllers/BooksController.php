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
            // Add any additional validation rules for pictures URLs if needed
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
        $bookIdentifier = uniqid();

        // Generate a barcode containing the book identifier
        $barcodePath = $this->generateBarcode($bookIdentifier);

        // Store the book information in the database
        $book = new Book();
        $book->book_name = $request->input('book_name');
        $book->book_author = $request->input('book_author'); // Assuming you'll add author to the request
        $book->book_quantity = $request->input('book_quantity');
        $book->book_barcode = $barcodePath; // Save the path to the barcode image in the database
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

        // Save the barcode image to storage
        $path = 'barcodes/' . $bookIdentifier . '.png'; // Define the path where the barcode image will be saved
        Storage::put($path, $barcode); // Save the barcode image to storage

        return $path; // Return the path to the saved barcode image
    }
}
