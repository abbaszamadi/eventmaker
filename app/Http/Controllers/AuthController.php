<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\User;
use App\Verify_code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $userModel       = new User();
        $verifyCodeModel = new Verify_code();
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|max:255',
            'phone' => 'required|unique:users|max:11',
            'name'  => 'max:255'
        ]);
        if($validator->fails()){
            $response = array(
                'resultCode'    => 405,
                'message'       => $validator->errors()
            );
            return $this->sendResponse( $response );
        }
        $email      = $request->get('email');
        $phone      = $request->get('phone');
        $token      = Str::random(60);
        $userData   = array(
            'phone' => $phone,
            'email' => $email,
            'name'  => '',
            'api_token' => $token,
            'createdAt' => time()
        );
        if ($userId = $userModel->save($userData))
        {
            $verifyCode = rand(112120,999999);
            $codeData   = array(
                'verifyCode'    => $verifyCode,
                'userId'        => $userId,
                'createdAt'     => time()
            );
            if ($verifyCodeModel->save($codeData))
            {
                $emailData = array('to' => $email, 'verifyCode' => $verifyCode);
                if ($this->sendVerificationEmail($emailData))
                {
                    $response = array(
                        'resultCode'    => 200,
                        'data'          => array('token' => $token),
                        'message'       => 'ثبت نام با موفقیت انجام شد! یک ایمیل حاوی کد اعتبار سنجی به ایمیل شما ارسال شد'
                    );
                }else{
                    $response = array(
                        'resultCode'    => 400,
                        'message'       => 'خطا در ارسال ایمیل تایید! دوباره تلاش کنید'
                    );
                }
            }else{
                $response = array(
                    'resultCode'    => 400,
                    'message'       => 'خطا در ارسال ایمیل تایید! دوباره تلاش کنید'
                );
            }
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'خطا در ثبت اطلاعات کاربری! دوباره تلاش کنید'
            );
        }
        return $this->sendResponse($response);
    }

    public function sendVerificationEmail($emailData)
    {
        $to         = isset($emailData['to']) ? $emailData['to'] : null;
        $verifyCode = $emailData['verifyCode']? $emailData['verifyCode'] : null;
        if ( ! $to or ! $verifyCode )
        {
            return false;
        }
        $data = array(
            'subject'           => 'verify',
            'from'              => 'verify@eventmaker.com',
            'verificationCode'  => $verifyCode,
            'viewName'          => 'verification_mail'
        );
        Mail::to($to)->send(new SendMail($data));
        return true;
    }
}
