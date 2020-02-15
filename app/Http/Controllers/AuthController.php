<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\User;
use App\Verify_code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $verifyCodeExpiration = 100; // 100 is the time(seconds) that verify code expire.

    public function register(Request $request)
    {
        $userModel       = new User();
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|max:255',
            'phone' => 'required|unique:users|max:11',
            'name'  => 'max:255',
            'password' => 'required'
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
        $name       = $request->get('name');
        $token      = Str::random(60);
        $userData   = array(
            'phone' => $phone,
            'email' => $email,
            'name'  => $name,
            'password'  => Hash::make('password', ['rounds' => 12]),
            'api_token' => $token,
            'createdAt' => time()
        );
        if ($userId = $userModel->save($userData))
        {
            $emailData = array(
                'to'        => $email,
                'userId'    => $userId
            );
            $this->saveAndSendVerificationEmail($emailData);
            $response = array(
                'resultCode'    => 200,
                'message'       => 'ثبت نام با موفقیت انجام شد! یک ایمیل حاوی کد اعتبار سنجی به ایمیل شما ارسال شد'
            );
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'خطا در ثبت اطلاعات کاربری! دوباره تلاش کنید'
            );
        }

        return $this->sendResponse($response);
    }

    public function verify(Request $request)
    {
        $userModel          = new User();
        $verifyCodeModel    = new Verify_code();
        $email  = $request->get('email');
        $user   = $userModel->get(array('filters' => array('email' => $email)));
        if (isset($user))
        {
            if ($user->statusCode == 0)
            {
                $validator          = Validator::make($request->all(), [
                    'verifyCode' => 'required|max:6',
                ]);
                if($validator->fails()){
                    $response = array(
                        'resultCode' => 400,
                        'message'    => $validator->errors()
                    );
                    return $this->sendResponse($response);
                }
                $verifyCode = $request->get('verifyCode');
                $filters    = array('verifyCode' => $verifyCode, 'userId' => $user->id);
                $code       = $verifyCodeModel->get(array('filters' => $filters));
                if ($code)
                {
                    if ($code->verifyCode == $verifyCode )
                    {
                        if ( time() - $code->createdAt < 100)
                        {
                            $filters    = array('id' => $code->userId);
                            $data       = array('statusCode' => 1);
                            if ($userModel->update(array('filters' => $filters, 'data' => $data)))
                            {
                                $response = array('message' => 'حساب کاربری با موفقیت تایید شد');
                            }else{
                                $response = array(
                                    'resultCode'    => 400,
                                    'message'       => 'خطا ! دوباره تلاش کنید'
                                );
                            }
                        }else{
                            $response = array(
                                'resultCode' => 400,
                                'message'    => 'کد اعتبار سنجی منقضی شده است'
                            );
                        }

                    }else{
                        $response = array(
                            'resultCode' => 400,
                            'message'    => 'کد اعتبار سنجی صحیح نمی باشد'
                        );
                    }
                }else{
                    $response = array(
                        'resultCode' => 400,
                        'message'    => 'کد اعتبار سنجی نامعتبر است'
                    );
                }
            }else{
                $response = array(
                    'resultCode' => 400,
                    'message'    => 'حساب کاربری از قبل تایید شده است'
                );
            }
        }else{
            $response = array(
                'resultCode' => 400,
                'message'    => 'اطلاعات ارسالی نامعتبر است'
            );
        }
        return $this->sendResponse($response);

    }

    public function login(Request $request)
    {
        $userModel       = new User();
        $validator = Validator::make($request->all(), [
            'email' => 'max:255',
            'phone' => 'max:11',
            'password' => 'required'
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
        $password   = $request->get('password');
        if (isset($email))
        {
            $filters = array('email' => $email);
        }elseif (isset($phone))
        {
            $filters = array('phone' => $phone);
        }else{
            return $this->sendResponse(array(
                'resultCode'    => 400,
                'message'       => 'اطلاعات ارسالی نامعتبر است'
            ));
        }
        $user = $userModel->get(array('filters' => $filters));
        if ( $user )
        {
            if ($user->statusCode == 1)
            {
                if ( password_verify($password, $user->password) )
                {
                    $response = array(
                        'resultCode'    => 200,
                        'message'       => 'شما با موفقیت وارد شدید',
                        'data'          => array('token' => $user->api_token)
                    );
                }else{
                    $response = array(
                        'resultCode'    => 400,
                        'message'       => 'رمز عبور نادرست است'
                    );
                }
            }else{
                $response = array(
                    'resultCode' => 400,
                    'message'    => 'ابتدا حساب کاربری خود را فعال کنید'
                );
            }
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'اطلاعات وارد شده صحیح نیست'
            );
        }
        return $this->sendResponse($response);

    }

    public function resend_verify_code(Request $request)
    {
        $userModel          = new User();
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:255',
        ]);
        if($validator->fails()){
            $response = array(
                'resultCode'    => 405,
                'message'       => $validator->errors()
            );
            return $this->sendResponse( $response );
        }
        $email  = $request->get('email');
        $user   = $userModel->get(array('filters' => array('email' => $email)));
        if ($user)
        {
            if ($user->statusCode == 0)
            {
                $emailData = array(
                    'to'    => $user->email,
                    'userId' => $user->id
                );
                $this->saveAndSendVerificationEmail($emailData);
                $response = array(
                    'resultCode'    => 200,
                    'message'       => 'کد اعتبار سنجی با موفقیت ارسال شد'
                );
                return $this->sendResponse($response);
            }else{
                $response = array(
                    'resultCode'    => 400,
                    'message'       => 'امکان ارسال کد اعتبار سنجی وجود ندارد'
                );
            }
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'اطلاعات ارسالی نامعتبر است'
            );
        }

        return $this->sendResponse($response);
    }

    private function saveAndSendVerificationEmail($emailData)
    {
        $verifyCodeModel = new Verify_code();
        $userId     = isset($emailData['userId']) ? $emailData['userId'] : null;
        $to         = isset($emailData['to']) ? $emailData['to'] : null;
        $verifyCode = rand(112120,999999);
        if ( ! $to or ! $verifyCode or ! $userId )
        {
            return false;
        }
        $result = $this->isAuthorizedToResendCode($userId);
        if ($result['result'])
        {
            $codeData = array(
                'userId'        => $userId,
                'verifyCode'    => $verifyCode,
                'createdAt'          => time()
            );
            if ($verifyCodeModel->save($codeData))
            {
                $data = array(
                    'subject'           => 'verify',
                    'from'              => 'verify@eventmaker.com',
                    'verificationCode'  => $verifyCode,
                    'viewName'          => 'verification_mail'
                );
                Mail::to($to)->send(new SendMail($data));
                return true;
            }else{
                return $this->sendResponse(array(
                    'resultCode'    => 400,
                    'message'       => 'خطا در ارسال کد اعتبار سنجی!'
                ));
            }
        }else{
            return $this->sendResponse(array(
                'resultCode'    => 400,
                'message'       => 'لطفا بعد از ' . $result['data']['remainingTime'] . ' ثانیه دوباره امتحان کنید'
            ));
        }

    }
    private function isAuthorizedToResendCode($userId)
    {
        $verifyCodeModel    = new Verify_code();
        $filters            = array(
            'userId'        => $userId,
           // 'createdAt'     => array('>=', time() - 100)
        );
        $filters[] = array( 'createdAt', '>',  time() - 100 );
        $verifyCode = $verifyCodeModel->get(array('filters' => $filters));

        if ( $verifyCode )
        {
            return array(
                'result'    => false,
                'data'      => array('remainingTime' => 100 - (time() - $verifyCode->createdAt) )
            );
        }else{
            return array(
                'result'    => true
            );
        }
    }

}
