<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class MainCustomerTemplate extends Model
{
    protected $table = "main_customer_template";
    protected $fillable = [
    	'ct_salon_name',
    	'ct_fullname',
        'ct_firstname',
        'ct_lastname',
    	'ct_business_phone',
    	'ct_cell_phone',
    	'ct_email',
    	'ct_address',
    	'ct_website',
        'ct_birthdate',
    	'ct_note',
    	'created_by',
    	'updated_by',
        'created_at',
        'ct_active',
        'old_customer_id'
    ];
    public function getFullname(){
        return $this->ct_lastname." ".$this->ct_firstname;
    }
    public function getMainCustomer(){
        return $this->hasOne(MainCustomer::class,'customer_customer_template_id','id');
    }
    public function getCreatedBy(){
        return $this->belongsTo(MainUser::class,'created_by','user_id')->withDefault();
    }
    public function getPlace(){
        return $this->hasOne(PosPlace::class,'place_phone','ct_business_phone');
    }


}
//
