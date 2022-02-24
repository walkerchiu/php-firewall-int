<?php

namespace WalkerChiu\Firewall\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class ItemRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.firewall.item'));
    }

    /**
     * @param Array  $data
     * @param Bool   $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(array $data, $auto_packing = false)
    {
        $instance = $this->instance;

        $data = array_map('trim', $data);
        $repository = $instance->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['setting_id']), function ($query) use ($data) {
                                                return $query->where('setting_id', $data['setting_id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-firewall.output_format'), config('wk-firewall.pagination.pageName'), config('wk-firewall.pagination.perPage'));
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Item  $instance
     * @return Array
     */
    public function show($instance): array
    {
        if (empty($instance))
            return [
                'id'         => '',
                'setting_id' => '',
                'user_id'    => '',
                'updated_at' => ''
            ];

        $this->setmodel($instance);

        return [
              'id'         => $instance->id,
              'setting_id' => $instance->setting_id,
              'user_id'    => $instance->user_id,
              'updated_at' => $instance->updated_at
        ];
    }
}
