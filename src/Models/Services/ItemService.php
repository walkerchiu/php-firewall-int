<?php

namespace WalkerChiu\Firewall\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Exceptions\NotExpectedEntityException;
use WalkerChiu\Core\Models\Exceptions\NotFoundEntityException;
use WalkerChiu\Core\Models\Services\CheckExistTrait;
use WalkerChiu\Firewall\Models\Services\LogService;

class ItemService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.firewall.itemRepository'));
    }

    /*
    |--------------------------------------------------------------------------
    | Get item
    |--------------------------------------------------------------------------
    */

    /**
     * @param Int  $item_id
     * @return Item
     *
     * @throws NotFoundEntityException
     */
    public function find(int $item_id)
    {
        $entity = $this->repository->find($item_id);

        if (empty($entity))
            throw new NotFoundEntityException($entity);

        return $entity;
    }

    /**
     * @param Item|Int  $source
     * @return Item
     *
     * @throws NotExpectedEntityException
     */
    public function findBySource($source)
    {
        if (is_integer($source))
            $entity = $this->find($source);
        elseif (is_a($source, config('wk-core.class.firewall.item')))
            $entity = $source;
        else
            throw new NotExpectedEntityException($source);

        return $entity;
    }



    /*
    |--------------------------------------------------------------------------
    | Operation
    |--------------------------------------------------------------------------
    */

    /**
     * Update value.
     *
     * @param Item|Int  $source
     * @param Float     $value
     * @return Bool
     */
    public function updateValue($source, float $value)
    {
        $entity = $this->findBySource($source);

        return $entity->update(['value' => $value]);
    }
}
