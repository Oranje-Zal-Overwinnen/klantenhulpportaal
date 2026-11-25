<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Category::class);

        return $this->allCategories();
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        Category::create($request->validated());

        return $this->allCategories();
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return $this->allCategories();
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $ticketsCount = DB::transaction(function () use ($category) {
            $lockedCategory = Category::query()
                ->whereKey($category->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $ticketsCount = $lockedCategory->tickets()->count();

            if ($ticketsCount > 0) {
                return $ticketsCount;
            }

            $lockedCategory->delete();

            return 0;
        });

        if ($ticketsCount > 0) {
            return response()->json([
                'message' => 'Deze categorie kan niet worden verwijderd omdat er nog tickets aan gekoppeld zijn.',
                'tickets_count' => $ticketsCount,
            ], 422);
        }

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
