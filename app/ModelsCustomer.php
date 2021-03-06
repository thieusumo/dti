<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelsCustomer extends Model
{
    protected $table = 'csr_customers';
    protected $fillable = [
    	'fullname',
    	'cell_phone',
    	'business_phone',
    	'address',
    	'email',
    	'website',
    	'notes',
    	'created_by',
    	'modified_by',
        'seller_id'
    	
    ];
}
