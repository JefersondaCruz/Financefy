<?php

namespace App\Http\Services;

use App\Http\Repositories\BaseRepository;
use App\Interfaces\Service;

abstract class BaseService implements Service
{

    public function __construct(protected BaseRepository $repository)
    {}

    public function store(array $data)
    {
        return $this->repository->store($data);
    }

    public function show(string $id)
    {
        return $this->repository->show($id);
    }

    public function update(array $data, string $id)
    {
        return $this->repository->update($data, $id);
    }

    public function index()
    {
        return $this->repository->index();
    }

    public function destroy(string $id)
    {
        $this->repository->destroy($id);
    }
}
