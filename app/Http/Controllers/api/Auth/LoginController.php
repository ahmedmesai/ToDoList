<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\api\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('todolist')->accessToken;
            $success['name'] = $user->name;
            return $this->sendResponse($success, 'User Logined Successfully!');
        } else {
            return $this->sendError('Please Verify Your Email Or Password');
        }
    }
}
