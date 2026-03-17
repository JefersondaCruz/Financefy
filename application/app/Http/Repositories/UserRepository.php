<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model) {
        parent::__construct($model);
    }

    public function getByPhone($phone){
        $this->model->where('phone', $phone)->first();
    }

}
