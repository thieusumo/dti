<?php

namespace App\Http\Controllers\Task;

use App\Models\MainComboService;
use App\Models\MainGroupUser;
use App\Models\MainPermissionDti;
use App\Models\MainUserReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\GeneralHelper;
use App\Helpers\ImagesHelper;
use App\Models\MainTask;
use App\Models\MainTrackingHistory;
use App\Models\MainFile;
use App\Models\MainUser;
use App\Models\MainTeam;
use Carbon\Carbon;
use App\Jobs\SendNotification;
use DataTables;
use Auth;
use Validator;
use DB;
use ZipArchive;
use Laracasts\Presenter\PresentableTrait;
use App\Models\MainNotification;
use Gate;
use App\Models\PosPlace;
use App\Models\MainUserCustomerPlace;

class TaskController extends Controller
{
    use PresentableTrait;
    protected $presenter = 'App\\Presenters\\ThemeMailPresenter';

    public function index(){
        if(Gate::denies('permission','my-task'))
            return doNotPermission();

        $data['user_list'] = MainUser::active()->get();
        $data['service_list'] = MainComboService::where([['cs_type',2],['cs_status',1]])->get();
    	return view('task.my-task',$data);
    }
    public function myTaskDatatable(Request $request){

        $task_list = MainTask::where([['status','!=',3]])->whereNull('task_parent_id');
        if($request->category != "")
            $task_list->where('category',$request->category);
        if($request->service_id != "")
            $task_list->where('service_id',$request->service_id);
        if($request->assign_to && $request->assign_to != ""){
            $assign_to = $request->assign_to;
            $task_list->where(function($query) use($assign_to){
                $query->where('assign_to',$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to.';%')
                    ->orWhere('assign_to','like',$assign_to.';%');
            });
        }
        if($request->priority != "")
            $task_list->where('priority',$request->priority);
        if($request->status != "")
            $task_list->where('status',$request->status);
        if(isset($request->task_dashboard)){
            $task_list->where('status','!=',3);
            $task_list = $task_list->skip(0)->take(5)->get();
        }

    	return DataTables::of($task_list)
    		->editColumn('priority',function($row){
    			return getPriorityTask()[$row->priority];
    		})
    		->editColumn('status',function($row){
    			return getStatusTask()[$row->status];
    		})
    		->addColumn('task',function($row){
    		    if(count($row->getSubTask) >0){
    		        $detail_button = "<i class=\"fas fa-plus-circle details-control text-danger\" id='".$row->id."'></i>";
                }else $detail_button = "";

    			return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> #'.$row->id.'</a>';
    		})
    		->editColumn('order_id',function($row){
    		    if($row->order_id != null)
    			    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
    		})
    		->editColumn('date_start',function($row){
    			if($row->date_start != "")
    				$date_start = Carbon::parse($row->date_start)->format('m/d/Y');
    			else
    				$date_start = "";

    			return $date_start;
    		})
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
    		->editColumn('date_end',function($row){
    			if($row->date_end != "")
    				$date_end = Carbon::parse($row->date_end)->format('m/d/Y');
    			else
    				$date_end = "";

    			return $date_end;
    		})
    		->editColumn('updated_at',function($row){
    			return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
    		})
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
    		->rawColumns(['order_id','task'])
    		->make(true);
    }
    public function postComment(Request $request){
       // return $request->all();
       if(count($request->file_image_list) > 20){
        return response(['status'=>'error','message'=>'Amount of files maximum is 20 files']);
       }
    	$rule = [
    		// 'order_id' => 'required',
            'note' => 'required'
    	];
    	/*$message = [
    		'order_id.required' => 'Order not exist!',
            'note.required' => 'Comment required!'
    	];*/
    	$validator = Validator::make($request->all(),$rule);
    	if($validator->fails())
    		return response([
    			'status'=>'error',
    			'message' => $validator->getMessageBag()->toArray()
    	]);

    	$order_id = $request->order_id;
    	$task_id = $request->task_id;
    	$content = $request->note;
    	$file_list = $request->file_image_list;
    	$current_month = Carbon::now()->format('m');
    	$file_arr = [];

    	$tracking_arr = [
    		'order_id' => $order_id,
    		'task_id' => $task_id==0?NULL:$task_id,
    		'content' => $content,
    		'created_by' => Auth::user()->user_id,
            'email_list' => $request->email_list?implode(';',$request->email_list):"",
            'receiver_id' => $request->receiver_id,
    	];
    	DB::beginTransaction();
    	$tracking_create = MainTrackingHistory::create($tracking_arr);
    	//CHANGE STATUS
        $change_status_task = 'ok';
        if(isset($request->status))
            $change_status_task = MainTask::find($task_id)->update(['status'=>$request->status]);

    	//SAVE NOTIFICATION
        $task_id = "";
        if($tracking_create->task_id != "")
            $task_id = $tracking_create->task_id;
        if($tracking_create->subtask_id != "")
            $task_id = $tracking_create->subtask_id;

        $content = $tracking_create->getUserCreated->user_nickname." created a comment on task #".$task_id;

        $notification_arr = [
            'content' => $content,
            'href_to' => route('task-detail',$task_id),
            'receiver_id' => $tracking_create->receiver_id,
            'read_not' => 0,
            'created_by' => Auth::user()->user_id,
        ];
        $notification_create = MainNotification::create($notification_arr);

        if($file_list != ""){
            //CHECK SIZE IMAGE
            $size_total = 0;
            $files_total = 1;
            foreach ($file_list as $key => $file){
                $size_total += $file->getSize();
                $files_total += 1;
            }
            if($files_total >20)
                return response(['status'=>'error','message'=>'Amount of files maximum is 20 files']);

            $size_total = number_format($size_total / 1048576, 2); //Convert KB to MB
            if($size_total > 50){
                return response(['status'=>'error','message'=>'Total Size Image maximum is 50M!']);
            }
            //Upload Image
            foreach ($file_list as $key => $file) {

                $file_name = ImagesHelper::uploadImage2($file,$current_month,'images/comment/');
                $file_arr[] = [
                    'name' => $file_name,
                    'name_origin' => $file->getClientOriginalName(),
                    'tracking_id' => $tracking_create->id,
                ];
            }
            $file_create = MainFile::insert($file_arr);

            if(!isset($tracking_create) || !isset($file_create) || !isset($notification_create) || !isset($change_status_task))
            {
                DB::callback();
                return response(['status'=>'error', 'message'=> 'Failed!']);
            }
            else{
                DB::commit();
                return response(['status'=> 'success','message'=>'Successly!']);
            }
        }
        if(!isset($tracking_create) || !isset($notification_create))
        {
            DB::callback();
            return response(['status'=>'error', 'message'=> 'Failed!']);
        }
        else{
            DB::commit();
            return response(['status'=> 'success','message'=>'Successly!']);
        }


    }
    public function downImage(Request $request){

        $src_image = $request->src;

        if(file_exists($src_image)){
            return response()->download($src_image);
        }
        else
            return back()->with(['error'=>"Download Failed"]);
    }
    public function taskDetail($id){

        $data['task_info'] = MainTask::find($id);
        $data['id'] = $id;
        $assign_to_arr = explode(';',$data['task_info']->assign_to);
        $data['assign_to'] = MainUser::whereIn('user_id',$assign_to_arr)->get();
        $data['team'] = MainTeam::all();
        $data['user_list'] = MainUser::where('user_id','!=',Auth::user()->user_id)->get();

        if(in_array(Auth::user()->user_id, $assign_to_arr) && $data['assign_to']->count() > 2){
            $data['button'] = 'button';
        }

        return view('task.task-detail',$data);
    }

    public function taskTracking(Request $request){

        $task_id = $request->task_id;

        $order_tracking = MainUser::join('main_tracking_history',function($join){
            $join->on('main_tracking_history.created_by','main_user.user_id');
        })
            ->where('main_tracking_history.task_id',$task_id)
            ->whereNull('main_tracking_history.subtask_id')
            ->select('main_tracking_history.*','main_user.user_firstname','main_user.user_lastname','main_user.user_team','main_user.user_nickname')->get();

        return DataTables::of($order_tracking)

            ->addColumn('user_info',function($row){
                return '<span>'.$row->user_nickname.'('.$row->getFullname().')</span><br>
                        <span>'.format_datetime($row->created_at).'</span><br>
                        <span class="badge badge-secondary">'.$row->getTeam->team_name.'</span>';
            })
            ->addColumn('task',function($row){
                return "<a href='' >Task#".$row->task_id."</a>";
            })
            ->editColumn('content',function($row){
                $file_list = MainFile::where('tracking_id',$row->id)->get();
                $file_name = "<div class='row '>";

                    foreach ($file_list as $key => $file) {
                        $zip = new ZipArchive();

                        if ($zip->open($file->name, ZipArchive::CREATE) !== TRUE) {
                            $file_name .= '<form action="'.route('down-image').'" method="POST"><input type="hidden" value="'.csrf_token().'" name="_token" /><input type="hidden" value="'.$file->name.'" name="src" /><img class="file-comment ml-2" src="'.asset($file->name).'"/></form>';
                        }else{
                            $file_name .= '<form action="'.route('down-image').'" method="POST"><input type="hidden" value="'.csrf_token().'" name="_token" /><input type="hidden" value="'.$file->name.'" name="src" /><a href="javascript:void(0)" class="file-comment ml-2" /><i class="fas fa-file-archive"></i>'.$file->name_origin.'</a></form>';
                        }
                    }

                $file_name .= "</div>";
                return $row->content."<br>".$file_name;
            })
            ->rawColumns(['user_info','task','content'])
            ->make(true);
    }
    public function taskAdd($id = 0){

        if(Gate::denies('permission','create-new-task'))
            return doNotPermission();

        $data['user_list'] = MainUser::all();
        $data['task_parent_id'] = $id;
         $data['task_name'] = "";
         $data['assign_to_team'] = MainTeam::active()->get();

        if($id>0){
            $data['task_name'] = MainTask::find($id)->subject;
        }
        return view('task.add-task',$data);
    }
    public function getTask(Request $request){

        $task_parent_id = $request->task_parent_id;

        $task_name = MainTask::find($task_parent_id);
        if(!isset($task_name))
            return response(['status'=>'error','message'=>'ID Task Correctly!']);
        else{
            if($task_name == "")
                return response(['status'=>'error','message'=>'ID Task Correctly!']);
            else{
                $task_name = strtoupper($task_name->subject);
                return response(['task_name'=>$task_name]);
            }
        }
    }
    public function saveTask(Request $request){
        // return $request->all();
        if($request->complete_percent != null){
            $rule = [
                'complete_percent' => 'integer|between:0,100',
            ];
            $validator = Validator::make($request->all(),$rule);
            if($validator->fails())
                return back()->withErrors($validator)->withInput();
        }
            
        if(Gate::denies('permission','create-new-task'))
            return doNotPermission();

        $subject = $request->subject;

        if($subject == ""){
            return back()->with(['error'=>'Enter Subject, Please!']);
        }

        $input =  $request->all();
        if(is_null($input['complete_percent'] ))
            $input['complete_percent'] = 0;

        if($request->date_start != "")
            $input['date_start'] = format_date_db($request->date_start);
        if($request->date_end != "")
            $input['date_end'] = format_date_db($request->date_end);

        // if(isset($request->cskh_task)){

            if($request->assign_type == 1){
                $assign_arr = [];

                $assign_list = MainUser::active()->where('user_team',$request->assign_to)->where('user_id','!=',Auth::user()->user_id)->select('user_id')->get();
                if($assign_list->count() == 0)
                    return back()->with(['error'=>'Team empty for assign!']);

                foreach ($assign_list as $key => $value) {
                    $assign_arr[] = $value->user_id;
                }
                $assign_list = implode(';', $assign_arr);
            }else{
                if(is_array($request->assign_to))
                    $assign_list = implode(';',$request->assign_to);
                else
                    $assign_list = $request->assign_to; 
            }
            $input['assign_to'] = $assign_list; 
        // }
        if(!isset($request->id)){

            $input['created_by'] = Auth::user()->user_id;
            $input['updated_by'] = Auth::user()->user_id ;
            $task_save = MainTask::create($input);
            //SAVE NOTIFICATION
            $content = Auth::user()->user_nickname. "created a task #".$task_save->id;
            $notification_arr = [
                'content' => $content,
                'href_to' => route('task-detail',$task_save->id),
                'receiver_id' => $input['assign_to'],
                'read_not' => 0,
                'created_by' => Auth::user()->user_id,
            ];
            $notification_create = MainNotification::create($notification_arr);

        }else{

            $task_info = MainTask::find($request->id);

            //PARENT TASK
            $subTaskList = $task_info->getSubTask;

            //CHILD TASK
            if(!empty($task_info->task_parent_id)){
                $parent_task = MainTask::find($task_info->task_parent_id);
                $subTaskList = $parent_task->getSubTask->where('id','!=',$request->id);

            }
            $subTaskTotal = count($subTaskList);

            $complete_percent_sub = 0;
            if( $subTaskTotal > 0 ){
                $complete_percent_total = 0;
                foreach($subTaskList as $subTask){
                    $complete_percent_total += $subTask->complete_percent;
                }
                $complete_percent_sub = $complete_percent_total/$subTaskTotal;
            }


            //GET STATUS TASK FOLLOW PERCENT COMPLETE
            if(!empty($task_info->task_parent_id)){
                //UPDATE PARENT TASK
                if($complete_percent_sub > 0)
                    $parentPercent = ($parent_task->complete_percent + $complete_percent_sub + $input['complete_percent']) / 3;
                else
                    $parentPercent = ($parent_task->complete_percent + $input['complete_percent']) / 2;

                if($parentPercent == 0)
                    $status = 1;
                elseif($parentPercent > 0 && $parentPercent < 100)
                    $status = 2;
                else $status = 3;

                $parent_task->update(['complete_percent'=>$parentPercent,'status'=>$status]);

            }else{

                $input['complete_percent'] +=  $complete_percent_sub;
                if($complete_percent_sub > 0)
                    $input['complete_percent'] = $input['complete_percent']/2;
            }


            if($input['complete_percent'] == 0)
                $input['status'] = 1;
            elseif($input['complete_percent'] > 0 && $input['complete_percent'] < 100)
                $input['status'] = 2;
            else $input['status'] = 3;

            //CHECK SOMETHING CHANGE AFTER EDIT TASK
            $content = "";
            if(isset($request->subject) && $request->subject != $task_info->subject)
                $content .= "Subject has been change from: ".$task_info." to ".$request->subject."<br>";

            if(isset($request->category) && $request->category != $task_info->category)
                $content .= "Category has change from: <span class='text-danger'>".getCategory()[$task_info->category]."</span> to <span class='text-danger'>".getCategory()[$request->category]."</span><br>";

            if(isset($request->priority) && $request->priority != $task_info->priority)
                $content .= "Priority has change from: <span class='text-danger'>".getPriorityTask()[$task_info->priority]."</span> to <span class='text-danger'>".getPriorityTask()[$request->priority]."</span><br>";

            if(isset($request->status) && $request->status != $task_info->status)
                $content .= "Status has been change from: <span class='text-danger'>".getStatusTask()[$task_info->status]."</span> to <span class='text-danger'>".getStatusTask()[$request->status]."</span><br>";

            if(isset($request->date_start) && format_date_db($request->date_start) != $task_info->date_start)
                $content .= "Date Start has been change from: <span class='text-danger'>".$task_info->date_start."</span> to <span class='text-danger'>".format_date_db($request->date_start)."</span><br>";

            if(isset($request->date_end) && format_date_db($request->date_end) != $task_info->date_end)
                $content .= "Date End has been change from: <span class='text-danger'>".$task_info->date_end."</span> to <span class='text-danger'>".format_date_db($request->date_end)."</span><br>";

            if(isset($request->complete_percent) && $request->complete_percent != $task_info->complete_percent)
                $content .= "Complete Percent has been change from: <span class='text-danger'>".$task_info->complete_percent."</span> to <span class='text-danger'>".$request->complete_percent."</span><br>";

            /*if(isset($request->assign_to) && $request->assign_to != $task_info->assign_to){
                $user_list = MainUser::all();
                $user_list = collect($user_list);
                $old_assign = $user_list->where('user_id',$task_info->assign_to)->first()->user_nickname;
                $new_assign =  $user_list->where('user_id',$request->assign_to)->first()->user_nickname;
                $content .= "Assign has been change from: <span class='text-danger'>".$old_assign."</span> to <span class='text-danger'>".$new_assign."</span><br>";
            }*/


            //UPDATE TASK
            $input['updated_by'] = Auth::user()->user_id;
            $task_save = $task_info->update($input);

            //ADD TRACKING HISTORY
            $task_tracking = [
                'order_id' => $task_info->order_id,
                'task_id' => $request->id,
                'created_by' => Auth::user()->user_id,
                'content' => $request->note."<br>".$content,
            ];
            $tracking_history = MainTrackingHistory::create($task_tracking);

            //SAVE NOTIFICATION
            $content = Auth::user()->user_nickname. " updated a task #".$request->id;

            if(isset($request->cskh_task)){

                $assign_list = MainUser::active()->where('user_team',$request->assign_to)->where('user_id','!=',Auth::user()->user_id)->select('user_id')->get();

                foreach ($assign_list as $key => $value) {
                    $notification_arr = [
                        'content' => $content,
                        'href_to' => route('task-detail',$request->id),
                        'receiver_id' => $value->user_id,
                        'read_not' => 0,
                        'created_by' => Auth::user()->user_id,
                    ];
                    $notification_create = MainNotification::create($notification_arr);
                }
            }else{

                $notification_arr[] = [
                    'content' => $content,
                    'href_to' => route('task-detail',$request->id),
                    'receiver_id' => $request->assign_to,
                    'read_not' => 0,
                    'created_by' => Auth::user()->user_id,
                ];
                $notification_create = MainNotification::create($notification_arr);
            }
            if(!isset($task_save) || !isset($tracking_history) || !isset($notification_create))
                return back()->with(['error'=>'Save Error. Check Again, Please!']);
            else
                return redirect()->route('my-task');
        }


        if(!isset($task_save) || !isset($notification_create))
            return back()->with(['error'=>'Save Error. Check Again, Please!']);
        else
            return redirect()->route('my-task');
    }
    public function getSubtask(Request $request){

        $task_id = $request->task_id;

        $subtask_list = MainTask::where('task_parent_id',$task_id);

        return DataTables::of($subtask_list)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->addColumn('task',function($row){
                return '<a href="'.route('task-detail',$row->id).'">#'.$row->id.'</a>';
            })
            ->editColumn('order_id',function($row){
                return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->addColumn('assign_to',function ($row){
                return $row->getAssignTo->user_nickname;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = format_date($row->date_start);
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('assign_to',function($row){
                return $row->getUser->user_nickname;
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = format_date($row->date_end);
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('updated_at',function($row){
                return format_datetime($row->updated_at)." by ".$row->getUpdatedBy->user_nickname;
            })
            ->editColumn('complete_percent',function ($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->rawColumns(['order_id','task'])
            ->make(true);
    }
    public function editTask($id){

        if(Gate::denies('permission','task-update'))
            return doNotPermission();

        $data['user_list'] = MainUser::all();

        $data['task_info'] = MainTask::find($id);

        $data['id'] = $id;

        $data['task_name'] = $data['task_info']->subject;

        return view('task.edit-task',$data);
    }
    public function sendMailNotification(Request $request){

        $rule = [
            'subject' => 'required',
            'message' => 'required',
        ];
        $message = [
            'subject.required' => 'Type Subject',
            'message.required' => 'Type Message',
        ];
        $validator = Validator::make($request->all(),$rule,$message);
        if($validator->fails())
            return response([
                'status' => 'error',
                'message' => $validator->getMessageBag()->toArray()
            ]);
        //GET EMAIL TEAM
        $team_info =  MainTeam::find($request->team);
        $team_email = $team_info->team_email;
        $team_name = $team_info->team_name;


        if(!isset($team_email))
            return response(['status'=>'error','message'=>'Get Email Team Error!']);
        else{
            if($team_email == "")
                return response(['status'=>'error','message'=>'Get Email Team Error!']);
            else{
                // $input =  MainTeam::find($request->team);
                $input['email'] = $team_email;
                $input['name'] = $team_name;
                $input['subject'] = $request->subject;
                $input['message'] = $request->message;
                dispatch(new SendNotification($input));
                return response(['status'=>'success','message'=>'Message has been sent']);
            }
        }
    }
    public function allTask(){

        if(Gate::denies('permission','all-task-admin') && Gate::denies('permission','all-task-leader'))
            return doNotPermission();

        $data['user_list'] = MainUser::active()->get();
        $data['service_list'] = MainComboService::where([['cs_type',2],['cs_status',1]])->get();
        return view('task.all-task',$data);
    }
    public function allTaskDatatable(Request $request){

        // if(Gate::denies('permission','all-task-read'))
        //     return doNotPermission();

        if(Auth::user()->user_group_id == 1)
            $task_list = MainTask::whereNull('task_parent_id');
        else
            $task_list = MainTask::where('updated_by',Auth::user()->user_id)->whereNull('task_parent_id');

        if(Gate::allows('permission','all-task-admin'))
            $task_list = MainTask::whereNull('task_parent_id');
        elseif(Gate::allows('permission','all-task-leader')){
            $users = MainUser::where('user_team',Auth::user()->user_team)->get();
            $task_list = [];
            foreach ($users as $key => $user) {
                $tasks = MainTask::whereNull('task_parent_id')->where(function($query) use ($user) {
                    $query->where('assign_to',$user->user_id)
                    ->orWhere('assign_to','LIKE','%;'.$user->user_id)
                    ->orWhere('assign_to','LIKE','%;'.$user->user_id.';%')
                    ->orWhere('assign_to','LIKE',$user->user_id.';%');
                })->get();
                foreach ($tasks as $key => $task) {
                    $task_list[] = $task;
                }
            }
            $task_list = array_unique($task_list);
        }else
            return doNotPermission();

        if($request->category != "")
            $task_list->where('category',$request->category);
        if($request->service_id != "")
            $task_list->where('service_id',$request->service_id);
        if($request->assign_to && $request->assign_to != ""){
           $assign_to = $request->assign_to;
            $task_list->where(function ($query) use ($assign_to){
                $query->where('assign_to',$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to.';%')
                    ->orWhere('assign_to','like',$assign_to.';%');
            });
        }
        if($request->priority != "")
            $task_list->where('priority',$request->priority);
        if($request->status != "")
            $task_list->where('status',$request->status);

        return DataTables::of($task_list)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->addColumn('task',function($row){
                if(count($row->getSubTask) >0){
                    $detail_button = "<i class='fas fa-plus-circle details-control text-danger' id='".$row->id."'></i>";
                }else $detail_button = "";

                return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> #'.$row->id.'</a>';
            })
            ->editColumn('order_id',function($row){
                if($row->order_id != null)
                    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = Carbon::parse($row->date_start)->format('m/d/Y');
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = Carbon::parse($row->date_end)->format('m/d/Y');
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->editColumn('updated_at',function($row){
                return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
            })
            ->rawColumns(['order_id','task'])
            ->make(true);
    }
    public function cskhTask($id = 0){
        if(Gate::denies('permission','cskh-task'))
            return doNotPermission();

        $role_arr = [];

        $permission_id = MainPermissionDti::where('permission_slug','cskh-task-read')->first()->id;

        $role_list = MainGroupUser::active()
            ->where('gu_id','!=',1)
            ->where(function ($query){
                $query->where('gu_role_new','!=',null)
                    ->orWhere('gu_role_new','!=','');
            })
            ->select('gu_role_new','gu_id')
            ->get();

        foreach ($role_list as $role){
            $permission_list = explode(';',$role->gu_role_new);
            if(in_array($permission_id,$permission_list)){
                $role_arr[] = $role->gu_id;
            }
        }
        $data['user_list'] = MainUser::active()->whereIn('user_group_id',$role_arr)->get();
        $data['task_parent_id'] = $id;
        $data['task_name'] = "";

        if($id>0){
            $data['task_name'] = MainTask::find($id)->subject;
        }
        $data['assign_to_team'] = MainTeam::active()->get();
        return view('task.cskh-task',$data);
    }
    public function getStatusTaskOrder(Request $request){
        $order_id = $request->order_id;
        $task_id = $request->task_id;

        $task_info = MainTask::find($task_id)->status;
        $task_status = getStatusTask()[$task_info];
//        $order_info = Main
    }
    public function getReview(Request $request){

        $task_id = $request->task_id;
//        $task_id = 66;
        $order_review = $request->order_review;
//        $order_review = 10;
        $order_review_list = [];

        $database_review_list = MainUserReview::where('task_id',$task_id)->orderBy('created_at','desc')->get();
        $database_review_list = collect($database_review_list);
//        return  $database_review_list->where('review_id',2)->first();
        //CHECK FAILED REVIEW
//        $count_failed_review = MainUserReview::latest()->get()->unique('review_id')->where('status',0)->count();

        for ($i=1;$i<=$order_review;$i++){
            $note = "";
            $status = "";

            $review_info = $database_review_list->where('review_id',$i)->first();
            if(isset($review_info) && $review_info != ""){
                $note = $review_info->note;
                $status = $review_info->status;
            }

            $order_review_list[] = [
                'id' => $i,
                'name' => 'Review '.$i,
                'note' => $note,
                'status' => $status
            ];
        }
//        return $order_review_list;
        return DataTables::of($order_review_list)
            ->editColumn('status',function($row){
                $status = "";
                if(gettype($row['status']) == "string")
                     $status ="";
                else{
                    if ($row['status'] == 1) {
                        $status = "<span class='text-primary'>SUCCESSFULLY</span>";
                    } elseif ($row['status'] == 0)
                        $status = "<span class='text-danger'>FAILED</span>";
                }
                return $status;
            })
            ->addColumn('action',function($row){
                 return '<a class="btn btn-sm edit-review" note="'.$row['note'].'" review_id="'.$row['id'].'"  status="'.$row['status'].'" ><i class="fas fa-edit"></i></a> ';
            })
            ->rawColumns(['status','action'])
            ->make(true);
    }
    public function saveReview(Request $request){
        $task_id = $request->task_id;
        $review_id = $request->review_id;
        $input = $request->all();
        unset($input['_token']);
        $latest_review = MainUserReview::where([['task_id',$task_id],['review_id',$review_id]])->latest()->first();



        if(isset($input['status']) && isset($input['note']) && $latest_review->note == $input['note'] && $latest_review->status == $input['status'])
            return response(['status'=>'error','message'=>'Failed!Nothing Change to Save']);
        else{
            DB::beginTransaction();
            $update_review = MainUserReview::create($input);
            //GET PERCENT COMPLETE
            $successfully_review = MainUserReview::where('task_id',$task_id)->latest()->get()->unique('review_id')->where('status',1)->count();
            $percent_complete = round($successfully_review/$request->total_order*100);

            if($percent_complete < 100)
                $status = 2;
            else
                $status = 3;

            $task_update = MainTask::find($task_id)->update(['complete_percent'=>$percent_complete,'status'=>$status]);

            if(!isset($update_review) || !isset($task_update))
                return response(['status'=>'error','message'=>'Failed!Save Error']);
            else{
                DB::commit();
                return response(['status'=>'success','message'=>'Successfully! Change Successfully!','percent_complete'=>$percent_complete,'status'=>$status]);
            }
        }
    }
    public function updateAssignTask(Request $request){

        $task_info = MainTask::find($request->task_id);
        if (strpos($task_info->assign_to, ';') !== false) {
            $update_task = $task_info->update(['assign_to'=>Auth::user()->user_id]);
            if(!$update_task)
                return back()->with(['error'=>'Accept Failed!']);
            else
                return redirect()->route('task-detail',$request->task_id);
        }else
            return redirect()->route('my-task')->with(['success'=>'This Task had accpeted by other!Thanks']);
    }
     public function receiverTaskDatatable(Request $request){

        $user_id = Auth::user()->user_id;

        $task_list = MainTask::where([['status','!=',3]])->whereNull('task_parent_id')
        ->where(function($query) use ($user_id) {
            $query->where('assign_to',$user_id)
                    ->orWhere('assign_to','like','%;'.$user_id)
                    ->orWhere('assign_to','like','%;'.$user_id.';%')
                    ->orWhere('assign_to','like',$user_id.';%');
        });
        if($request->category != "")
            $task_list->where('category',$request->category);
        if($request->service_id != "")
            $task_list->where('service_id',$request->service_id);
        if($request->assign_to && $request->assign_to != ""){
            $assign_to = $request->assign_to;
            $task_list->where(function($query) use($assign_to){
                $query->where('assign_to',$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to.';%')
                    ->orWhere('assign_to','like',$assign_to.';%');
            });
        }
        if($request->priority != "")
            $task_list->where('priority',$request->priority);
        if($request->status != "")
            $task_list->where('status',$request->status);
        if(isset($request->task_dashboard)){
            $task_list->where('status','!=',3);
            $task_list = $task_list->skip(0)->take(5)->get();
        }

        return DataTables::of($task_list)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->addColumn('task',function($row){
                if(count($row->getSubTask) >0){
                    $detail_button = "<i class=\"fas fa-plus-circle details-control text-danger\" id='".$row->id."'></i>";
                }else $detail_button = "";

                return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> #'.$row->id.'</a>';
            })
            ->editColumn('order_id',function($row){
                if($row->order_id != null)
                    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = Carbon::parse($row->date_start)->format('m/d/Y');
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = Carbon::parse($row->date_end)->format('m/d/Y');
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('updated_at',function($row){
                return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
            })
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->rawColumns(['order_id','task'])
            ->make(true);
    }
    public function myCreatedTaskDatatable(Request $request){

        $task_list = MainTask::where([['status','!=',3]])->whereNull('task_parent_id')->where('created_by',Auth::user()->user_id);
        if($request->category != "")
            $task_list->where('category',$request->category);
        if($request->service_id != "")
            $task_list->where('service_id',$request->service_id);
        if($request->assign_to && $request->assign_to != ""){
            $assign_to = $request->assign_to;
            $task_list->where(function($query) use($assign_to){
                $query->where('assign_to',$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to)
                    ->orWhere('assign_to','like','%;'.$assign_to.';%')
                    ->orWhere('assign_to','like',$assign_to.';%');
            });
        }
        if($request->priority != "")
            $task_list->where('priority',$request->priority);
        if($request->status != "")
            $task_list->where('status',$request->status);
        if(isset($request->task_dashboard)){
            $task_list->where('status','!=',3);
            $task_list = $task_list->skip(0)->take(5)->get();
        }

        return DataTables::of($task_list)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->addColumn('task',function($row){
                if(count($row->getSubTask) >0){
                    $detail_button = "<i class=\"fas fa-plus-circle details-control text-danger\" id='".$row->id."'></i>";
                }else $detail_button = "";

                return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> #'.$row->id.'</a>';
            })
            ->editColumn('order_id',function($row){
                if($row->order_id != null)
                    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = Carbon::parse($row->date_start)->format('m/d/Y');
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = Carbon::parse($row->date_end)->format('m/d/Y');
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('updated_at',function($row){
                return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
            })
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->rawColumns(['order_id','task'])
            ->make(true);
    }
    public function taskDashboardDatatable(){

        $task_list = MainTask::getListPendingTasks();

        if(Gate::allows('permission','dashboard-admin'))
            $tasks = $task_list->skip(0)->take(5)->get();
        elseif(Gate::allows('permission','dashboard-leader'))
            $tasks = array_slice($task_list, 0, 5, true);
        else
            $tasks = $task_list->skip(0)->take(5)->get();

        return DataTables::of($tasks)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->editColumn('order_id',function($row){
                if($row->order_id != null)
                    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = Carbon::parse($row->date_start)->format('m/d/Y');
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = Carbon::parse($row->date_end)->format('m/d/Y');
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('updated_at',function($row){
                return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
            })
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->editColumn('subject',function($row){
                if(count($row->getSubTask) >0){
                    $detail_button = "<i class=\"fas fa-plus-circle details-control text-danger\" id='".$row->id."'></i> ";
                }else $detail_button = "";

                return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> '.$row->subject.'</a> ';
            })
            ->rawColumns(['order_id','subject'])
            ->make(true);
    }
    public function getAssignTo(Request $request){

        $assign_type = $request->assign_type;
        $assign_to = '';

        if($assign_type == 1){
            $teams = MainTeam::active()->where('id','!=',Auth::user()->user_team)->get();
            foreach ($teams as $key => $team) {
                $assign_to .= "<option value=".$team->id." >".$team->team_name."</option>";
            }

        }else{
            $users = MainUser::active()->where('user_id','!=',Auth::user()->user_id)->get();
            foreach ($users as $key => $user) {
                $assign_to .= "<option value=".$user->user_id." >".$user->user_nickname."(".$user->getFullname().")</option>";
            }
        }
        return $assign_to;
    }
    public function searchCustomerTask(Request $request){
        $business_phone_customer = $request->business_phone_customer;
        //CHECK CUSTOMER EXISTED
        $check_customer = PosPlace::where('place_phone',$business_phone_customer)->first();
        if(!isset($check_customer))
            return response(['status'=>'error','message'=>'Customer NOT existed!']);

        //CHECK SALE'S CUSTOMER
        if(Gate::denies('permission','customer-search')){
            $check_place = MainUserCustomerPlace::where([['user_id',Auth::user()->user_id],['place_id',$check_customer->place_id]])->first();
            if(!isset($check_place))
                return response(['status'=>'error','message'=>'You do NOT include this customer']);
            return response(['status'=>'success','place_info'=>$check_customer]); 
        }
        else{
            $check_place = MainUserCustomerPlace::where([['place_id',$check_customer->place_id]])->first();
            if(!isset($check_place))
                return response(['status'=>'error','message'=>'This customer has not bought service. Check again!']);
            return response(['status'=>'success','place_info'=>$check_customer]); 
        }
    }
    public function taskExpiredDashboardDatatable(){

        $today = today();
        $date_add_a_month = Carbon::parse(now())->addMonth(1)->format('Y-m-d');

         if(Gate::allows('permission','dashboard-admin')){
            $tasks = MainTask::where('complete_percent','!=',"100")->whereBetween('date_end',[$today,$date_add_a_month])->skip(0)->take(5)->get();
        }
        elseif(Gate::allows('permission','dashboard-leader')){
            //GET USER OF TEAM
            $users = MainUser::where('user_team',Auth::user()->user_team)->get();
            $task_list = [];
            foreach ($users as $key => $user) {
                $tasks = MainTask::where('complete_percent','!=',"100")->where(function($query) use ($user) {
                    $query->where('assign_to',$user->user_id)
                    ->orWhere('assign_to','LIKE','%;'.$user->user_id)
                    ->orWhere('assign_to','LIKE','%;'.$user->user_id.';%')
                    ->orWhere('assign_to','LIKE',$user->user_id.';%');
                })
                ->whereBetween('date_end',[$today,$date_add_a_month])
                ->get();
                foreach ($tasks as $key => $task) {
                    $task_list[] = $task;
                }
            }
            $task_list = array_unique($task_list);
            $tasks = array_slice($task_list, 0, 5, true);
        }
        else{
            $tasks = MainTask::where('complete_percent','!=',"100")
                        ->where(function($query) {
                            $query->where('assign_to',Auth::user()->user_id)
                            ->orWhere('assign_to','LIKE','%;'.Auth::user()->user_id)
                            ->orWhere('assign_to','LIKE','%;'.Auth::user()->user_id.';%')
                            ->orWhere('assign_to','LIKE',Auth::user()->user_id.';%');
                        })
                        ->whereBetween('date_end',[$today,$date_add_a_month])
                        ->skip(0)->take(5)->get();
            }
                    
        return DataTables::of($tasks)
            ->editColumn('priority',function($row){
                return getPriorityTask()[$row->priority];
            })
            ->editColumn('status',function($row){
                return getStatusTask()[$row->status];
            })
            ->editColumn('order_id',function($row){
                if($row->order_id != null)
                    return '<a href="'.route('order-view',$row->order_id).'">#'.$row->order_id.'</a>';
            })
            ->editColumn('date_start',function($row){
                if($row->date_start != "")
                    $date_start = Carbon::parse($row->date_start)->format('m/d/Y');
                else
                    $date_start = "";

                return $date_start;
            })
            ->editColumn('category',function($row){
                return getCategory()[$row->category];
            })
            ->editColumn('date_end',function($row){
                if($row->date_end != "")
                    $date_end = Carbon::parse($row->date_end)->format('m/d/Y');
                else
                    $date_end = "";

                return $date_end;
            })
            ->editColumn('updated_at',function($row){
                return Carbon::parse($row->updated_at)->format('m/d/Y h:i A');
            })
            ->editColumn('complete_percent',function($row){
                if(!empty($row->complete_percent))
                    return $row->complete_percent."%";
            })
            ->editColumn('subject',function($row){
                if(count($row->getSubTask) >0){
                    $detail_button = "<i class=\"fas fa-plus-circle details-control text-danger\" id='".$row->id."'></i> ";
                }else $detail_button = "";

                return $detail_button.'&nbsp&nbsp<a href="'.route('task-detail',$row->id).'"> #'.$row->id." ".$row->subject.'</a> ';
            })
            ->rawColumns(['order_id','subject'])
            ->make(true);
    }
}
