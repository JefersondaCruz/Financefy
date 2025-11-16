<?php

namespace App\Http\Services;

use App\Http\Repositories\CategoryRepository;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepository $categoryRepository)
    {
        parent::__construct($categoryRepository);
    }
}
