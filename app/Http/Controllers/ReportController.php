<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralHelper;
use App\Models\MainComboService;
use App\Models\MainComboServiceBought;
use App\Models\MainCustomer;
use App\Models\MainCustomerTemplate;
use App\Models\MainTask;
use App\Models\MainTeam;
use App\Models\MainUser;
use App\Models\MainUserCustomerPlace;
use App\Models\MainUserReview;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Auth;
use Carbon\Carbon;
use Gate,DB;
use App\Models\MainTeamType;
use App\Models\MainCustomerRating;

class ReportController extends Controller
{
    public function customers(){

        if(Gate::denies('permission','customer-report'))
            return doNotPermission();

        $data['status'] = GeneralHelper::getCustomerStatusList();
        if(Gate::allows('permission','customer-report-admin'))
             $data['teams'] = MainTeam::active()->get();
        else
             $data['teams'] = MainTeam::where('id',Auth::user()->user_team)->get();
        return view('reports.customers',$data);
    }
    public function getCustomerList($request){

        if(Gate::denies('permission','customer-report'))
            return doNotPermission();

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $address = $request->address;
        $status_customer = $request->status_customer;
        $customer_arr = [];

        if(Gate::allows('permission','customer-report-admin')){
            $team_id = $request->team_id;

            $team_info  = MainTeam::find($team_id)->getTeamType;
            $team_slug = $team_info->slug;

            $customer_list = DB::table('main_customer_template')->leftjoin('main_customer',function($join){
                $join->on('main_customer_template.id','main_customer.customer_customer_template_id');
            });
        }
        else{
            $team_id = Auth::user()->user_team;
            if(Gate::allows('permission','customer-report-leader')){
                $user_customer_list = MainUserCustomerPlace::where([
                    ['team_id',$team_id],
                ])->select('customer_id')->get()->toArray();
            }
            else{
                $user_id = Auth::user()->user_id;
                $user_customer_list = MainUserCustomerPlace::where([
                    ['user_id',$user_id],
                    ['team_id',$team_id],
                ])->select('customer_id')->get()->toArray();
            }
            $user_customer_arr = array_values($user_customer_list);

            $team_info  = MainTeam::find($team_id)->getTeamType;
            $team_slug = $team_info->slug;

            $customer_list = DB::table('main_customer_template')->leftjoin('main_customer',function($join){
                $join->on('main_customer_template.id','main_customer.customer_customer_template_id');
            })
            ->whereIn('main_customer_template.id', $user_customer_arr);
        }

            if($start_date != "" && $end_date != ""){

                $start_date = Carbon::parse($start_date)->subDay(1)->format('Y-m-d');
                $end_date = Carbon::parse($end_date)->addDay(1)->format('Y-m-d');

                $customer_list->whereDate('main_customer_template.created_at','>=',$start_date)
                    ->whereDate('main_customer_template.created_at','<=',$end_date);
            }
            if($address != ""){
                $customer_list->where('main_customer_template.ct_address','LIKE',"%".$address."%");
            }
            if($status_customer != ""){
                if($status_customer == 3){
                    $customer_list->where(function($query) use ($team_slug,$status_customer){
                        $query->where('main_customer_template.'.$team_slug,0)
                        ->orWhere('main_customer_template.'.$team_slug,$status_customer);
                    });
                }else
                    $customer_list->where('main_customer_template.'.$team_slug,$status_customer);
            }

            $customer_list = $customer_list->get();

            //GET CUSTOMER BY SERVICE
            // $service_customers = MainComboServiceBought::all();
            $service_customers = DB::table('main_combo_service_bought')->join('main_user',function($join){
                $join->on('main_combo_service_bought.created_by','main_user.user_id');
            });
            $service_customers = collect($service_customers);


            foreach ($customer_list as $key => $customer) {

                $discount_total = 0;
                $seller_name = "";
                $charged_total = 0;

                if($customer->$team_slug == 0 || $customer->$team_slug == 1){
                    $ct_status = 'New Arrivals';
                }
                else{
                    $ct_status = GeneralHelper::getCustomerStatus($customer->$team_slug);
                    if($customer->$team_slug == 4){
                        $order_info = $service_customers->where('csb_customer_id',$customer->customer_id);
                        $seller_list  = $order_info->select('created_by')->distinct('created_by')->get();
                        foreach($seller_list as $seller){
                            $seller_name .= $seller->user_nickname.";";
                        }
                        $discount_total = $order_info->sum('csb_amount_deal');
                        $charged_total = $order_info->sum('csb_charge');
                    }
                }

                $customer_arr[] = [
                    'id' => $customer->id,
                    'ct_salon_name' => $customer->ct_salon_name,
                    'ct_fullname' => $customer->ct_fullname,
                    'ct_business_phone' => $customer->ct_business_phone,
                    'ct_cell_phone' => $customer->ct_cell_phone,
                    'ct_status' => $ct_status,
                    'seller' => $seller_name,
                    'discount_total' => $discount_total,
                    'charged_total' => $charged_total
                ];
            }
         return $customer_arr;

    }
    public function customersDataTable( Request $request){

        if(Gate::denies('permission','customer-report'))
            return doNotPermission();

        $customer_list = self::getCustomerList($request);
        return Datatables::of($customer_list)
            ->editColumn('id',function ($row){
                if($row['ct_status'] == 'Serviced')
                    return '<a href="'.route('customer-detail',$row['id']).'">'.$row['id'].'</a>';
                else
                    return '<a href="javascript:void(0)">'.$row['id'].'</a>';
            })
            ->rawColumns(['seller','id'])
            ->make(true);
    }
    public function services(){

        if(Gate::denies('permission','service-report'))
            return doNotPermission;

        if(Gate::allows('permission','service-report-admin'))
            $data['sellers'] = MainUser::all();
        elseif(Gate::allows('permission','service-report-leader'))
            $data['sellers'] = MainUser::where('user_team',Auth::user()->user_team)->get();
        else
            $data['sellers'] = MainUser::where('user_id',Auth::user()->user_id)->get();

        return view('reports.services',$data);
    }
    public function getServiceList($request){

        if(Gate::allows('permission','service-report-admin'))
            $combo_service_list = MainComboService::orderBy('cs_combo_service_type','asc')->get();
        else{
            $service_list = MainTeam::find(Auth::user()->user_team)->getTeamType->service_permission;
            $service_list = explode(';',$service_list);
            $combo_service_list = MainComboService::whereIn('id',$service_list)->orderBy('cs_combo_service_type','asc')->get();
        }

        $service_customer_result = [];

        foreach ($combo_service_list as $combo_service){

            $service_id = $combo_service->id;

            $service_customer = MainComboServiceBought::where(function($query) use ($service_id){
                $query->where('csb_combo_service_id',$service_id)
                    ->orWhere('csb_combo_service_id','like','%;'.$service_id)
                    ->orWhere('csb_combo_service_id','like','%;'.$service_id.';%')
                    ->orWhere('csb_combo_service_id','like',$service_id.';%');
            });
            if($request->address != ""){

                $address_customer = $request->address;

                $service_customer = $service_customer->join('main_customer',function($join) use ($address_customer){
                    $join->on('main_combo_service_bought.csb_customer_id','main_customer.customer_id')
                    ->where('main_customer.customer_address','like','%'.$address_customer.'%');
                });

                if($request->start_date != "" && $request->end_date != ""){
                    $start_date = Carbon::parse($request->start_date)->subDay(1)->format('Y-m-d');
                    $end_date = Carbon::parse($request->end_date)->addDay(1)->format('Y-m-d');
                    $service_customer = $service_customer->whereBetween('main_combo_service_bought.created_at',[$start_date,$end_date]);
                }

                if($request->seller_id != ""){
                    $service_customer = $service_customer->where('main_combo_service_bought.created_by',$request->seller_id);
                }

                $customer_total = $service_customer->distinct('main_combo_service_bought.csb_customer_id')->count('main_combo_service_bought.csb_customer_id');
                $order_total = $service_customer->count();

            }else{

                if($request->start_date != "" && $request->end_date != ""){
                    $start_date = Carbon::parse($request->start_date)->subDay(1)->format('Y-m-d');
                    $end_date = Carbon::parse($request->end_date)->addDay(1)->format('Y-m-d');
                    $service_customer = $service_customer->whereBetween('created_at',[$start_date,$end_date]);
                }
                if($request->seller_id != ""){
                    $service_customer = $service_customer->where('created_by',$request->seller_id);
                }
                $customer_total = $service_customer->distinct('csb_customer_id')->count('csb_customer_id');
                $order_total = $service_customer->count();
            }

            $service_customer_result[] = [
                'id' => $combo_service->id,
                'service_name' => $combo_service->cs_name,
                'service_price' => $combo_service->cs_price,
                'customer_total' => $customer_total,
                'order_total' => $order_total
            ];
        }
        return $service_customer_result;
    }
    public function servicesDataTable(Request $request){

        if(Gate::denies('permission','service-report'))
            return doNotPermission;

        $service_list = self::getServiceList($request);
        return DataTables::of($service_list)
            ->make(true);
    }
    public function sellers(){

        if(Gate::denies('permission','seller-report'))
            return doNotPermission;


        if(Gate::allows('permission','seller-report-admin')){
            $team_type_id = MainTeamType::where('team_type_name','Telemarketing')->first()->id;
            $team_id = MainTeam::select('id')->where('team_type',$team_type_id)->get()->toArray();
            $team_id_array = array_values($team_id);
            $data['sellers'] = MainUser::whereIn('user_team',$team_id_array)->get();
        }
        else
            $data['sellers'] = MainUser::where('user_team',Auth::user()->user_team)->get();
        
        return view('reports.sellers',$data);
    }
    public function getSellerList($request){

        if(Gate::allows('permission','seller-report-admin')){

            $team_type_id = MainTeamType::where('team_type_name','Telemarketing')->first()->id;
            $team_id = MainTeam::select('id')->where('team_type',$team_type_id)->get()->toArray();
            $team_id_array = array_values($team_id);
            $user_list = MainUser::whereIn('user_team',$team_id_array);
        }
        else
            $user_list = MainUser::where('user_team',Auth::user()->user_team);

        if($request->start_date != "" && $request->end_date){
            $start_date = Carbon::parse($request->start_date)->subDay(1)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->addDay(1)->format('Y-m-d');
            $user_list->whereBetween('created_at',[$start_date,$end_date]);
        }
        if($request->seller_id != ""){
            $user_list->where('user_id',$request->seller_id);
        }
        $user_list =  $user_list->get();

        $user_customer_place = MainUserCustomerPlace::all();
        $user_customer_place = collect($user_customer_place);
        $order_list_collect = MainComboServiceBought::all();
        $order_list_collect = collect($order_list_collect);

        $seller_list = [];

        foreach ($user_list as $user){

            $total_assigned = $user_customer_place->where('user_id',$user->user_id)->where('place_id',null)->count();
            $total_serviced = $user_customer_place->where('user_id',$user->user_id)->where('place_id','!=',null)->count();
            $total_orders = $order_list_collect->where('created_by',$user->user_id)->count();
            $total_discount = $order_list_collect->where('created_by',$user->user_id)->sum('csb_amount_deal');
            $total_charged = $order_list_collect->where('created_by',$user->user_id)->sum('csb_charge');

                $seller_list[] = [
                    'id' => $user->user_id,
                    'user_nickname' => $user->user_nickname,
                    'user_fullname' => $user->getFullname(),
                    'email' => $user->user_email,
                    'total_assigned' => $total_assigned,
                    'total_serviced' => $total_serviced,
                    'total_orders' => $total_orders,
                    'total_discount' => $total_discount,
                    'total_charged' => $total_charged
                ];
        }
        return $seller_list;
    }
    public function sellersDataTable(Request $request){

        if(Gate::denies('permission','seller-report'))
            return doNotPermission;

        $seller_list = self::getSellerList($request);
        return DataTables::of($seller_list)
            ->make(true);
    }

    public function customersTotal(Request $request){
        $team_id = $request->team_id;

        $arrivals_total = 0;
        $assigned_total = 0;
        $serviced_total = 0;
        $disabled_total = 0;
        $discount_total = 0;
        $charged_total  = 0;

        if(Gate::allows('permission','customer-report-admin')){
            $customer_list = MainCustomerTemplate::select('*');
        }
        else{
            if(Gate::allows('permission','customer-report-leader')){
                $user_customer_list = MainUserCustomerPlace::where([
                    ['team_id',$team_id],
                ])->select('customer_id')->get()->toArray();
            }
            else{
                $user_id = Auth::user()->user_id;
                $user_customer_list = MainUserCustomerPlace::where([
                    ['user_id',$user_id],
                    ['team_id',$team_id],
                ])->select('customer_id')->get()->toArray();
            }
                $user_customer_arr = array_values($user_customer_list);
                $customer_list = MainCustomerTemplate::whereIn('id', $user_customer_arr);
        }

            if($request->start_date != "" && $request->end_date != ""){
                $start_date = Carbon::parse($request->start_date)->subDay(1)->format('Y-m-d');
                $end_date = Carbon::parse($request->end_date)->addDay(1)->format('Y-m-d');

                $customer_list->whereDate('created_at','>=',$start_date)
                    ->whereDate('created_at','<=',$end_date);
            }
            if($request->address != ""){
                $customer_list->where('ct_address','LIKE',"%".$request->address."%");
            }

            $customer_list = $customer_list->get();

            $team_info = MainTeam::find($team_id)->getTeamType;
            $team_slug = $team_info->slug;

            foreach ($customer_list as $key => $customer) {
                
                switch ($customer->$team_slug) {
                    case 1:
                        //Assigned
                        $assigned_total++;
                        break;
                    case 4:
                        //Serviced
                        $serviced_total++;
                        break;
                    case 3:
                        //New Arrivals
                        $arrivals_total++;
                        break;
                    case 0:
                        //New Arrivals
                        $arrivals_total++;
                        break;
                    case 2:
                        //Disabled
                        $disabled_total++;
                        break;
                }
            }
            $customers = self::getCustomerList($request);
            foreach ($customers as $key => $customer) {
                $discount_total += $customer['discount_total'];
                $charged_total += $customer['charged_total'];
            }

        return response([
            'arrivals_total' => $arrivals_total,
            'assigned_total' => $assigned_total,
            'serviced_total' => $serviced_total,
            'disabled_total' => $disabled_total,
            'charged_total' => $charged_total,
            'discount_total' => $discount_total 
        ]);
    }
    public function customersExport(Request $request){

        $customer_list = self::getCustomerList($request);
        $date = Carbon::now()->format('Y_m_d_His');
        //GET TEAM NAME
        $team_name = MainTeam::find($request->team_id)->team_name;

        return \Excel::create('customer_list_'.$team_name."_".$date,function($excel) use ($customer_list){

            $excel ->sheet('Customer List', function ($sheet) use ($customer_list)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('ID');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Nail Shop');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Contact Name');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Business Phone');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Cell Phone');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Status');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Seller');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Discount Total');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Charged Total');   });

                if (!empty($customer_list)) {
                    foreach ($customer_list as $key => $value) {
                        $i=$key+2;
                        $sheet->cell('A'.$i, $value['id']);
                        $sheet->cell('B'.$i, $value['ct_salon_name']);
                        $sheet->cell('C'.$i, $value['ct_fullname']);
                        $sheet->cell('D'.$i, $value['ct_business_phone']);
                        $sheet->cell('E'.$i, $value['ct_cell_phone']);
                        $sheet->cell('F'.$i, $value['ct_status']);
                        $sheet->cell('G'.$i, $value['seller']);
                        $sheet->cell('H'.$i, $value['discount_total']);
                        $sheet->cell('I'.$i, $value['charged_total']);
                    }
                }
            });
        })->download("xlsx");
    }
    public function serviceExport(Request $request){

        $service_list = self::getServiceList($request);
        $date = Carbon::now()->format('Y_m_d_His');
        return \Excel::create('service_list_'.$date,function($excel) use ($service_list){

            $excel ->sheet('Service List', function ($sheet) use ($service_list)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('ID');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Serivce');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Service Price($)');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Total Customers');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Total Orders');   });

                if (!empty($service_list)) {
                    foreach ($service_list as $key => $value) {
                        $i=$key+2;
                        $sheet->cell('A'.$i, $value['id']);
                        $sheet->cell('B'.$i, $value['service_name']);
                        $sheet->cell('C'.$i, $value['service_price']);
                        $sheet->cell('D'.$i, $value['customer_total']);
                        $sheet->cell('E'.$i, $value['order_total']);
                    }
                }
            });
        })->download("xlsx");
    }
    public function sellerExport(Request $request){

        $seller_list = self::getSellerList($request);
        $date = Carbon::now()->format('Y_m_d_His');
        return \Excel::create('seller_list'.$date,function($excel) use ($seller_list){

            $excel ->sheet('Seller List', function ($sheet) use ($seller_list)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('ID');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Username');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Fulllname');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Email');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Total Assigned Customers');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Total Serviced Customers');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Total Order');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Total Discount($)');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Total Charged($)');   });

                if (!empty($seller_list)) {
                    foreach ($seller_list as $key => $value) {
                        $i=$key+2;
                        $sheet->cell('A'.$i, $value['id']);
                        $sheet->cell('B'.$i, $value['user_nickname']);
                        $sheet->cell('C'.$i, $value['user_fullname']);
                        $sheet->cell('D'.$i, $value['email']);
                        $sheet->cell('E'.$i, $value['total_assigned']);
                        $sheet->cell('F'.$i, $value['total_serviced']);
                        $sheet->cell('G'.$i, $value['total_orders']);
                        $sheet->cell('H'.$i, $value['total_discount']);
                        $sheet->cell('I'.$i, $value['total_charged']);
                    }
                }
                else{
                    return back('error','Empty Data for Exporting!');
                }
            });
        })->download("xlsx");
    }
    public function reviews(){

        if(Gate::denies('permission','review-report'))
            return doNotPermission();

        $team_type_id = MainTeamType::where('team_type_name','Review')->first()->id;
        $team_id = MainTeam::select('id')->where('team_type',$team_type_id)->get()->toArray();
        $team_id_array = array_values($team_id);
        $data['user_list'] = MainUser::whereIn('user_team',$team_id_array)->get();
        return view('reports.reviews',$data );
    }
    public function reviewsDataTable(Request $request){

        if(Gate::denies('permission','review-report'))
            return doNotPermission();

        $review_user_arr = [];
        $review_list = [];
        $current_month = $request->current_month;
        $current_year = $request->current_year;

        if(isset($request->user_id))
            $user_list = MainUser::where('user_id',$request->user_id)->get();
        else{
            $team_type_id = MainTeamType::where('team_type_name','Review')->first()->id;
            $team_id = MainTeam::select('id')->where('team_type',$team_type_id)->get()->toArray();
            $team_id_array = array_values($team_id);
            $user_list = MainUser::whereIn('user_team',$team_id_array)->get();
        }
        //GET COMBO SERVICE
        $combo_service_list = MainComboService::select('id')->where('cs_form_type',1)->orWhere('cs_form_type',3)->get()->toArray();
        $combo_service_arr = array_values($combo_service_list);

        foreach($user_list as $user){

            $review_total = MainUserReview::where(function ($query) use ($user){
                $query->where('user_id',$user->user_id)
                    ->orWhere('user_id','like','%;'.$user->user_id)
                    ->orWhere('user_id','like','%;'.$user->user_id.";%")
                    ->orWhere('user_id','like',$user->user_id.';%');
            })->latest();

            $task_list = MainTask::where(function($query) use ($user){
                $query->where('assign_to',$user->user_id)
                ->orWhere('assign_to','like','%;'.$user->user_id)
                ->orWhere('assign_to','like','%;'.$user->user_id.';%')
                ->orWhere('assign_to','like',$user->user_id.';%');
            })->whereIn('service_id',$combo_service_arr)->where('content','!=',null)

            ->where(function($query) use($current_year,$current_month){
                $query->whereDate('date_start','<=',$current_year."-".$current_month."-31")
                ->whereDate('date_end','>=',$current_year."-".$current_month."-1");
            });

            
            $review_total->whereMonth('updated_at',$current_month)->whereYear('updated_at',$current_year);
                        
            
            $review_total = $review_total->get();
            $task_list = $task_list->select('content','date_start','date_end')->get();

            $failed_total = $review_total->unique('review_id')->where('status',0)->count();
            $successfully_total  = $review_total->unique('review_id')->where('status',1)->count();

            $review_total = 0;
            $percent_complete = 0;
            foreach($task_list as $task){
                $content = json_decode($task->content,TRUE);

                // if( isset($content['order_review']) && !empty($content['order_review'])){
                //     $review_total += intval($content['order_review']);
                // }elseif(isset($content['number']) && !empty($content['number']) )
                //     $review_total += intval($content['number']);

                if( ((isset($content['order_review']) && !empty($content['order_review']))
                    || (isset($content['number']) && !empty($content['number'])))
                    && ($task->date_start != "" && $task->date_end != "")
                ){
                    //GET REVIEW OF MONTH
                    // $d1 = $task->date_start;
                    // $d2 = $task->date_end;
                    // $count_month = (int)abs((strtotime($d1) - strtotime($d2))/(60*60*24*30));

                    $start_month = Carbon::parse($task->date_start)->format('m');
                    $end_month = Carbon::parse($task->date_end)->format('m');

                    $year_start = Carbon::parse($task->date_start)->format('Y');
                    $year_end = Carbon::parse($task->date_end)->format('Y');

                    $count_year = $year_end - $year_start;

                    if($count_year == 0)
                        $count_month = $end_month - $start_month +1;
                    else
                        $count_month = ($count_year-1)*12+(12-$start_month+1)+$end_month;

                    if(isset($content['order_review']))
                        $review_number = $content['order_review'];
                    elseif(isset($content['number']))
                        $review_number = $content['number'];

                    if($count_month == 0)
                        $review_total += intval($review_number);
                    else{
                        $review_avg_per_month = ceil(intval($review_number)/$count_month);

                        if($current_month == $end_month)
                            $review_total += $review_number - $review_avg_per_month*($count_month-1);
                        else
                            $review_total += $review_avg_per_month;
                    }
                }
            }
            if($review_total > 0)
                $percent_complete = round(($successfully_total/$review_total)*100);

            $review_list[] = [
                'id' => $user->user_id,
                'user' => "<a href='javascript:void(0)' title='View Reviews Today' class='user' id='".$user->user_id."'><span class='text-capitalize'>".$user->getFullname()."</span>(".$user->user_nickname.")</a>",
                'total_reviews' => $review_total,
                'successfully_total' => $successfully_total,
                'failed_total' => $failed_total,
                'percent_complete' => $percent_complete
            ];
        }
        return DataTables::of($review_list)
            ->editColumn('percent_complete',function($row){
                return $row['percent_complete']."%";
            })
            ->rawColumns(['user'])
            ->make(true);
    }
    public function reviewsToday(Request $request){

        $user_id = $request->user_id;
        $successfully_total = 0;
        $failed_total = 0;

        $review_total = MainUserReview::where(function ($query) use ($user_id){
            $query->where('user_id',$user_id)
                ->orWhere('user_id','like','%;'.$user_id)
                ->orWhere('user_id','like','%;'.$user_id.";%")
                ->orWhere('user_id','like',$user_id.';%');
        })->latest();

        $review_total = $review_total->whereDate('updated_at',today())->get();
        $failed_total = $review_total->unique('review_id')->where('status',0)->count();
        $successfully_total  = $review_total->unique('review_id')->where('status',1)->count();

        $data['successfully_total'] = $successfully_total;
        $data['failed_total'] = $failed_total;
        $data['today'] = today()->format('m-d-Y');
        return $data;
    }
    public function ratingCustomer(){

        return view('reports.rating_customer');
    }
    public function ratingCustomerDataTable(Request $request){

        $rating_list = MainCustomerRating::all();

        if($request->rating_level && $request->rating_level != ''){
            $rating_list = $rating_list->where('rating_level',$request->rating_level);
        }
        if($request->start_date != "" && $request->end_date != ""){
            $start_date = Carbon::parse($request->start_date)->subDay(1)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->addDay(1)->format('Y-m-d');
            $rating_list = $rating_list->whereBetween('created_at',[$start_date,$end_date]);
        }

        return Datatables::of($rating_list)
        ->editColumn('order_id',function($row){
            return '<a href="'.route('order-view',$row->order_id).'" title="">#'.$row->order_id.'</a>';
        })
        ->editColumn('rating_level',function($row){
            return ratingCustomer()[$row->rating_level];
        })
        ->editColumn('created_at',function($row){
            return format_datetime($row->created_at);
        })
        ->rawColumns(['order_id'])
        ->make(true);
    }
}
