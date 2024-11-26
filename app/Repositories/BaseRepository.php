<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     */
    abstract public function getFieldsSearchable(): array;

    /**
     * Configure the Model
     */
    abstract public function model(): string;

    /**
     * Make Model instance
     *
     * @return Model
     * @throws \Exception
     *
     */
    public function makeModel()
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     */
    public function paginate(int $perPage, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     * @param array $searchArray
     * @param string|null $search
     * @param int|null $skip
     * @param int|null $limit
     * @param string $order
     * @param string $orderColumn
     * @return Builder
     */
    public function allQuery(array  $searchArray = [], string $search = null, int $skip = null, int $limit = null,
                             string $order = 'desc', string $orderColumn = 'id', array $with = [],
                                    $additionalConditions = null, $onlyTrashed = false): Builder
    {
        $query = $this->model->newQuery();

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        // Agregar los 'with' al query
        if (!empty($with)) {
            $query->with($with);
        }

        if (count($searchArray)) {
            foreach ($searchArray as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                foreach ($this->getFieldsSearchable() as $key => $value) {
                    $query->orWhere($value, 'like', "%" . $search . "%");
                }

                if (strpos(get_class($this->model), 'Booking') !== false) {
                    $query->orWhere(function($q) use($value, $search) {
                        $q->whereHas('bookingUsers.course', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', "%" . $search . "%");
                        });

                    });
                }

                if (strpos(get_class($this->model), 'Booking') !== false) {
                    $query->orWhere(function($q) use($value, $search) {
                        $q->whereHas('clientMain', function ($subQuery) use ($search) {
                            $subQuery->where('first_name', 'like', "%" . $search . "%")
                            ->orWhere('last_name', 'like', "%" . $search . "%");
                        });
                    });
                }

                if (strpos(get_class($this->model), 'Voucher') !== false) {
                    $query->orWhere(function($q) use($value, $search) {
                        $q->whereHas('client', function ($subQuery) use ($search) {
                            $subQuery->where('first_name', 'like', "%" . $search . "%")
                                ->orWhere('last_name', 'like', "%" . $search . "%");
                        });
                    });
                }
            });

        }

        // Aplicar condiciones adicionales si se proporcionan
        if ($additionalConditions && is_callable($additionalConditions)) {
            $additionalConditions($query);
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query->orderBy($orderColumn, $order);
    }

    /**
     * Retrieve all records with given filter criteria
     */
    public function all($searchArray = [], string $search = null, $skip = null, $limit = null, $pagination = 10,
                        array $with = [],
        $order = 'desc', $orderColumn = 'id', $additionalConditions = null, $onlyTrashed = false): Builder|LengthAwarePaginator
    {
        $query = $this->allQuery($searchArray, $search, $skip, $limit, $order, $orderColumn, $with, $additionalConditions, $onlyTrashed);

        return $query->paginate($pagination);
    }

    /**
     * Create model record
     */
    public function create(array $input): Model
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find(int $id, array $with = [], array $columns = ['*'])
    {
        $query = $this->model->newQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Update model record for given id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update(array $input, int $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @return bool|mixed|null
     * @throws \Exception
     *
     */
    public function delete(int $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }
}
