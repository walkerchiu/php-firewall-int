<?php

namespace WalkerChiu\Firewall\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use WalkerChiu\Core\Models\Entities\DateTrait;

class Item extends Model
{
    use DateTrait;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var Array
     */
    protected $fillable = [
        'setting_id', 'user_id'
    ];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var Array
	 */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var Array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.firewall.items');

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function setting()
    {
        return $this->belongsTo(config('wk-core.class.firewall.setting'), 'setting_id', 'id');
    }
}
