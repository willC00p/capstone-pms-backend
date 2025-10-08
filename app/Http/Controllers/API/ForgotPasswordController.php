<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetCodeMail;
use App\Http\Controllers\API\BaseController as BaseController;

class ForgotPasswordController extends BaseController
{
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendError('No user found with that email', [], 404);
        }

        $token = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new ResetCodeMail($token));
        } catch (\Exception $e) {
            return $this->sendError('Failed to send email: ' . $e->getMessage(), [], 500);
        }

        return $this->sendResponse(['message' => 'Code sent successfully'], 'Code sent successfully');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return $this->sendError('Invalid code or email', [], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (Hash::check($request->password, $user->password)) {
            return $this->sendError('New password cannot be the same as your current password', [], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return $this->sendResponse(['message' => 'Password reset successful'], 'Password reset successful');
    }
}
