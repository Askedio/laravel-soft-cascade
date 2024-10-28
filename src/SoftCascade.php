<?php

namespace Askedio\SoftCascade;

use Askedio\SoftCascade\Contracts\SoftCascadeable;
use Askedio\SoftCascade\Exceptions\SoftCascadeLogicException;
use Askedio\SoftCascade\Exceptions\SoftCascadeNonExistentRelationActionException;
use Askedio\SoftCascade\Exceptions\SoftCascadeRestrictedException;
use Askedio\SoftCascade\Traits\ChecksCascading;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type ModelArray      \Illuminate\Database\Eloquent\Model[]
 * @phpstan-type ModelCollection \Illuminate\Support\Collection<array-key, \Illuminate\Database\Eloquent\Model>
 * @phpstan-type Models          ModelCollection | ModelArray | \Illuminate\Database\Eloquent\Model
 */
class SoftCascade implements SoftCascadeable
{
    use ChecksCascading;

    /**
     * @var 'update'|'delete'|'restore'
     */
    protected $direction;

    /**
     * @var array
     */
    protected $directionData;

    /**
     * @var string[]
     */
    protected $availableActions = ['update', 'restrict'];

    /**
     * @var string[]
     */
    protected $fnGetForeignKey = ['getQualifiedForeignKeyName', 'getQualifiedOwnerKeyName', 'getForeignPivotKeyName'];

    /**
     * @var string[]
     */
    protected $dbsToTransact = [];

    /**
     * Cascade over Eloquent items.
     *
     * @param \Illuminate\Database\Eloquent\Model | Models $models
     * @param 'update'|'delete'|'restore'                  $direction
     * @param array                                        $directionData
     *
     * @throws \Askedio\SoftCascade\Exceptions\SoftCascadeLogicException
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection,PhpUnhandledExceptionInspection
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
     * @param Models $models
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection,PhpUnhandledExceptionInspection
     */
    protected function run($models)
    {
        $models = collect($models);
        if ($models->count() > 0) {
            $model = $models->first();

            if (!is_object($model)) {
                return;
            }

            if (!$this->hasCascadingRelations($model)) {
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
     * @param \Illuminate\Database\Eloquent\Model & \Askedio\SoftCascade\Traits\SoftCascadeTrait $model
     * @param \Illuminate\Support\Collection<array-key, int>                                     $foreignKeyIds
     *
     * @return void
     */
    protected function relations($model, $foreignKeyIds)
    {
        $relations = $model->getSoftCascade();

        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            $resolver = $this->relationResolver($relation);
            extract($resolver);
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

            //Many-to-many relations need to get related ids and related local key
            if ($modelRelation instanceof BelongsToMany) {
                $data = $this->getBelongsToManyData($modelRelation, $foreignKeyUse, $foreignKeyIds);
                extract($data);
            } elseif ($modelRelation instanceof MorphOneOrMany) {
                $data = $this->getMorphManyData($modelRelation, $foreignKeyIds);
                extract($data);
            }

            $affectedRows = $this->affectedRows($modelRelation, $foreignKeyUse, $foreignKeyIdsUse);

            if ($action === 'restrict' && $affectedRows > 0) {
                DB::rollBack(); //Rollback the transaction before throw exception

                throw (new SoftCascadeRestrictedException())->setModel(
                    get_class($modelRelation->getModel()),
                    $foreignKeyUse,
                    $foreignKeyIdsUse->toArray()
                );
            }

            $this->execute($modelRelation, $foreignKeyUse, $foreignKeyIdsUse, $affectedRows);
        }
    }

    /**
     * Get many to many related key ids and key use.
     *
     * @param \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation
     * @param string                                                $relationForeignKey
     * @param \Illuminate\Support\Collection<array-key, int>        $foreignKeyIds
     *
     * @return array{
     *             foreignKeyIdsUse: \Illuminate\Support\Collection<array-key, int>,
     *             foreignKeyUse:    string,
     *         }
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
     * @param \Illuminate\Database\Eloquent\Relations\MorphOneOrMany $relation
     * @param \Illuminate\Support\Collection<array-key, int>         $foreignKeyIds
     *
     * @return array{
     *             foreignKeyIdsUse: \Illuminate\Support\Collection<array-key, int>,
     *             foreignKeyUse:    string,
     *         }
     */
    protected function getMorphManyData($relation, $foreignKeyIds)
    {
        $relatedClass = $relation->getRelated();
        $foreignKeyUse = $relatedClass->getKeyName();

        $baseQuery = $this->withTrashed($relatedClass::query());

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
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                           $foreignKey
     * @param \Illuminate\Support\Collection<array-key, int>   $foreignKeyIds
     * @param int                                              $affectedRows
     *
     * @return void
     */
    protected function execute($relation, $foreignKey, $foreignKeyIds, $affectedRows)
    {
        $relationModel = $relation->getQuery()->getModel();
        if ($affectedRows > 0) {
            $relationModel = $this->withTrashed($relationModel::query());

            $relationModel = $relationModel->whereIn($foreignKey, $foreignKeyIds)->limit($affectedRows);

            $this->run($relationModel->get([$relationModel->getModel()->getKeyName()]));
            $relationModel->{$this->direction}($this->directionData);
        }
    }

    /**
     * Validate the relation method exists and is a type of Eloquent Relation.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $relation
     *
     * @throws \LogicException If $model doesn't support the given $relation.
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

        if (!$model->$relation() instanceof Relation) {
            DB::rollBack(); //Rollback the transaction before throw exception

            throw new \LogicException(sprintf(
                '%s \'%s\' is not an instance of Illuminate\Database\Eloquent\Relations\Relation.',
                $class,
                $relation
            ));
        }
    }

    /**
     * Affected rows if we do execute.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                           $foreignKey
     * @param \Illuminate\Support\Collection<array-key, int>   $foreignKeyIds
     *
     * @return int
     */
    protected function affectedRows($relation, $foreignKey, $foreignKeyIds)
    {
        $relationModel = $relation->getQuery()->getModel();
        $relationModel = $this->withTrashed($relationModel::query());

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
        $return['action'] = $parsedAction[1] ?? 'update';

        if (!in_array($return['action'], $this->availableActions)) {
            DB::rollBack(); //Rollback the transaction before throw exception

            throw (new SoftCascadeNonExistentRelationActionException())->setRelation(implode('@', $return));
        }

        return $return;
    }

    /**
     * @template TBuilder of \Illuminate\Database\Eloquent\Builder
     *
     * @param TBuilder $builder
     *
     * @return TBuilder
     *
     * @noinspection PhpDocSignatureInspection,PhpUndefinedMethodInspection
     */
    protected function withTrashed(Builder $builder): Builder
    {
        if ($this->direction === 'delete') {
            return $builder;
        }

        // if the Model does not use SoftDeletes, withTrashed() will be unavailable.
        if ($builder->hasMacro('withTrashed') || method_exists($builder, 'withTrashed')) {
            return $builder->withTrashed();
        }

        return $builder;
    }
}
