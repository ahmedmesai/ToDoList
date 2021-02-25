<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends BaseController
{
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $email = $request->email;

        if (User::where('email', $email)->doesntExist()) {
            return $this->sendError('User does not exists');
        }

        $token = Str::random(6);

        try {
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token
            ]);

            Mail::send('Mails.forgot', ['token' => $token], function (Message $message)  use ($email) {
                $message->from('ahmed@messaibelgacem.com', 'Ahmed Messai Belgacem');
                $message->to($email);
                $message->subject('Reset Your Password');
            });

            return response([
                'message' => 'Please Check Your Email'
            ]);
        } catch (\Exception $exception) {
            return $this->sendError('error', $exception->getMessage(), 400);
        }
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $token = $request->token;
        $passwordResets = DB::table('password_resets')->where('token', $token)->first();

        if (!$passwordResets) {
            return $this->sendError('Invalid Token!', [], 400);
        }

        $user = User::where('email', $passwordResets->email)->first();

        if (!$user) {
            return $this->sendError("User Does Not Exist");
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->sendResponse([], "Change Password Successfully");
    }
}
