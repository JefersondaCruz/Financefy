<?php

namespace App\Http\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model) {
        parent::__construct($model);
    }

    public function findByName(string $name): ?Category
    {
        return $this->model
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    public function index()
    {
        return Category::where(function ($q) {
            $q->whereNull('user_id')
                ->orWhere('user_id', Auth::id());
        })->orderBy('name')->get();
    }

    public function store(array $data)
    {
        $data['user_id'] = Auth::id();
        return Category::create($data);
    }

    public function destroy(string $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $category->delete();
    }
}
