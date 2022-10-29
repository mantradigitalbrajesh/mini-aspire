<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function loginCustomer(Request $request)
    {
        try
        {
            $data = $request->all();
            $email = (isset($data['email']) && !empty($data['email'])) ? $data['email'] : "";
            $password = (isset($data['password']) && !empty($data['password'])) ? $data['password'] : "";
            if(!isset($data['email']) || empty($data['email']))
            {
                return response()->json(['error' => 'Email is Required Field'], 400);
            }
            if(isset($email) && !empty($email))
            {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                {
                    return response()->json(['error' => 'Email Id is Invalid'], 400);
                }
            }
            if(!isset($data['password']) || empty($data['password']))
            {
                return response()->json(['error' => 'Password is Required Field'], 400);
            }
            $get_user = User::getUserDataEmail($email);
            if(isset($get_user) && !empty($get_user))
            {
                if(Hash::check($password, $get_user->password))
                {
                    return response()->json(['Authorisation Token' => $get_user->remember_token],200);
                }
                else
                {
                    return response()->json(['error' => 'Entered Password is Invalid'], 403);
                }
            }
            else
            {
                return response()->json(['error' => 'User Not Found ! Please Check Your Email Id'], 404);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
