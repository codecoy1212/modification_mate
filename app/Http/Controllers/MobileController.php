<?php

namespace App\Http\Controllers;

use App\Jobs\ForgetPassJob;
use App\Mail\ForgetPassMail;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\SubGoal;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MobileController extends Controller
{
    public function signup(Request $request)
    {
        // return "HELLO";
        // return $request->all();

        $validator = Validator::make($request->all(),[
            'name'=> 'required|min:3',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'username' => 'required|min:3|unique:users,username',
            'password' => 'required|min:6',
            // 'c_password' => 'required|same:password',
            'picture' => 'mimes:jpeg,bmp,png,jpg|max:5120',
        ], [
            'name.required' => 'Please enter your Name.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Please enter your Email.',
            'email.unique' => 'Email is already registered.',
            'email.email' => 'Email is invalid.',
            'username.required' => 'Please enter your Username',
            'username.min' => 'Username must be at least 3 characters.',
            'username.unique' => 'Username must be unique',
            'pasword.required' => 'Please enter your password.',
            'pasword.min' => 'Password Not Less Than 6 digits.',
            // 'c_pasword.required' => 'Please confirm your password.',
            // 'c_pasword.min' => 'Password Not Less Than 6 digits.',
            'picture.mimes' => 'Picture Is Not Valid or Larger than 5 MB.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            $var = new User;
            $var->name = $request->name;
            $var->email = $request->email;
            $var->username = $request->username;
            $var->password = $request->password;

            if($request->picture)
            {
                $vbl3 = rand(100000000000000,999999999999999);
                $vbl4 = File::extension($request->picture->getClientOriginalName());
                request()->picture->storeAs('public/pictures',$vbl3.".".$vbl4);
                $var->picture = $vbl3.".".$vbl4;
                $var->save();
            }
            else
            {
            $var->picture = "default-user.jpg";
            $var->save();
            }

            $str['status']=true;
            $str['message']="NEW USER CREATED";
            $str['data']=$var;
            return $str;
        }
    }

    public function login(Request $request)
    {
        // return "hello";

        if($request->device_token == null || $request->device_token == "")
        {
            $str['status']=false;
            $str['message']="DEVICE TOKEN REQUIRED";
            return $str;
        }

        $eml = $request->email;
        $pwd = $request->password;
        $dbpwd = "";
        $verification = User::where('email',$eml) -> first();
        // echo $verification;

        if($verification)
        {
            if($pwd == $verification->password)                  //main directory is here
            {
                $verification->device_token = $request->device_token;
                $verification->update();

                $token = $verification->createToken($verification->email)->plainTextToken;

                $dbpwd = $verification->password;
                $str['status']=true;
                $str['message']="STUDENT LOGGED IN";
                $str['data']=$verification;
                $str['token']=$token;
                return $str;
            }
            else
            {
                $validator = Validator::make($request->all(),[
                'password' => ['required',Rule::in($dbpwd)],
                ], [
                'password.in' => 'Password is Incorrent.',
                'password.required' => 'Please enter your password.',
                ]);

                if ($validator->fails())
                {
                    $str['status']=false;
                    $error=$validator->errors()->toArray();
                    foreach($error as $x_value){
                        $err[]=$x_value[0];
                    }
                    $str['message'] =$err['0'];
                    return $str;
                }
            }

        }
        else
        {
            $validator = Validator::make($request->all(),[
            'email'=>'required|exists:users,email|email:rfc,dns',
            'password' => 'required',
            ], [
            'password.required' => 'Please enter your Password.',
            'email.required' => 'Please enter your Email.',
            'email.exists' => 'Email is not Registered.',
            'email.email' => 'Email is Invalid.',
            ]);

            if ($validator->fails())
            {
                $str['status']=false;
                $error=$validator->errors()->toArray();
                foreach($error as $x_value){
                    $err[]=$x_value[0];
                }
                $str['message'] =$err['0'];
                // $str['data'] = $validator->errors()->toArray();
                return $str;
            }
        }
    }

    public function logout(Request $request)
    {
        // return $request;

        $vbl = User::find($request->user_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="LOGIN ID DOES NOT EXIST";
            return $str;
        }
        else
        {
            // $request->user()->currentAccessToken()->delete();
            $request->user()->tokens()->delete();
            $str['status']=true;
            $str['message']="USER LOG OUT SUCCESSFULL";
            return $str;
        }
    }

    public function profile(request $request){

        $vbl = User::find($request->user_id);
        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="STUDENT PROFILE NOT FOUND";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="STUDENT PROFILE SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function profile_updated(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=> 'required|numeric|exists:users,id',
            'name'=> 'required|min:3',
            'picture' => 'mimes:jpeg,bmp,png,jpg|max:5120',

        ], [
            'name.required' => 'Please enter your Name.',
            'name.min' => 'Name must be at least 3 characters.',
            'picture.mimes' => 'Picture Is Not Valid or Larger than 5 MB.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $var = User::find($request->user_id);
            $var->name = $request->name;

            if($request->picture)
            {
                $vbl3 = rand(100000000000000,999999999999999);
                $vbl4 = File::extension($request->picture->getClientOriginalName());
                request()->picture->storeAs('public/pictures',$vbl3.".".$vbl4);
                $var->picture = $vbl3.".".$vbl4;
            }
            $var->update();

            $str['status']=true;
            $str['message']="USER UPDATED";
            $str['data']=$var;
            return $str;

        }
    }

    public function password_update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=> 'required|numeric|exists:users,id',
            'old_password' => 'required|min:6',
            'new_password' => 'required|min:6',
        ],[
            'user_id.required'=>'Please Enter User Id',
            'user_id.exists'=>'User Not Found',
            'old_password.required'=>'Enter Your Old Password',
            'old_password.min'=>'Password Not Less Than 6 Digits',
            'new_password.required'=>'Enter Your New Password',
            'new_password.min'=>'Password Not Less Than 6 Digits',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $var = User::find($request->user_id);
            if($request->old_password == $var->password)
            {
                $var->password = $request->new_password;
                $var->update();

                $str['status']=true;
                $str['message']="PASSWORD UPDATED";
                $str['data']=$var;
                return $str;
            }
            else
            {
                $str['status']=false;
                $str['message']="Old Password Is Incorrect!";
                return $str;
            }
        }
    }

    ################################################################################################

    public function add_task(Request $request)
    {
        // return $request;

        $validator = Validator::make($request->all(),[
            'from_user'=> 'required',
            'to_user'=> 'required',
            'goal_title'=> 'required|min:2',
            'goal_description' => 'min:2',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:h:i:s A',
            'picture' => 'required',
            'sub_goals' => 'required',
        ], [
            'from_user.required' => 'Please enter Logged In User ID.',
            'to_user.required' => 'Please enter Assigned To User ID.',
            'goal_title.required' => 'Please enter Goal Title.',
            'goal_title.min' => 'Goal Title must be at least 2 characters.',
            'goal_description.min' => 'Goal Description must be at least 2 characters.',
            'end_date.required' => 'End Date is required.',
            'end_date.date_format' => 'Date format is Incorrect.',
            'end_time.required' => 'End Time is required.',
            'end_time.date_format' => 'Time format is Incorrect.',
            'picture.required' => 'Task Picture Is Required.',
            'sub_goals.required' => 'There must be at least one Sub Goal.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            if($request->sub_goals[0])
            {
                // echo"hello";
                // echo $request->sub_goals[0];
                foreach ($request->sub_goals as $value) {
                    // echo "hello";
                    if($value['title'] == "")
                    {
                        $str['status']=false;
                        $str['message']="There must be at least one Sub Goal.";
                        return $str;
                    }
                }

                // echo "HELLO";
                $var = new Task;
                $var->title = $request->goal_title;
                $var->description = $request->goal_description;
                $var->end_date = $request->end_date;
                $var->end_time = $request->end_time;
                $var->picture = $request->picture;
                $var->from_user = $request->from_user;
                $var->to_user = $request->to_user;
                $var->task_status = "PENDING";
                $var->save();

                foreach ($request->sub_goals as $value) {
                    $var3 = new SubGoal;
                    $var3->task_id = $var->id;
                    $var3->title = $value['title'];
                    $var3->description = $value['description'];
                    $var3->rating = 0;
                    $var3->save();
                }


                $var4 = new Notification;
                $var4->task_id = $var->id;
                $var4->notification_type = "ADDED";
                $var4->notification_msg = "New Behaviour Request.";
                $var4->from_user = $request->from_user;
                $var4->to_user = $request->to_user;
                $var4->status = 0;
                $var4->save();


                $str['status']=true;
                $str['message']="NEW TASK CREATED & SENT (PENDING).";
                $str['task_id']=$var->id;
                return $str;
            }
            else
            {
                $str['status']=false;
                $str['message']="There must be at least one Sub Goal.";
                return $str;
            }
        }
    }

    public function get_tasks(Request $request)
    {
        // return "hello";
        if($request->assign_type == "from")
        {
            $vbl0 = User::where('id',$request->user_id)->first();
            if(empty($vbl0))
            {
                $str['status']=false;
                $str['message']="User does not Exist.";
                return $str;
            }
            // $vbl2 = UserTask::where('to_user',$request->user_id)->get();
            $vbl2 = DB::table('tasks')
            ->where('to_user','=',$request->user_id)
            ->where('task_status','=',"ON_GOING")
            ->join('users','users.id','=','tasks.to_user')
            ->join('users as users2','users2.id','=','tasks.from_user')
            ->select('users.name as to_user_name','users2.name as from_user_name','tasks.id','tasks.title','tasks.end_date','tasks.end_time','tasks.to_user','tasks.from_user','tasks.task_status')
            ->get();

            if(count($vbl2) == 0)
            {
                $str['status']=false;
                $str['message']="NO TASKS IN THE LIST";
                return $str;
            }
            else
            {
                $str['status']=true;
                $str['message']="ASSIGNED TO ME TASKS SHOWN";
                $str['data']= $vbl2;
                return $str;
            }
        }
        else if($request->assign_type == "to")
        {
            $vbl1 = User::where('id',$request->user_id)->first();
            if(empty($vbl1))
            {
                $str['status']=false;
                $str['message']="User does not Exist.";
                return $str;
            }
            $vbl3 = DB::table('tasks')
            ->where('from_user','=',$request->user_id)
            ->where(function($q){
                $q->where('task_status','=',"ON_GOING")
                // ->orWhere('task_status','=',"R_PENDING")
                ->orWhere('task_status','=',"PENDING");
            })
            ->join('users','users.id','=','tasks.from_user')
            ->join('users as users2','users2.id','=','tasks.to_user')
            ->select('users.name as from_user_name','users2.name as to_user_name','tasks.id','tasks.title','tasks.end_date','tasks.end_time','tasks.from_user','tasks.to_user','tasks.task_status')
            ->get();
            if(count($vbl3) == 0)
            {
                if(count($vbl3) == 0)
                {
                    $str['status']=false;
                    $str['message']="NO TASKS GIVEN YET";
                    return $str;
                }
            }
            else
            {
                $str['status']=true;
                $str['message']="GIVEN TO OTHERS TASKS SHOWN";
                $str['data']= $vbl3;
                return $str;
            }
        }
        else
        {
            $str['status']=false;
            $str['message']="ASSIGN TYPE NOT GIVEN";
            return $str;
        }
    }

    public function get_users(Request $request)
    {
        $curr_user = $request->user();
        // return $curr_user;
        $vbl = User::where('id','!=',$curr_user->id)->get();
        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO USER ADDED YET.";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL USERS SHOWN";
            $str['data']= $vbl;
            return $str;
        }
    }

    public function get_task_detail(Request $request)
    {
        if($request->task_status == null)
        {
            $str['status']=false;
            $str['message']="TASK STATUS NULL";
            return $str;
        }
        else if($request->task_status == "ON_GOING" || $request->task_status == "PENDING")
        {
            $vbl2 = Task::where('id',$request->task_id)
            ->where(function($q){
                $q->where('task_status',"ON_GOING")
                ->orWhere('task_status',"PENDING");
            })

            ->first();
            // return $vbl2;

            if(empty($vbl2))
            {
                $str['status']=false;
                $str['message']="TASK NOT AVAILABLE OR DOES NOT EXIST";
                return $str;
            }
            else
            {
                $vbl3 = SubGoal::where('task_id',$vbl2->id)->get();
                // return $vbl3;

                $str['status']=true;
                $str['message']="PENDING OR ON GOING TASK DETAIL SHOW";

                if($vbl2->task_status == "PENDING")
                {
                    $vbl8 = Notification::where('task_id',$vbl2->id)->where('notification_type',"ADDED")->first();
                    // return $vbl8;
                    $vbl2->notification_type = $vbl8->notification_type;
                }

                if($vbl2->task_status == "ON_GOING")
                {
                    $vbl8 = Notification::where('task_id',$vbl2->id)->where('notification_type',"ACCEPT")->first();
                    // return $vbl8;
                    $vbl2->notification_type = $vbl8->notification_type;
                }

                $vbl333 = User::find($vbl2->from_user);
                $vbl2->from_device_token = $vbl333->device_token;

                $vbl334 = User::find($vbl2->to_user);
                $vbl2->to_device_token = $vbl334->device_token;

                $vbl2->to_user_name = $vbl334->name;
                $vbl2->from_user_name = $vbl333->name;

                $str['data']['task_details']= $vbl2;
                $str['data']['sub_goals']= $vbl3;
                return $str;
            }
        }
        else if($request->task_status == "COMPLETED")
        // || $request->task_status == "R_PENDING"
        {
            $vbl2 = Task::where('id',$request->task_id)
            ->where(function($q){
                $q->where('task_status',"COMPLETED");
                // ->orWhere('task_status',"R_PENDING");
            })->first();

            // return $vbl2;

            if(empty($vbl2))
            {
                $str['status']=false;
                $str['message']="TASK HISTORY NOT AVAILABLE OR DOES NOT EXIST";
                return $str;
            }
            else
            {
                $vbl3 = SubGoal::where('task_id',$vbl2->id)->get();
                // return $vbl3;

                $str['status']=true;
                $str['message']="COMPLETED OR R_PENDING TASK DETAIL SHOW";

                if($vbl2->task_status == "COMPLETED")
                {
                    $vbl8 = Notification::where('task_id',$vbl2->id)->where('notification_type',"RATING")->first();
                    // return $vbl8;
                    if(!empty($vbl8))
                    $vbl2->notification_type = $vbl8->notification_type;
                    else
                    $vbl2->notification_type = "RATING";
                }

                $vbl333 = User::find($vbl2->from_user);
                $vbl2->from_device_token = $vbl333->device_token;

                $vbl334 = User::find($vbl2->to_user);
                $vbl2->to_device_token = $vbl334->device_token;

                $vbl2->to_user_name = $vbl334->name;
                $vbl2->from_user_name = $vbl333->name;

                $str['data']['task_details']= $vbl2;
                $str['data']['sub_goals']= $vbl3;
                return $str;

            }
        }else if ($request->task_status == "REQ_MODIFY" ) {
            $vbl2 = Task::where('id',$request->task_id)
            ->where('task_status',"REQ_MODIFY")->first();

            // return $vbl2;

            if(empty($vbl2))
            {
                $str['status']=false;
                $str['message']="TASK HISTORY NOT AVAILABLE OR DOES NOT EXIST";
                return $str;
            }
            else
            {
                $vbl3 = SubGoal::where('task_id',$vbl2->id)->get();
                // return $vbl3;

                $str['status']=true;
                $str['message']="COMPLETED OR R_PENDING TASK DETAIL SHOW";

                if($vbl2->task_status == "REQ_MODIFY")
                {
                    $vbl8 = Notification::where('task_id',$vbl2->id)->where('notification_type',"MODIFY")->first();
                    // return $vbl8;
                    $vbl2->notification_type = $vbl8->notification_type;
                }

                $vbl333 = User::find($vbl2->from_user);
                $vbl2->from_device_token = $vbl333->device_token;

                $vbl334 = User::find($vbl2->to_user);
                $vbl2->to_device_token = $vbl334->device_token;

                $vbl2->to_user_name = $vbl334->name;
                $vbl2->from_user_name = $vbl333->name;

                $str['data']['task_details']= $vbl2;
                $str['data']['sub_goals']= $vbl3;
                return $str;
            }
        }
        else if($request->task_status != "COMPLETED"
        // || $request->task_status != "R_PENDING"
        || $request->task_status != "ON_GOING"
        || $request->task_status != "REQ_MODIFY")
        {
            $str['status']=false;
            $str['message']="TASK STATUS INCORRECT";
            return $str;
        }
    }

    public function give_feedback(Request $request)
    {
        // return $request;
        // return $vbl4;

        $validator = Validator::make($request->all(),[
            'sub_goal_id'=> 'required|exists:sub_goals,id',
            'feedback'=> 'required|min:5',
        ], [
            'sub_goal_id.required' => 'Sub Goal ID is required.',
            'feedback.required' => 'Feedback is required to be submitted.',
            'feedback.min' => 'Feedback minimum length in 5 characters.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            $vbl = new Feedback;
            $vbl->sub_goal_id = $request->sub_goal_id;
            $vbl->feedback = $request->feedback;
            $vbl->save();

            $vbl4 = SubGoal::find($request->sub_goal_id);
            $vbl5 = Task::find($vbl4->task_id);

            $var4 = new Notification;
            $var4->task_id = $vbl5->id;
            $var4->notification_type = "FEEDBACK";
            $var4->notification_msg = "New Feedback added for the following task.";
            $var4->from_user = $vbl5->to_user;
            $var4->to_user = $vbl5->from_user;
            $var4->status = 0;
            $var4->save();

            $str['status']=true;
            $str['message']="FEED BACK ADDED TO THE SUB GOAL";
            return $str;
        }
    }

    public function get_sub_task_and_feedbacks(Request $request)
    {
        // return $request;
        $vbl2 = SubGoal::where('id',$request->sub_goal_id)->first();
        // return $vbl2;

        if(empty($vbl2))
        {
            $str['status']=false;
            $str['message']="SUB GOAL DOES NOT EXIST";
            return $str;
        }
        else
        {
            $vbl3 = Feedback::where('sub_goal_id',$vbl2->id)->get();
            // return $vbl3;

            $vbl5 = Task::find($vbl2->task_id);
            // return $vbl5;
            // $vbl2->from_user = $vbl5->from_user;
            // $vbl2->to_user = $vbl5->to_user;

            foreach ($vbl3 as $value) {
                // echo $value->created_at;
                $time = date('H:i',strtotime($value->created_at));
                $date = date('d-m-Y',strtotime($value->created_at));
                $value->time =  $time;
                $value->date =  $date;
                // return array($time,$date);
            }

            if(count($vbl3) == 0)
            {
                $str['status']=true;
                $str['message'] = "SUB GOALS WITH FEEDBACKS SHOWN";
                $str['data']['from_user'] = $vbl5->from_user;
                $str['data']['to_user'] = $vbl5->to_user;
                $str['data']['sub_goal_details'] = $vbl2;
                $str['data']['sub_goal_feedbacks_list'] = [];
                return $str;
            }

            $str['status']=true;
            $str['message']="SUB GOALS WITH FEEDBACKS SHOWN";
            $str['data']['from_user'] = $vbl5->from_user;
            $str['data']['to_user'] = $vbl5->to_user;
            $str['data']['sub_goal_details']= $vbl2;
            $str['data']['sub_goal_feedbacks_list']= $vbl3;
            return $str;
        }
    }

    public function update_task(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(),[
            'task_id'=> 'required|exists:tasks,id',
            'goal_title'=> 'required|min:2',
            'goal_description' => 'min:2',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:h:i:s A',
            'picture' => 'required',
            'sub_goals' => 'required',
        ], [
            'task_id.required'=> 'Task ID is required to Modify the Task.',
            'from_user.required' => 'Please enter Logged In User ID.',
            'to_user.required' => 'Please enter Assigned To User ID.',
            'goal_title.required' => 'Please enter Goal Title.',
            'goal_title.min' => 'Goal Title must be at least 2 characters.',
            'goal_description.min' => 'Goal Description must be at least 2 characters.',
            'end_date.required' => 'End Date is required.',
            'end_date.date_format' => 'Date format is Incorrect.',
            'end_time.required' => 'End Time is required.',
            'end_time.date_format' => 'Time format is Incorrect.',
            'picture.required' => 'Task Picture Is Required.',
            'sub_goals.required' => 'There must be at least one Sub Goal.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            if($request->sub_goals[0])
            {
                // echo"hello";
                // echo $request->sub_goals[0];
                foreach ($request->sub_goals as $value) {
                    // echo "hello";
                    if($value['title'] == "")
                    {
                        $str['status']=false;
                        $str['message']="There must be at least one Sub Goal.";
                        return $str;
                    }
                }

                // echo "HELLO";
                $var = Task::find($request->task_id);
                $var->title = $request->goal_title;
                $var->description = $request->goal_description;
                $var->end_date = $request->end_date;
                $var->end_time = $request->end_time;
                $var->picture = $request->picture;
                $var->task_status = "REQ_MODIFY";
                $var->update();


                $vbl6 = SubGoal::where('task_id',$request->task_id)->get();
                foreach ($vbl6 as $value) {
                    Feedback::where('sub_goal_id',$value->id)->delete();
                }
                $vbl6 = SubGoal::where('task_id',$request->task_id)->delete();

                foreach ($request->sub_goals as $value) {
                    $var3 = new SubGoal;
                    $var3->task_id = $var->id;
                    $var3->title = $value['title'];
                    $var3->description = $value['description'];
                    $var3->rating = 0;
                    $var3->save();
                }

                $var4 = new Notification;
                $var4->task_id = $var->id;
                $var4->notification_type = "MODIFY";
                $var4->notification_msg = "Request for change in Behaviour.";
                $var4->from_user = $var->to_user;
                $var4->to_user = $var->from_user;
                $var4->status = 0;
                $var4->save();

                $vbl7 = Notification::where('task_id',$var->id)->where('notification_type',"ADDED")->first();
                $vbl7->notification_type = "ADDED_PENDING";
                $vbl7->update();

                $str['status']=true;
                $str['message']="TASK UPDATED & SENT AGAIN (REQ_MODIFY).";
                return $str;
            }
            else
            {
                $str['status']=false;
                $str['message']="There must be at least one Sub Goal.";
                return $str;
            }
        }
    }

    public function give_rating(Request $request)
    {
        // return "hello 2";
        $validator = Validator::make($request->all(),[
            'sub_goal_id'=> 'required|exists:sub_goals,id',
            'rating'=> 'required',
        ], [
            'sub_goal_id.required' => 'Sub Goal ID is required.',
            'rating.required' => 'Rating is required to be submitted.',
            // 'rating.digits' => 'ONLY 1 TO 5 DIGIT ALLOWED',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            // if($request->rating == 1 || $request->rating == 2 || $request->rating == 3 || $request->rating == 4 ||
            // $request->rating == 5)
            // {
                $vbl = SubGoal::find($request->sub_goal_id);
                $vbl4 = SubGoal::where('task_id',$vbl->task_id)->get();

                $j=0;
                foreach ($vbl4 as $value2) {
                    // echo $value2->rating;
                    if($value2->rating != 0)
                    {
                        $j++;
                    }
                }
                // echo $j;
                if($j == count($vbl4))
                {
                    $str['status']=true;
                    $str['message']="RATING ALREADY SUBMITTED";
                    return $str;
                }

                $vbl->rating = $request->rating;
                $vbl->update();

                $vbl2 = SubGoal::where('task_id',$vbl->task_id)->get();
                $vbl3 = Task::find($vbl->task_id);

                $i = 0;
                foreach ($vbl2 as $value) {
                    // echo $i."\n";
                    // echo $value->rating."\n";
                    if($value->rating == 0)
                    {
                        break;
                    }
                    $i++;

                }

                // return array($i,count($vbl2));

                if(count($vbl2) == $i )
                {
                    $var4 = new Notification;
                    $var4->task_id = $vbl3->id;
                    $var4->notification_type = "RATING";
                    $var4->notification_msg = "Your Rating is given by the User.";
                    $var4->from_user = $vbl3->from_user;
                    $var4->to_user = $vbl3->to_user;
                    $var4->status = 0;
                    $var4->save();

                    $vbl5 = Task::find($vbl3->id);
                    $vbl5->task_status = "COMPLETED";
                    $vbl5->save();

                    Notification::where('task_id',$vbl3->id)->where('notification_type',"ACCEPT")->delete();
                }

                $str['status']=true;
                $str['message']="RATING GIVEN TO THE SUB GOAL";
                return $str;
            // }
            // else
            // {
            //     $str['status']=false;
            //     $str['message']="ONLY 1 TO 5 DIGIT ALLOWED";
            //     return $str;
            // }
        }
    }

    public function get_history(Request $request)
    {
        // return $request;
        $vbl0 = User::where('id',$request->user_id)->first();
        if(empty($vbl0))
        {
            $str['status']=false;
            $str['message']="User does not Exist.";
            return $str;
        }
        // $vbl2 = UserTask::where('to_user',$request->user_id)->get();
        $vbl2 = DB::table('tasks')
        ->orderBy('tasks.id', 'desc')
        ->where('to_user','=',$request->user_id)
        ->where('task_status','=',"COMPLETED")
        ->orWhere('from_user','=',$request->user_id)
        ->where('task_status','=',"COMPLETED")
        ->select('tasks.*')
        ->get();

        $arr = array();
        $i = 0;
        $sum = 0;
        foreach ($vbl2 as $value) {
            $vbl3 = SubGoal::where('task_id',$value->id)->get();
            foreach ($vbl3 as $value2) {
                // return $value->from_user;
                if($value2->rating == 0)
                {
                    $value->given_rating = 0;
                    // $user1 = User::find($value->to_user);
                    // $user2 = User::find($value->from_user);
                    // return $user1;
                    // $value->to_user_name = $user1->name;
                    // $value->from_user_name = $user2->name;
                    array_push($arr,$value);
                    break;
                }
                $i++;
                $sum = $value2->rating + $sum;
                // echo $sum."\n";
                if($i == count($vbl3))
                {
                    // echo $sum."\n";
                    $sum = $sum/count($vbl3);
                    $value->given_rating = number_format($sum,1);

                    // $user1 = User::find($value->to_user);
                    // $user2 = User::find($value->from_user);
                    // $value->to_user_name = $user1->name;
                    // $value->from_user_name = $user2->name;

                    array_push($arr,$value);

                    $i = 0;
                    $sum = 0;
                }
            }
        }
        // return $arr;
        if(count($vbl2) == 0)
        {
            $str['status']=false;
            $str['message']="NO TASKS IN THE HISTORY";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL HISTORY SHOWN";
            $str['data']= $arr;
            return $str;
        }
    }

    public function upload_file(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(),[
            'picture' => 'required|mimes:jpeg,bmp,png,jpg|max:5120',
        ], [
            'picture.mimes' => 'Picture Is Not Valid or Larger than 5 MB.',
            'picture.required' => 'Picture Is required to be Uploaded.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
            $str['message'] =$err['0'];
            return $str;
        }
        else
        {
            $vbl3 = rand(100000000000000,999999999999999);
            $vbl4 = File::extension($request->picture->getClientOriginalName());
            request()->picture->storeAs('public/pictures',$vbl3.".".$vbl4);
            $vbl5 = $vbl3.".".$vbl4;

            $str['status']=true;
            $str['message']="PICTURE SAVED";
            $str['data']= $vbl5;
            return $str;
        }
    }

    public function delete_task(Request $request)
    {
        // return $request;

        $vbl = Task::find($request->task_id);


        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="TASK DOES NOT EXIST";
            return $str;
        }
        else if($vbl->task_status == "REQ_MODIFY" || $vbl->task_status == "PENDING")
        {
            Notification::where('task_id',$vbl->id)->delete();
            SubGoal::where('task_id',$vbl->id)->delete();
            $vbl->delete();
            $str['status']=true;
            $str['message']="TASK DELETED";
            return $str;
        }
        else
        {
            $str['status']=false;
            $str['message']="NOT PENDING OR MODIFICATION REQUEST";
            return $str;
        }
    }

    public function accept_task(Request $request)
    {
        $vbl = Task::find($request->task_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="TASK DOES NOT EXIST";
            return $str;
        }
        else if($vbl->task_status == "REQ_MODIFY")
        {
            $var4 = new Notification;
            $var4->task_id = $vbl->id;
            // $var4->notification_type = "MODIFY_ACCEPT";
            $var4->notification_type = "ACCEPT";
            $var4->notification_msg = "Modify Request Accepted By User.";
            $var4->from_user = $vbl->from_user;
            $var4->to_user = $vbl->to_user;
            $var4->status = 0;
            $var4->save();

            $vbl->task_status = "ON_GOING";
            $vbl->save();

            Notification::where('task_id',$vbl->id)->where('notification_type',"ADDED_PENDING")->delete();
            Notification::where('task_id',$vbl->id)->where('notification_type',"MODIFY")->delete();

            $str['status']=true;
            $str['message']="TASK MODIFY REQUEST ACCEPT ACCEPTED ";
            return $str;
        }
        else if( $vbl->task_status == "PENDING")
        {
            $var4 = new Notification;
            $var4->task_id = $vbl->id;
            $var4->notification_type = "ACCEPT";
            $var4->notification_msg = "Request Accepted By User.";
            $var4->from_user = $vbl->to_user;
            $var4->to_user = $vbl->from_user;
            $var4->status = 0;
            $var4->save();

            $vbl->task_status = "ON_GOING";
            $vbl->save();

            // return "hello";
            Notification::where('task_id',$vbl->id)->where('notification_type',"ADDED")->delete();

            $str['status']=true;
            $str['message']="NEW TASK REQUEST ACCEPTED";
            return $str;
        }
        else
        {
            $str['status']=false;
            $str['message']="NOT PENDING OR MODIFICATION REQUEST";
            return $str;
        }
    }

    public function get_notification(Request $request)
    {
        // return $request;

        // $vbl = Notification::where('to_user',$request->user_id)->orderBy('id','desc')->get();

        $vbl = DB::table('notifications')
        ->orderBy('id','desc')
        ->where('notifications.to_user','=',$request->user_id)
        ->join('users','users.id','=','notifications.to_user')
        ->join('tasks','tasks.id','=','notifications.task_id')
        ->select('tasks.task_status','notifications.*','users.name','users.picture')
        ->get();

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO NOTIFICATIONS YET";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL NOTIFICATIONS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function forget_pass(Request $request)
    {
        // return $request;

        $vbl = User::where('email',$request->email)->first();

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="USER EMAIL NOT REGISTERED";
            return $str;
        }
        else
        {
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 12; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }

            $new_pass = implode($pass);
            // return $new_pass;

            $vbl->password = $new_pass;
            $vbl->update();

            // return $vbl;

            // $details = [
            //     'to_user' => "atillawall@gmail.com",
            //     'body' => "This is the Description of my Subject."
            // ];

            // ForgetPassJob::dispatch(new ForgetPassJob($details))->delay(Carbon::now()->addSeconds(30));
            ForgetPassJob::dispatch($vbl);     //for urgent mail

            $str['status']=true;
            $str['message']="EMAIL SENT";
            return $str;
        }
    }
}
