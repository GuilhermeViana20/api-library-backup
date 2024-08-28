<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? '';

        $categories = Category::take($limit)->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validatedData);
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        $category = Category::find($category->id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $category = Category::find($category->id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category->update($validatedData);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category = Category::find($category->id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function listBooksByCategory($category_id)
    {
        $books = Book::with('categories')->get();

        $groupedBooks = [];

        foreach ($books as $book) {
            $book->categories->contains('id', $category_id);

            foreach ($book->categories as $category) {
                if ($category->id == $category_id) {
                    if (count($groupedBooks[$category->name] ?? []) < 4) {
                        $groupedBooks[$category->name][] = $book;
                    }
                }
            }
        }

        return response()->json($groupedBooks);
    }
}