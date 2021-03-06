<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 找回密码模块控制器代码
 */
class user_get_password_controller
{

    public static function init()
    {
        unset($_SESSION['user_temp']);
        $cache_id = sprintf('%X', crc32($_SERVER['QUERY_STRING']));

        $referer_url = htmlspecialchars_decode($_GET['referer_url']);
        ecjia_front::$controller->assign('referer_url', $referer_url);

        if (!ecjia_front::$controller->is_cached('user_mobile_register.dwt', $cache_id)) {
            ecjia_front::$controller->assign_lang();
            ecjia_front::$controller->assign('title', __('找回密码', 'h5'));
            ecjia_front::$controller->assign_title(__('找回密码', 'h5'));
        }
        return ecjia_front::$controller->display('user_get_password.dwt', $cache_id);
    }

    //手机号码检查
    public static function mobile_check()
    {
        $mobile_phone = trim($_POST['mobile_phone']);
        if (empty($mobile_phone)) {
            return ecjia_front::$controller->showmessage(__('请输入手机号', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }

        $check_mobile = Ecjia\App\Sms\Helper::check_mobile($mobile_phone);
        if (is_ecjia_error($check_mobile)) {
            return ecjia_front::$controller->showmessage($check_mobile->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        $_SESSION['user_temp']['mobile'] = $mobile_phone;

        return ecjia_front::$controller->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('user/get_password/captcha_validate')));
    }

    //图形验证码
    public static function captcha_validate()
    {
        $mobile_phone = $_SESSION['user_temp']['mobile'];

        if (empty($mobile_phone)) {
            return ecjia_front::$controller->redirect(RC_Uri::url('user/get_password/init'));
        }
        $referer_url = htmlspecialchars_decode($_GET['referer_url']);
        ecjia_front::$controller->assign('referer_url', $referer_url);

        $token = ecjia_touch_user::singleton()->getShopToken();
        $res   = ecjia_touch_manager::make()->api(ecjia_touch_api::CAPTCHA_IMAGE)->data(array('token' => $token))->run();
        $res   = !is_ecjia_error($res) ? $res : array();

        ecjia_front::$controller->assign('captcha_image', $res['base64']);

        ecjia_front::$controller->assign('title', __('图形验证码', 'h5'));
        ecjia_front::$controller->assign_title(__('图形验证码', 'h5'));
        ecjia_front::$controller->assign_lang();
        ecjia_front::$controller->assign('url', RC_Uri::url('user/get_password/captcha_check'));
        ecjia_front::$controller->assign('refresh_url', RC_Uri::url('user/privilege/captcha_refresh'));

        return ecjia_front::$controller->display('user_captcha_validate.dwt');
    }

    //检查图形验证码
    public static function captcha_check()
    {
        $token  = ecjia_touch_user::singleton()->getShopToken();
        $mobile = $_SESSION['user_temp']['mobile'];

        $type = trim($_POST['type']);
        if ($type == 'resend') {
            $code_captcha = $_SESSION['user_temp']['captcha_code'];
        } else {
            $code_captcha = trim($_POST['code_captcha']);
        }
        if (empty($code_captcha)) {
            return ecjia_front::$controller->showmessage(__('请输入验证码', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        if (RC_Time::gmtime() < $_SESSION['user_temp']['resend_sms_time'] + 60) {
            return ecjia_front::$controller->showmessage(__('规定时间1分钟以外，可重新发送验证码', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        $param = array('token' => $token, 'type' => 'mobile', 'value' => $mobile, 'captcha_code' => $code_captcha);
        $data  = ecjia_touch_manager::make()->api(ecjia_touch_api::USER_FORGET_PASSWORD)->data($param)->run();
        if (is_ecjia_error($data)) {
            return ecjia_front::$controller->showmessage($data->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        $_SESSION['user_temp']['captcha_code']    = $code_captcha;
        $_SESSION['user_temp']['mobile']          = $mobile;
        $_SESSION['user_temp']['code_status']     = 'succeed';
        $_SESSION['user_temp']['resend_sms_time'] = RC_Time::gmtime();

        $pjaxurl = RC_Uri::url('user/get_password/enter_code');
        $message = __('验证码已发送', 'h5');
        if ($type == 'resend') {
            return ecjia_front::$controller->showmessage(__('发送成功', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
        }

        return ecjia_front::$controller->showmessage($message, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $pjaxurl));
    }

    //输入验证码
    public static function enter_code()
    {
        $mobile = $_SESSION['user_temp']['mobile'];
        if (empty($mobile)) {
            return ecjia_front::$controller->redirect(RC_Uri::url('user/get_password/init'));
        }

        $code_captcha = $_SESSION['user_temp']['captcha_code'];

        ecjia_front::$controller->assign('title', __('输入验证码', 'h5'));
        ecjia_front::$controller->assign_title(__('输入验证码', 'h5'));
        ecjia_front::$controller->assign_lang();

        ecjia_front::$controller->assign('code_captcha', $code_captcha);
        ecjia_front::$controller->assign('mobile', $mobile);

        ecjia_front::$controller->assign('resend_url', RC_Uri::url('user/get_password/captcha_check'));
        ecjia_front::$controller->assign('url', RC_Uri::url('user/get_password/validate_forget_password'));

        return ecjia_front::$controller->display('user_enter_code.dwt');
    }

    //验证短信验证码
    public static function validate_forget_password()
    {
        $token  = ecjia_touch_user::singleton()->getShopToken();
        $mobile = $_SESSION['user_temp']['mobile'];
        $code   = trim($_POST['password']);
        $token  = ecjia_touch_user::singleton()->getShopToken();

        $param = array('token' => $token, 'type' => 'mobile', 'value' => $mobile, 'code' => $code);
        $data  = ecjia_touch_manager::make()->api(ecjia_touch_api::VALIDATE_FORGET_PASSWORD)->data($param)->run();

        if (!is_ecjia_error($data)) {
            $_SESSION['user_temp']['mobile']      = $mobile;
            $_SESSION['user_temp']['code_status'] = 'succeed';
            return ecjia_front::$controller->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => RC_Uri::url('user/get_password/reset_password')));
        } else {
            return ecjia_front::$controller->showmessage($data->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
    }

    //重新设置密码
    public static function reset_password()
    {
        /*验证码相关设置*/
        $passwordf = !empty($_POST['passwordf']) ? trim($_POST['passwordf']) : '';
        $passwords = !empty($_POST['passwords']) ? trim($_POST['passwords']) : '';
        $mobile    = !empty($_SESSION['user_temp']['mobile']) ? trim($_SESSION['user_temp']['mobile']) : '';
        if ($_SESSION['user_temp']['code_status'] != 'succeed') {
            return ecjia_front::$controller->redirect(RC_Uri::url('user/get_password/init'));
        }

        if (isset($_POST['passwordf'])) {
            if (empty($passwordf)) {
                return ecjia_front::$controller->showmessage(__('请输入新密码', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
            }
            if (empty($passwords)) {
                return ecjia_front::$controller->showmessage(__('请输入确认密码', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
            }
            if ($passwordf != $passwords) {
                return ecjia_front::$controller->showmessage(__('两次密码输入不一致', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
            }

            $token = ecjia_touch_user::singleton()->getShopToken();
            $data  = ecjia_touch_manager::make()->api(ecjia_touch_api::USER_RESET_PASSWORD)->data(array('token' => $token, 'type' => 'mobile', 'value' => $mobile, 'password' => $passwordf))->run();
            if (!is_ecjia_error($data)) {
                unset($_SESSION['user_temp']['mobile']);
                unset($_SESSION['user_temp']['code_status']);
                return ecjia_front::$controller->showmessage(__('您已成功找回密码', 'h5'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('user/privilege/pass_login')));
            } else {
                return ecjia_front::$controller->showmessage($data->get_error_message(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
            }
        }

        $cache_id = sprintf('%X', crc32($_SERVER['QUERY_STRING']));
        if (!ecjia_front::$controller->is_cached('user_reset_password.dwt', $cache_id)) {
            ecjia_front::$controller->assign_lang();
            ecjia_front::$controller->assign('title', __('设置新密码', 'h5'));
            ecjia_front::$controller->assign_title(__('设置新密码', 'h5'));
        }
        return ecjia_front::$controller->display('user_reset_password.dwt', $cache_id);
    }
}

// end
