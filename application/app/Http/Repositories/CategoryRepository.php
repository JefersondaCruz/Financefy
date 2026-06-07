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
        $userId = Auth::id();

        if (!$userId) {
            return $this->findGlobalByName($name);
        }

        return $this->findByNameForUser($name, $userId);
    }

    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return Category::whereNull('user_id')->orderBy('name')->get();
        }

        return $this->indexForUser($userId);
    }

    public function indexForUser(int $userId)
    {
        return Category::where(function ($q) use ($userId) {
            $q->whereNull('user_id')
                ->orWhere('user_id', $userId);
        })->orderBy('name')->get();
    }

    public function findByNameForUser(string $name, int $userId): ?Category
    {
        return $this->model
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId])
            ->first();
    }

    public function findForUser(int $id, int $userId): ?Category
    {
        return $this->model
            ->where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->first();
    }

    private function findGlobalByName(string $name): ?Category
    {
        return $this->model
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->whereNull('user_id')
            ->first();
    }

    public function store(array $data)
    {
        $data['user_id'] = Auth::id();
        return Category::create($data);
    }

    public function update(array $data, string $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $category->update($data);

        return $category->fresh();
    }

    public function destroy(string $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $category->delete();
    }
}
