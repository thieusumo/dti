<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainLoginBackground;
use DataTables;
use Validator;
use App\Helpers\ImagesHelper;
use Gate;

class SetupLoginBackground  extends Controller
{
	public function index(){

        if(Gate::denies('permission','setup-login-background'))
            return doNotPermission();

		return view('setting.login-background');
	}

	public function datatable(){

        if(Gate::denies('permission','setup-login-background'))
            return doNotPermission();
        
		$data = MainLoginBackground::all();

		return DataTables::of($data)
		->editColumn('image',function($data){
            return "<img style='height: 5rem;' src='".env('PATH_VIEW_IMAGE').$data->image."'>";
        })
        ->addColumn('action', function ($data){
                    return '<a class="btn btn-sm btn-secondary edit" data-id="'.$data->id.'" href="#"><i class="fas fa-edit"></i></a>
                    <a class="btn btn-sm btn-secondary delete" data-id="'.$data->id.'" href="#"><i class="fas fa-trash"></i></a>';
            })
        ->rawColumns(['image','action'])
        ->make(true);
	}

	public function save(Request $request){
        $validate = Validator::make($request->all(),[
            'image' => 'image|max:4096',
        ]);
        // dd($request->image);
        $error_array = [];

        if($validate->fails()){
            foreach ($validate->messages()->getMessages() as $messages) {
                $error_array[] = $messages;
            }
            return response()->json(['status'=>0,"msg"=>$error_array]);
        }

        if($request->hasFile('image')){
            $image = ImagesHelper::uploadImage($request->hasFile('image'),$request->image,'');
        }

        $arr = [
            'image' => $image ?? '',
        ];
        // dd($image);

        if($request->action == "Create"){   
            MainLoginBackground::create($arr);
        } else {
            if(empty($image)){
                unset($arr['image']);
            }

            $data = MainLoginBackground::where('id',$request->id)->first();
            $data->update($arr);
        }
        
        return response()->json(['status'=>1,"msg"=>"Saved successfully!"],200);

    }

    public function delete(Request $request){
        if($request->id){
            $data = MainLoginBackground::where('id',$request->id)->first();
            $data->delete();
            return response()->json(['status'=>1,'msg'=>"Deleted successfully!"]);
        }
    }
}