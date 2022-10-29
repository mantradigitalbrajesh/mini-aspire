<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserController extends Controller
{
    public function registerCustomer(Request $request)
    {
        try
        {
            $data = $request->all();
            $user_data = [];
            $user_role_data = [];
            $name =  (isset($data['name']) && !empty($data['name'])) ? $data['name'] : "";
            $email = (isset($data['email']) && !empty($data['email'])) ? $data['email'] : "";
            $password = (isset($data['password']) && !empty($data['password'])) ? Hash::make($data['password']) : "";

            if(isset($email) && !empty($email))
            {
                $check_if_email_exists = User::getUserDataEmail($email);
                if(isset($check_if_email_exists) && !empty($check_if_email_exists))
                {
                    return response()->json(['error' => 'Email Id already in Use'], 403);
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                {
                    return response()->json(['error' => 'Email Id is Invalid'], 400);
                }
                
            }
            if(!isset($data['name']) || empty($data['name']))
            {
                return response()->json(['error' => 'Name is Required Field'], 400);
            }
            if(!isset($data['email']) || empty($data['email']))
            {
                return response()->json(['error' => 'Email is Required Field'], 400);
            }
            
            if(!isset($data['password']) || empty($data['password']))
            {
                return response()->json(['error' => 'Password is Required Field'], 400);
            }

            if((isset($name) && !empty($name)) && (isset($email) && !empty($email)) && (isset($password) && !empty($password)))
            {
                $remember_token = Str::random(60);
                $user_data = array(
                    'name'=>$name,
                    'email'=>$email,
                    'password'=>$password,
                    'remember_token'=>$remember_token,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                );
                $get_user_id = User::registerUser($user_data);
                if(!empty($get_user_id))
                {
                    $user_role_data = array('user_id'=>$get_user_id,'role_id'=>2);
                    $insert_user_role_data = User::assignRole($user_role_data);
                    if(!empty($insert_user_role_data))
                    {
                        $get_user_data = User::getUserData($get_user_id);
                        if(!empty($get_user_data))
                        {
                            return response()->json(['name' => $get_user_data->name,'email'=>$get_user_data->email,'Authorisation Token'=>$get_user_data->remember_token], 200);
                        }
                    }
                }
            }

        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
