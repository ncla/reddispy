<?php

namespace App\Scraper\Base\Storage;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

abstract class EloquentDatabaseStorage implements DatabaseStorageInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * EloquentDatabaseStorage constructor.
     * @param $eloquentClass
     * @throws \ErrorException
     */
    public function __construct($eloquentClass)
    {
        $this->makeModel($eloquentClass);
    }

    /**
     * @return Model|ReflectionClass
     * @throws \ErrorException
     */
    public function makeModel($class)
    {
        $model = new $class;

        if (!$model instanceof Model) {
            // Change exception
            throw new \ErrorException("Class {$this->model} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }
}
