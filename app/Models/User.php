<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Permissions\HasPermissionsTrait;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function registerUser($user_data)
    {
       $result  = User::insertGetId($user_data);
       return $result;
    }

    protected function assignRole($insert_user_role_data)
    {
        $result = DB::table("users_roles")->insert($insert_user_role_data);
        return $result;
    }

    protected function getUserData($get_user_id)
    {
        $result = User::where('id','=',$get_user_id)->first();
        return $result;
    }

    protected function getUserDataEmail($email)
    {
        $result = User::where('email','=',$email)->first();
        return $result;
    }

    //Check if Customer Exists based on Customer Role
    protected function checkUserExists($get_token)
    {
        $result = User::select("users.id")->join('users_roles','users_roles.user_id','=','users.id')
        ->where('remember_token','=',$get_token)
        ->where('users_roles.role_id','=',2)
        ->first();
        return $result;
    }

    //Check if Admin Exists based on Admin Role
    protected function checkAdminExists($get_token)
    {
        $result = User::select("users.id")->join('users_roles','users_roles.user_id','=','users.id')
        ->where('remember_token','=',$get_token)
        ->where('users_roles.role_id','=',1)
        ->first();
        return $result;
    }
}
