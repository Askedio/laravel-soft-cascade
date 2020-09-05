<?php

namespace Askedio\SoftCascade;

use Askedio\SoftCascade\Contracts\SoftCascadeable;
use Askedio\SoftCascade\Exceptions\SoftCascadeLogicException;
use Askedio\SoftCascade\Exceptions\SoftCascadeNonExistentRelationActionException;
use Askedio\SoftCascade\Exceptions\SoftCascadeRestrictedException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Facades\DB;

class SoftCascade implements SoftCascadeable
{
    protected $direction;
    protected $directionData;
    protected $availableActions = ['update', 'restrict'];
    protected $fnGetForeignKey = ['getQualifiedForeignKeyName', 'getQualifiedOwnerKeyName', 'getForeignPivotKeyName'];
    protected $dbsToTransact = [];

    /**
     * Cascade over Eloquent items.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     * @param string                             $direction     update|delete|restore
     * @param array                              $directionData
     *
     * @return void
     */
    public function cascade($models, $direction, array $directionData = [])
    {
        try {
            $this->direction = $direction;
            $this->directionData = $directionData;
            $this->run($models);
            //All ok we commit all database queries
            foreach ($this->dbsToTransact as $connectionToTransact) {
                DB::connection($connectionToTransact)->commit();
            }
        } catch (\Exception $e) {
            //Rollback the transaction before throw exception
            foreach ($this->dbsToTransact as $connectionToTransact) {
                DB::connection($connectionToTransact)->rollBack();
            }

            throw new SoftCascadeLogicException($e->getMessage(), null, $e);
        }
    }

    /**
     * Run the cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     *
     * @return void
     */
    protected function run($models)
    {
        $models = collect($models);
        if ($models->count() > 0) {
            $model = $models->first();

            if (!is_object($model)) {
                return;
            }

            if (!$this->isCascadable($model)) {
                return;
            }

            if (!in_array($model->getConnectionName(), $this->dbsToTransact)) {
                $this->dbsToTransact[] = $model->getConnectionName();
                DB::connection($model->getConnectionName())->beginTransaction();
            }

            $this->relations($model, $models->pluck($model->getKeyName()));
        }
    }

    /**
     * Iterate over the relations.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param array                              $foreignKeyIds
     * @param array                              $foreignKeyIds
     *
     * @return mixed
     */
    protected function relations($model, $foreignKeyIds)
    {
        $relations = $model->getSoftCascade();

        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            extract($this->relationResolver($relation));
            $this->validateRelation($model, $relation);

            $modelRelation = $model->$relation();

            /**
             * Maintains compatibility fot get foreign key name on laravel old and new methods.
             *
             * @link https://github.com/laravel/framework/issues/20869
             */
            $fnUseGetForeignKey = array_intersect($this->fnGetForeignKey, get_class_methods($modelRelation));
            $fnUseGetForeignKey = reset($fnUseGetForeignKey);

            //Get foreign key and foreign key ids
            $foreignKeyUse = $modelRelation->{$fnUseGetForeignKey}();
            $foreignKeyIdsUse = $foreignKeyIds;

            //Many to many relations need to get related ids and related local key
            if ($modelRelation instanceof BelongsToMany) {
                extract($this->getBelongsToManyData($modelRelation, $foreignKeyUse, $foreignKeyIds));
            } elseif ($modelRelation instanceof MorphOneOrMany) {
                extract($this->getMorphManyData($modelRelation, $foreignKeyIds));
            }

            $affectedRows = $this->affectedRows($modelRelation, $foreignKeyUse, $foreignKeyIdsUse);

            if ($action === 'restrict' && $affectedRows > 0) {
                DB::rollBack(); //Rollback the transaction before throw exception

                throw (new SoftCascadeRestrictedException())->setModel(get_class($modelRelation->getModel()), $foreignKeyUse, $foreignKeyIdsUse->toArray());
            }

            $this->execute($modelRelation, $foreignKeyUse, $foreignKeyIdsUse, $affectedRows);
        }
    }

    /**
     * Get many to many related key ids and key use.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                          $relationForeignKey
     * @param array                                           $foreignKeyIds
     *
     * @return array
     */
    protected function getBelongsToManyData($relation, $relationForeignKey, $foreignKeyIds)
    {
        $relationConnection = $relation->getConnection()->getName();
        $relationTable = $relation->getTable();
        $relationRelatedKey = $relation->getQualifiedRelatedPivotKeyName();
        //Get related ids
        $foreignKeyIdsUse = DB::connection($relationConnection)
            ->table($relationTable)
            ->whereIn($relationForeignKey, $foreignKeyIds)
            ->select([$relationRelatedKey])
            ->get()->toArray();
        $foreignKeyUse = explode('.', $relationRelatedKey);
        $foreignKeyUse = end($foreignKeyUse);
        $foreignKeyIdsUse = array_column($foreignKeyIdsUse, $foreignKeyUse);

        return [
            'foreignKeyIdsUse' => collect($foreignKeyIdsUse),
            'foreignKeyUse'    => $relation->getRelated()->getKeyName(),
        ];
    }

    /**
     * Get morph many related key ids and key use.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param array                                           $foreignKeyIds
     *
     * @return array
     */
    protected function getMorphManyData($relation, $foreignKeyIds)
    {
        $relatedClass = $relation->getRelated();
        $foreignKeyUse = $relatedClass->getKeyName();
        $baseQuery = $this->direction === 'delete'
        ? $relatedClass::query()
        : $relatedClass::withTrashed();
        $foreignKeyIdsUse = $baseQuery->where($relation->getMorphType(), $relation->getMorphClass())
            ->whereIn($relation->getQualifiedForeignKeyName(), $foreignKeyIds)
            ->select($foreignKeyUse)
            ->get()->toArray();
        $foreignKeyIdsUse = array_column($foreignKeyIdsUse, $foreignKeyUse);

        return [
            'foreignKeyIdsUse' => collect($foreignKeyIdsUse),
            'foreignKeyUse'    => $relation->getRelated()->getKeyName(),
        ];
    }

    /**
     * Execute delete, or restore.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                          $foreignKey
     * @param array                                           $foreignKeyIds
     * @param int                                             $affectedRows
     *
     * @return void
     */
    protected function execute($relation, $foreignKey, $foreignKeyIds, $affectedRows)
    {
        $relationModel = $relation->getQuery()->getModel();
        $relationModel = new $relationModel();
        if ($affectedRows > 0) {
            if ($this->direction != 'delete') {
                $relationModel = $relationModel->withTrashed();
            }

            $relationModel = $relationModel->whereIn($foreignKey, $foreignKeyIds)->limit($affectedRows);

            $this->run($relationModel->get([$relationModel->getModel()->getKeyName()]));
            $relationModel->{$this->direction}($this->directionData);
        }
    }

    /**
     * Validate the relation method exists and is a type of Eloquent Relation.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $relation
     *
     * @return void
     */
    protected function validateRelation($model, $relation)
    {
        $class = get_class($model);
        if (!method_exists($model, $relation)) {
            DB::rollBack(); //Rollback the transaction before throw exception

            throw new \LogicException(sprintf('%s does not have method \'%s\'.', $class, $relation));
        }

        if (!$model->$relation() instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            DB::rollBack(); //Rollback the transaction before throw exception

            throw new \LogicException(sprintf('%s \'%s\' is not an instance of Illuminate\Database\Eloquent\Relations\Relation.', $class, $relation));
        }
    }

    /**
     * Check if the model is enabled to cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function isCascadable($model)
    {
        return method_exists($model, 'getSoftCascade');
    }

    /**
     * Affected rows if we do execute.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                          $foreignKey
     * @param array                                           $foreignKeyIds
     *
     * @return void
     */
    protected function affectedRows($relation, $foreignKey, $foreignKeyIds)
    {
        $relationModel = $relation->getQuery()->getModel();
        $relationModel = new $relationModel();

        if ($this->direction != 'delete') {
            $relationModel = $relationModel->withTrashed();
        }

        return $relationModel->whereIn($foreignKey, $foreignKeyIds)->count();
    }

    /**
     * Resolve relation string.
     *
     * @param string $relation
     *
     * @return array
     */
    protected function relationResolver($relation)
    {
        $parsedAction = explode('@', $relation);
        $return['relation'] = $parsedAction[0];
        $return['action'] = isset($parsedAction[1]) ? $parsedAction[1] : 'update';

        if (!in_array($return['action'], $this->availableActions)) {
            DB::rollBack(); //Rollback the transaction before throw exception

            throw (new SoftCascadeNonExistentRelationActionException())->setRelation(implode('@', $return));
        }

        return $return;
    }
}
