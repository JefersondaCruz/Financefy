<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;

class UserService extends BaseService
{

    public function __construct(protected UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }

}
