<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index()
    {
        return $this->categoryService->index();
    }

    public function store(CreateCategoryRequest $request)
    {
        $category = $this->categoryService->store($request->validated());

        return response()->json($category, 201);
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = $this->categoryService->update($request->validated(), $id);
            return response()->json($category);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoria não encontrada ou sem permissão.'], 403);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->categoryService->destroy($id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoria não encontrada ou sem permissão.'], 403);
        }
    }
}
