<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseCrudController extends AppBaseController
{
    protected $repository;
    protected ?string $resource = null;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    protected function makeResource($model)
    {
        if ($this->resource) {
            $class = $this->resource;
            return new $class($model);
        }

        return $model;
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->repository->all(
            $request->except(['skip','limit','search','exclude','user','perPage','order','orderColumn','page','with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->get('perPage', 10),
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($items, 'Data retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        $model = $this->repository->create($input);

        return $this->sendResponse($this->makeResource($model), 'Data saved successfully');
    }

    public function show($id, Request $request): JsonResponse
    {
        $model = $this->repository->find($id, with: $request->get('with', []));

        if (empty($model)) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($this->makeResource($model), 'Data retrieved successfully');
    }

    public function update($id, Request $request): JsonResponse
    {
        $input = $request->all();
        $model = $this->repository->find($id, with: $request->get('with', []));

        if (empty($model)) {
            return $this->sendError('Data not found');
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($this->makeResource($model), 'Data updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $model = $this->repository->find($id);

        if (empty($model)) {
            return $this->sendError('Data not found');
        }

        $model->delete();

        return $this->sendSuccess('Data deleted successfully');
    }
}
