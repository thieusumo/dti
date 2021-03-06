<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Class PosGiftcode
 */
class PosGiftcode extends BaseModel
{
    protected $table = 'pos_giftcode';

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();
    }

    protected $fillable = [
        'giftcode_id',
        'giftcode_code',
        'giftcode_used_times',
        'giftcode_customer_id',
        'giftcode_date_expire',
        'giftcode_linkimage',
        'giftcode_surplus',
        'giftcode_image_front',
        'giftcode_sub_id',
        'giftcode_place_id',
        'giftcode_status'
    ];

    protected $guarded = [];

        
}