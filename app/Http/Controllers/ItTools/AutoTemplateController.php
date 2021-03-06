<?php

namespace App\Http\Controllers\ItTools;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DataTables;
use App\Helpers\GeneralHelper;
use Validator;
use App\Models\PosTemplateType;
use App\Models\PosTemplate;
use App\Models\PosCateService;
use Gate;

class AutoTemplateController extends Controller
{
    public function index(){
        if(Gate::denies('permission','couponpromotion-template'))
            return doNotPermission();
    	$data['templateType'] = PosTemplateType::getAll();
        return view('tools.auto-template',$data);
    }

    public function getAutoTemplateDatatable(Request $request){
        return PosTemplate::getDatatableByPlaceId(null,$request->type);
    }

    public function getAutoTemplateById(Request $request){
    	$template = PosTemplate::getByPlaceIdAndId($request->id, null);

    	return response()->json(['status'=>1,'data'=>$template]);
    }

    public function deleteAutoTemplate(Request $request){

        if(Gate::denies('permission','couponpromotion-template'))
            return doNotPermission();

    	$template = PosTemplate::deleteByIdAndPlaceId($request->id, null);
        // dd($template);

    	return response()->json(['status'=>1,'msg'=>'deleted successfully']);
    }

    public function saveAutoTemplate(Request $request){

    	$template = PosTemplate::saveAuto($request->id,  null, $request->title,$request->color, $request->discount, $request->discountType, $request->image, $request->services, $request->templateType,$request->type);
    	
    	return response()->json(['status'=>1,'msg'=>'saved successfully']);
    }

    public function getServicesByPlaceId(Request $request){
        if($request->placeId){
            $services = PosCateService::getCateServicesByPlaceId($request->placeId);

            return response()->json(['status'=>1,'data'=>$services]);
        }
    }
}
