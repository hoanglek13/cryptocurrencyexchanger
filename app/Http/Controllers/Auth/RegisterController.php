<?php

namespace App\Http\Controllers\Auth;

use App\Currency;
use App\EmailService;
use App\HelperService;
use App\User;
use App\Http\Controllers\Controller;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            //'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            //'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data, $password , $ip)
    {
        return User::create([
            'nickname' => $data['email'],
            'email' => $data['email'],
            'password' => bcrypt($password),
            'registered_ip' => $ip
        ]);
    }

    //Handles registration request for seller

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function register(Request $request)
    {
        //dd( $request->all() );
        //Validates data
        $this->validator($request->all())->validate();

        //Create seller
        $password = str_random(5);
        $user = $this->create($request->all(), $password, $request->ip());

        HelperService::createWallet($user);

        //Authenticates seller
        $this->guard()->login($user);

        // send to user
        $emailService = new EmailService;
        $emailService->registeredEmail($user, $password, $request->ip());

        //Redirects sellers
        return redirect($this->redirectTo);
    }
}
