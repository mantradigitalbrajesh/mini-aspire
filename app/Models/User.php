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
}
