<?php

namespace App\Http\Controllers\Home;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Google_Client;

class MessageController extends Controller
{
    public function getIndex()
    {
        echo 'index';
    }

    /**
     *  发送邮件
     */
    public function getSendmessage()
    {
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

        if (!$gmail) {
            echo "<script>";
            echo "setTimeout('window.opener.location.reload()', 2000);";
            echo "window.opener.toastr.warning('Gmail参数错误！');";
            echo "</script>";
            echo '参数错误，请关闭窗口！';
        }
        
        //通过gmail 获取平台，保证平台一致
        $googleCli = new Google_Client();
        $googleCli->setAuthConfig('client_secrets.json');
        $googleCli->setAccessType("offline");        // offline access
        $googleCli->setIncludeGrantedScopes(true);   // incremental auth
        $googleCli->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $googleCli->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
        header('Location:', $googleCli->createAuthUrl());
    }

}