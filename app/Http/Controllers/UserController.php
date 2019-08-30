<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainUser;
use Auth;
use DataTables;
use DB;
use App\Helpers\ImagesHelper;

class UserController extends Controller 
{    
    public function index(){
        return view('user.list');
    }

    public function editProfile(){
    	$data['user'] = Auth::user(); 
        return view('auth.editprofile',$data);
    }


    public function edit(Request $request){
    	$id = Auth::id();

    	$this->validate($request,[                       
            'confirm_password' =>'same:new_password',
    	],[

    	]);
    	$user = MainUser::where('user_id',$id)->first();
        $user->user_nickname = $request->user_nickname;
    	$user->user_firstname = $request->user_firstname;
    	$user->user_lastname = $request->user_lastname;

        if($request->hasFile('avatar')){
            $user->user_avatar = ImagesHelper::uploadImage($request->hasFile('avatar'),$request->avatar,$user->user_avatar);
            // dd($user->user_avatar);
        }

        if($request->password && $request->new_password && $request->confirm_password){
            if(\Hash::check($request->password,$user->user_password)){
                $user->user_password = bcrypt($request->new_password);               
            }else {            	
                return back()->with('error',"Password don't match");
            }            
        }

    	$user->save();

    	return back()->with('success','Profile have been updated successfully!');
    }

    public function userDataTable(Request $request){

        $user_list = MainUser::join('main_group_user',function($join){
                    $join->on('main_user.user_group_id','main_group_user.gu_id');
                    })
                    ->where('main_group_user.gu_status',1)
                    ->select('main_group_user.gu_name','main_user.user_firstname','main_user.user_lastname','main_user.user_nickname','main_user.user_phone','main_user.user_status','main_user.user_email','main_user.user_id');

        return DataTables::of($user_list)

               ->editColumn('user_fullname',function($row){
                    return $row->user_lastname." ".$row->user_firstname;
               })
               ->editColumn('user_status',function($row){

                if($row->user_status == 1) $checked='checked';
                else $checked="";
                    return '<input type="checkbox" user_id="'.$row->user_id.'" user_status="'.$row->user_status.'" class="js-switch"'.$checked.'/>';
               })
               ->addColumn('action',function($row){
                    return '<a class="btn btn-sm btn-secondary" href=""><i class="fas fa-edit"></i></a>
                    <a class="btn btn-sm btn-secondary" href="#"><i class="fas fa-trash"></i></a>';
               })
               ->rawColumns(['user_status','action'])
               ->make(true);
    }
    public function changeStatusUser(Request $request){

        $user_status = $request->user_status;
        $user_id = $request->user_id;

        if($user_status == 1)
            $user_status = 0;
        else
            $user_status = 1;

        MainUser::where('user_id',$user_id)->update(['user_status'=>$user_status]);
    }
    //ROLES
    public function roleList(){
        return view('user.roles');
    }
    public function roleDatatable(Request $request){

        $role_list = DB::table('main_group_user')->select('gu_id','gu_name','gu_descript','gu_status');

        return DataTables::of($role_list)
                ->editColumn('gu_status',function($row){
                    if($row->gu_status == 1) $checked='checked';
                    else $checked="";
                    return '<input type="checkbox" gu_id="'.$row->gu_id.'" gu_status="'.$row->gu_status.'" class="js-switch"'.$checked.'/>';
                })
                ->addColumn('action',function($row){
                    return '<a class="btn btn-sm btn-secondary" href=""><i class="fas fa-plus"></i></a><a class="btn btn-sm btn-secondary role-edit" href="javascript:void(0)"><i class="fas fa-edit"></i></a>';
                })
                ->rawColumns(['gu_status','action'])
                ->make(true);
    }
    public function changeStatusRole(Request $request){

        $gu_id = $request->gu_id;
        $gu_status = $request->gu_status;

        if($gu_status == 1)

            $gu_status = 0;
        else
            $gu_status = 1;

        DB::table('main_group_user')->where('gu_id',$gu_id)->update(['gu_status'=>$gu_status]);
    }
    public function addRole(Request $request){

        $gu_id = $request->gu_id;
        $gu_name = $request->gu_name;
        $gu_descript = $request->gu_descript;

        if($gu_id > 0){

            $gu_insert = DB::table('main_group_user')->where('gu_id',$gu_id)->update(['gu_name'=>$gu_name,'gu_descript'=>$gu_descript]);
        }else{

            $gu_id_max = DB::table('main_group_user')->max('gu_id')+1;

            $gu_arr = [
                'gu_id' => $gu_id_max,
                'gu_name' => $gu_name,
                'gu_descript' => $gu_descript,
                'gu_role' => 'tyty'
            ];
            $gu_insert = DB::table('main_group_user')->insert($gu_arr);
        }
        if(!isset($gu_insert)){
            return 0;
        }
        else{
            return 1;
        }
    }
}

    
    