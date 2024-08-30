<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('categories')->get();

        $groupedBooks = [];

        foreach ($books as $book) {
            if ($book->categories->isEmpty()) {
                if (count($groupedBooks['Sem Categoria'] ?? []) < 2) {
                    $groupedBooks['Sem Categoria'][] = $book;
                }
            } else {
                foreach ($book->categories as $category) {
                    if (count($groupedBooks[$category->name] ?? []) < 2) {
                        $groupedBooks[$category->name][] = $book;
                    }
                }
            }
        }

        return response()->json($groupedBooks);
    }    

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'authors' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'pdf' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        if ($request->hasFile('pdf')) {
            $validatedData['pdf'] = base64_encode(file_get_contents($request->file('pdf')));
        }

        $book = Book::create($validatedData);

        $bookCategory = new BookCategory();
        $bookCategory->book_id = $book->id;
        $bookCategory->category_id = $validatedData['category_id'];
        $bookCategory->save();

        return response()->json($book, 201);
    }

    public function show(Book $book)
    {
        $book = Book::find($book->id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json($book);
    }

    public function update(Request $request, Book $book)
    {
        $book = Book::find($book->id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'authors' => 'sometimes|required|string|max:255',
            'image' => 'nullable|string|max:255',
        ]);

        $book->update($validatedData);
        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        $book = Book::find($book->id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted successfully']);
    }

    // Anexa categorias ao livro
    public function attachCategories(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->categories()->attach($request->category_ids);
        return response()->json(['message' => 'Categories attached successfully']);
    }

    // Remove uma categoria do livro
    public function detachCategory($id, $categoryId)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->categories()->detach($categoryId);
        return response()->json(['message' => 'Category detached successfully']);
    }

    public function searchBooks(Request $request)
    {
        $query = $request->input('query');

        $books = Book::where('title', 'like', "%" . $query . "%")->get();

        $groupedBooks = [];

        foreach ($books as $book) {
            if ($book->categories->isEmpty()) {
                $groupedBooks['Sem Categoria'][] = $book;
            } else {
                foreach ($book->categories as $category) {
                    $categoryName = $category->name;
                    if (!isset($groupedBooks[$categoryName])) {
                        $groupedBooks[$categoryName] = [];
                    }
                    $groupedBooks[$categoryName][] = $book;
                }
            }
        }

        return response()->json($groupedBooks);
    }
}