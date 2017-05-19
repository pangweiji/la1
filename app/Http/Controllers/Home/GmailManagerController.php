<?php

namespace App\Http\Controllers\Home;
require_once app_path('Libraries/').'GmailAPI.class.php';

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Google_Client;
use Google_Service_Drive;

class GmailManagerController extends Controller
{
    private static $mail = null;


    public function list(Request $request)
    {
        if ($request->has('submit')) {
            $this->validate($request, [
                'platform' => 'required',
                'account' => 'required',
                'email' => 'email'
            ]);
            $email = $request->email;
            $account = $request->account;
            $platform = $request->platform;

            $result = DB::table('email_info')
                ->insert(
                    ['account' => $account, 'platform' => $platform, 'email_type' => 'gmail', 'email' => $email]
                );
            if (!$result) {
                return response()->json([
                    'code' => 1002,
                    'msg' => '添加失败!'
                ]);
            }
            return response()->json([
                'code' => 1001,
                'msg' => '添加成功!'
            ]);
        }

        $lists = DB::table('email_info')
            ->where('email_type', 'gmail')
            ->where('is_delete', 0)
            ->get();

        return view('home.gmail.list',['lists' => $lists]);
    }

    public function auth(Request $request)
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
/*        $googleCli = new Google_Client();
        $googleCli->setAuthConfig(storage_path('keys/').$platform.'.json');
        $googleCli->setAccessType("offline");        // offline access
        $googleCli->setIncludeGrantedScopes(true);   // incremental auth
        $googleCli->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $auth_url = $googleCli->createAuthUrl();*/
        self::$mail = new \GmailAPI($platform);
        $auth_url = self::$mail->getAuthUrl();

        $request->session()->put('platform', $platform);
        $request->session()->put('gmail', $gmail);
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public function getCode(Request $request)
    {
        $authCode = $request->get('code', '');

        if (empty($authCode)) {
            echo '授权失败';die;
        }

        $platform = $request->session()->get('platform');
        $gmail = $request->session()->get('gmail');

        //获取token
        self::$mail = new \GmailAPI($platform);
        $accessToken = self::$mail->getAuthToken($authCode);

        //入库
        $result1 = DB::table('msg_gmailtoken')
            ->insert([
                'platform' => $platform,
                'shop' => '123',
                'gmail' => $gmail,
                'access_token' => $accessToken['access_token'],
                'token_type' => $accessToken['token_type'],
                'expires_in' => $accessToken['expires_in'],
                'refresh_token' => $accessToken['refresh_token'],
                'created' => $accessToken['created'],
                'add_time' => time()
            ]);
        
        if ($result1) {
            echo "<script>";
            echo "window.opener.location.reload();";
            echo "window.close();";
            echo "</script>";
        }
    }
}
