<?php

if(!function_exists('getCategory')){
	function getCategory(){
		return [
			1 => 'GENERAL',
			2 => 'ISSUE'
		];
	}
}
if(!function_exists('getPriorityTPAask')){
	function getPriorityTask(){
	    return [
	        1 => 'LOW',
	        2 => 'NORMAL',
	        3 => 'HIGH',
	        4 => 'URGENT',
	        5 => 'IMMEDIATE'
	    ];
	}
}
if(!function_exists('getStatusTask')){

	 function getStatusTask(){
	    return [
	        1 => 'NEW',
	        2 =>'PROCESSING',
	        3 => 'DONE',
            4 => 'CANCEL'
	    ];
	}
}

if(!function_exists('getFormService')){
    function getFormService(){
        return [
            1 => 'Google',
            2 => 'Website',
            3 => 'Facebook',
            4 => 'Domain',
            5 => 'Default'
        ];
    }
}
function cutString($str, $length = 15, $end = '...')
{
    $minword = 3;
    $sub = '';
    $len = 0;
    foreach (explode(' ', $str) as $word) {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        if (strlen($word) > $minword && strlen($sub) >= $length) {
            break;
        }
    }
    return $sub . (($len < strlen($str)) ? $end : '');
}
function checkPermission($role,$permission){

    $permission_list = $role->gu_role_new;
    if($permission_list == "")
        $check = "";
    else{
        $permission_arr = explode(';',$permission_list);
        if(in_array($permission->id,$permission_arr))
            $check = "checked";
        else
            $check = "";
    }
    return $check;
}
function doNotPermission(){
    return back()->with('error','Permission Denies!');
}
function doNotPermissionAjax(){
    return response(['status'=>'error','message'=>'Permission Denies!']);
}
function getReviewStatus(){
    return [
        0 => 'FAILED',
        1 => 'SUCCESSFULLY'
    ];
}
// if(!function_exists('getTimeType'))
function getTimeType(){
    return [
        1 => 'Month',
        2 => 'Day'
    ];
}
function getOrderStatus(){
    return [
        0 => 'NOTPAYMENT',
        1 => 'PAID',
        2 => 'PROCESSING',
        3 => 'DONE',
        4 => 'DELIVERED',
        5 => 'FIXING',
        6 => 'CANCEL'
    ];
}
function ratingCustomer(){
    return [
        1 => 'Rất hài lòng',
        2 => 'Hài lòng',
        3 => 'Bình thường',
        4 => 'Không hài lòng',
        5 => 'Rất không hài lòng'
    ];
}
function yesNo(){
    return [
        1 => 'Có',
        2 => 'Không'
    ];
}
function orderType(){
    return [
        1 => 'Website',
        2 => 'Design',
    ];
}
function dayOfWeek(){
    return [ 'Monday','Tuesday','Wednesday','Thursday', 'Friday','Saturday','Sunday' ];
}
?>
