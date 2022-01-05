<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
            'first_name'=> 'required|min:3',
            'last_name'=> 'required|min:3',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'username' => 'required|min:3|unique:users,username',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            'picture' => 'mimes:jpeg,bmp,png,jpg|max:5120',
        ], [
            'first_name.required' => 'Please enter your First Name.',
            'first_name.min' => 'First name must be at least 3 characters.',
            'last_name.required' => 'Please enter your Last Name.',
            'last_name.min' => 'Last name must be at least 3 characters.',
            'email.required' => 'Please enter your Email.',
            'email.unique' => 'Email is already registered.',
            'email.email' => 'Email is invalid.',
            'username.required' => 'Please enter your Username',
            'username.min' => 'Username must be at least 3 characters.',
            'username.unique' => 'Username must be unique',
            'pasword.required' => 'Please enter your password.',
            'pasword.min' => 'Password Not Less Than 6 digits.',
            'c_pasword.required' => 'Please confirm your password.',
            'c_pasword.min' => 'Password Not Less Than 6 digits.',
            'picture.mimes' => 'Picture Is Not Valid.',
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
            $var->first_name = $request->first_name;
            $var->last_name = $request->last_name;
            $var->email = $request->email;
            $var->username = $request->username;
            $var->password = $request->password;

            if($request->picture)
            {
                $vbl3 = rand(100000000000000,999999999999999);
                $vbl4 = File::extension($request->picture->getClientOriginalName());
                request()->picture->storeAs('public/profile_pictures',$vbl3.".".$vbl4);
                $var->picture = $vbl3.".".$vbl4;
                $var->save();
            }
            else
            {
            $var->stu_pic = "default-user.jpg";
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

        $eml = $request->email;
        $pwd = $request->password;
        $dbpwd = "";
        $verification = User::where('email',$eml) -> first();
        // echo $verification;

        if($verification)
        {
            if($pwd == $verification->password)                  //main directory is here
            {
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
            $vbl->tokens()->delete();
            $str['status']=true;
            $str['message']="USER LOG OUT SUCCESSFULL";
            return $str;
        }
    }
}
