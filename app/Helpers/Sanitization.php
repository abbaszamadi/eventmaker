<?php
namespace App\Helpers;
class Sanitization
{


    public static function isValidPhone($phone)
    {
        $phone_reg = '/^(\+98|0)?9\d{9}$/i';

        if(preg_match($phone_reg, $phone)){
            return true;
        }else{
            return false;
        }
    }

    public static function sanitizePhone($phone)
    {
        $persian_num = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
        for($i=0;$i<10;$i++)
        {
            $str = str_replace($persian_num[$i], $i, $phone);
        }
        if ( substr($phone, 0, 1) != '0' ){
            $phone = '0' . $phone;
        }
        return $phone;
    }
}
