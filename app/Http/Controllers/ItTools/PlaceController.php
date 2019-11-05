<?php

namespace App\Http\Controllers\ItTools;

use App\Models\MainComboServiceBought;
use App\Models\MainCustomerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\PosPlace;
use App\Models\PosUser;
use DataTables;
use App\Helpers\GeneralHelper;
use App\Helpers\RunShFileHelper;
use Validator;
// use App\Http\Controllers\ItTools\WebsiteThemeController;
use App\Models\MainTheme;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\PosWebsiteProperty;

use App\Models\PosTemplate;

use DB;



class PlaceController extends Controller
{
    public function index(){
        return view('tools.place');
    }
    public function cloneUpdateWebsite(Request $request)
    {

        $place = PosPlace::getPlaceIdByLicense($request->get_license);

        $place->place_theme_code = $request->get_code;
        $place->save();

        $placeId = $place->place_id;

        PosWebsiteProperty::cloneUpdate($request->id_properties,$placeId);

        //run sh file
        return response()->json(['status'=>1,'msg'=>"Clone website successfully!"]);
    }


    public function getPlacesDatatable(){
        $places = PosPlace::select('place_id','place_name','place_address','place_email','place_phone','place_ip_license','created_at')
            ->where('place_status',1)
            ->get();

        return DataTables::of($places)
        ->editColumn('action',function($places){
            return '<a class="btn btn-sm btn-secondary view" data-id="'.$places->place_id.'" href="#" data-toggle="tooltip" title="View users"><i class="fas fa-user-cog"></i></a>
            <a class="btn btn-sm btn-secondary detail" data-id="'.$places->place_id.'" href="#" data-toggle="tooltip" title="Detail"><i class="fas fa-eye"></i></a>
            <a class="btn btn-sm btn-secondary setting" data-license="'.$places->place_ip_license.'" href="#" data-toggle="tooltip" title="Setting place theme"><i class="fas fa-cogs"></i></a>
            <a class="btn btn-sm btn-secondary btn-custom-properties" data-id="'.$places->place_id.'" href="#" data-toggle="tooltip" title="Custom properties"><i class="fas fa-project-diagram"></i></a>
            <a class="btn btn-sm btn-secondary btn-auto-coupon" data-id="'.$places->place_id.'" href="#" data-toggle="tooltip" title="Auto coupon"><i class="fas fa-images"></i></a>
            <a class="btn btn-sm btn-secondary extension-service" place_id="'.$places->place_id.'" href="javascript:void(0)" title="Extension for Place"><i class="fas fa-shopping-cart"></i></a>'
        })
        ->editColumn('created_at',function($places){
            return format_datetime($places->created_at);
        })
        ->rawColumns(['action'])
        ->make(true);
    }
    /**
     * get customer datatable by placeId
     * @param  $request->placeId
     * @return
     */
    public function getUsersDatatable(Request $request){
        $customers = PosUser::select('user_id','user_nickname','user_phone','user_email','created_at')
                        ->where('user_status',1)
                        ->where('enable_status',1)
                        ->where('user_place_id',$request->placeId)
                        ->get();

        return DataTables::of($customers)
        ->editColumn('created_at',function($customers){
            return format_datetime($customers->created_at);
        })
        ->make(true);
    }
    /**
     * update new password a user of places
     * @param  $request->placeId
     * @param  $request->userId
     * @param  $request->newPassword
     * @param  $request->confirmPassword
     * @return json
     */
    public function changeNewPassword(Request $request){
        $validate = Validator::make($request->all(),[
            'newPassword' => 'required',
            'confirmPassword' => 'required|same:newPassword',
        ]);
        $errorArr = [];
        if($validate->fails()){
            foreach ($validate->messages()->getMessages() as $messages) {
                $errorArr[] = $messages;
            }
            return response()->json(['status'=>0,'msg'=>$errorArr]);
        }

        $user = PosUser::where('user_id',$request->userId)
                        ->where('user_place_id',$request->placeId)
                        ->update([
                            'user_password' => bcrypt($request->newPassword),
                        ]);

        return response()->json(['status'=>1,'msg'=>"Changed successfully!"]);

    }
    /**
     * get detail place by placeId
     * @param  $request->placeId
     * @return json
     */
    public function getDetailPlace(Request $request){
        $place = PosPlace::select(
                                    'place_name','place_address','place_taxcode',
                                    'place_email','place_amount','place_description',
                                    'place_actiondate','place_logo','place_favicon',
                                    'hide_service_price','place_worker_mark_bonus',
                                    'place_interest','place_website','place_phone'
                                )
                ->where('place_id',$request->placeId)
                ->where('place_status',1)
                ->first();
        $place_actiondate = json_decode($place->place_actiondate,true);


        $result = [
            'place' =>$place,
            'place_actiondate' => $place_actiondate,
        ];

        return response()->json(['status'=>1,'data'=>$result]);
    }

    public function getThemeDatatable(){
        return MainTheme::getDatatable();
    }

    public function getThemeProperties(Request $request){
        if($request->themeId){
            $properties = MainThemeProperties::getThemePropertiesByThemeId($request->themeId);

            return response()->json(['status'=>1,'data'=>$properties]);
        }
    }
    public function getServicePlace(Request $request){
        $place_id = $request->place_id;
        $service_combo_list = MainCustomerService::active()
            ->with('getComboService')
            ->where('cs_place_id',$place_id)
            ->get();
        if(!isset($service_combo_list))
            return response(['status'=>'error','message'=>'Get Service Error']);
        else
            return response(['status'=>'success','service_combo_list'=>$service_combo_list]);
    }
    public function saveExpireDate(Request $request){

        $cs_id = $request->cs_id;
        $expire_date = $request->expire_date;
        $count_1 = 0;
        $count_2 = 0;

        DB::beginTransaction();

        foreach ($expire_date as $key => $date){
            if($date == null){}
            else{
                $count_1++;
                $update_cs = MainCustomerService::where('cs_place_id',$request->place_id)
                    ->where('cs_service_id',$cs_id[$key])
                    ->update(['cs_date_expire'=>format_date_db($date)]);
                if(isset($update_cs))
                    $count_2++;
            }
        }
        if($count_1 != $count_2){
            DB::callback();
            return response(['status'=>'error','message'=>'Failed!']);
        }
        else{
            DB::commit();
            return response(['status'=>'success','message'=>'Successfully!']);
        }

    }

    public function getWpDatableByPlaceId(Request $request){
        return PosWebsiteProperty::getDatatableByPlaceId($request->placeId);
    }

    public function deleteValueProperty(Request $request){
        if($request->id){
            PosWebsiteProperty::deleteByIdAndPlaceId($request->id,$request->placeId);
            return response()->json(['status'=>1,'msg'=>"Deleted successfully!"]);
        }
    }

    public function saveCustomValueProperty(Request $request){
       PosWebsiteProperty::saveValue($request->variable,$request->name,$request->value,$request->image,$request->action,$request->placeId); 
       return response()->json(['status'=> 1,"msg"=>"Saved successfully"]);
    }

    public function getAutoCouponDatatable(Request $request){
        return PosTemplate::getDatatableByPlaceId($request->placeId);
    }

    public function saveAutoCoupon(Request $request){
        
    }

    public function deleteAutoCoupon(Request $request){
        if($request->id){
            PosTemplate::deleteByIdAndPlaceId($request->id, $request->placeId);

            return response()->json(['status'=>1,'msg'=>"deleted successfully"]);
        }
    }

    public function getAutoCouponById(Request $request){
        if($request->id){
            $coupon = PosTemplate::getByPlaceIdAndId($request->id, $request->placeId);

            return response()->json(['status'=>1,'data'=>$coupon]);
        }
    }

    
}

