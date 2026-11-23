<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return $this->allCategories();
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return $this->allCategories();
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return $this->allCategories();
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return response()->json([
                'message' => 'Deze categorie kan niet worden verwijderd omdat er nog tickets aan gekoppeld zijn.',
                'tickets_count' => $category->tickets()->count(),
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categorie succesvol verwijderd.',
        ]);
    }

    private function allCategories()
    {
        return CategoryResource::collection(
            Category::query()
                ->orderBy('name')
                ->get()
        );
    }
}
