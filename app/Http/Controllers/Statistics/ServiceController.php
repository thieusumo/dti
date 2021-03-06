<?php

namespace App\Http\Controllers\Statistics;

use Illuminate\Http\Request;
use App\Models\MainCustomer;
use App\Http\Controllers\Controller;
use App\Models\MainComboServiceBought;

class ServiceController extends Controller
{
	function __construct(){
		$date = get_nowDate();

		// MainComboServiceBought::getDatatable($date);
	}

	public function index(){
		return view('statistics.service');
	}
	/**
	 * datatable statisic services
	 * @param  int $request->start
	 * @param  int $request->length
	 * @param  string $request->type
	 * @param  string $request->date
	 * @return mixed datatable
	 */
	public function datatable(Request $request){
		$start = $request->start;
		$length = $request->length;
		$type = $request->type;
		$valueQuarter = $request->valueQuarter;
		$date = format_date_db($request->date) ?? null;
		
		return MainComboServiceBought::getDatatable($start, $length, $type, $valueQuarter, $date);
	}	

	
}
