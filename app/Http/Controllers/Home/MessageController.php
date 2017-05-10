<?php

namespace App\Http\Controllers\Home;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Google_Client;
use Google_Service_Drive;

class MessageController extends Controller
{
    public function getIndex()
    {
        echo 'index';
    }

    /**
     *  发送邮件
     */
    public function getSendmessage(Request $request)
    {
        //outlook邮箱发送
        // dd($request->input());

        return view('home.message.send');
    }

    /**
     *  帐户管理
     */
    public function getAccountmanagment()
    {
        return view('home.message.accountmanager');
    }

    /**
     *  授权
     */
    public function getAuth(Request $request)
    {
        $gmail = $request->get('gmail', '');
        $platform = $request->get('platform', '');
        $request->session()->put('platform', $platform);

        if (!$gmail) {
            echo "<script>";
            echo "setTimeout('window.opener.location.reload()', 2000);";
            echo "window.opener.toastr.warning('Gmail参数错误！');";
            echo "</script>";
            echo '参数错误，请关闭窗口！';
        }

        //通过gmail 获取平台，保证平台一致
        $googleCli = new Google_Client();
        $googleCli->setAuthConfig(storage_path().'/keys/'.$platform.'.json');
        $googleCli->setAccessType("offline");        // offline access
        $googleCli->setIncludeGrantedScopes(true);   // incremental auth
        $googleCli->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $auth_url = $googleCli->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    /**
     *  获取token
     */
    public function getGetcode(Request $request)
    {
        $authCode = $request->get('code', '');

        if (empty($authCode)) {
            echo '授权失败';die;
        }

        //获取token
        $googleCli = new Google_Client();
        $googleCli->setAuthConfig(storage_path().'/keys/'.$request->session()->get('platform').'.json');
        $accessToken = $googleCli->fetchAccessTokenWithAuthCode($authCode);

        //入库

        dump($accessToken);

    }

    /**
     *  添加帐号
     */
    public function ajaxAddAccount(Request $request)
    {
        $this->validate($request, [
            'platform' => 'required',
            'account'  => 'required',
            'email'    => 'required'
        ]);

        //插入数据

        dd(storage_path());     
        return response()->json();
    }

}