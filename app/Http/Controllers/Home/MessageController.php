<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function list()
    {
        $list = DB::table('message')
            ->where('is_delete',0)
            ->orderBy('receivetimestamp', 'ASC')
            ->paginate(10);

        return view('home.message.list', ['list' => $list]);
    }

    public function detail($id)
    {
        $id = explode(',', $id);
        $msg = DB::table('message')
            ->whereIn('id',$id)
            ->where('is_delete',0)
            ->get();

        return view('home.message.detail',['msg'=>$msg]);
    }

    /**
     *  帐号列表
     */
    public function accountList()
    {
        $list = DB::table('email_info')
            ->where('is_delete', 0)
            ->paginate(10);
        return view('home.message.accountList', ['list' => $list]);
    }

    /**
     *  ajax添加帐号
     */
    public function addAccountAjax(Request $request)
    {
        return response()->json([
            'code' => 1002,
            'msg' => '帐号已存在！'
        ]);
        die;
        $account = $request->input('account','');
        $email = $request->input('email','');
        $password = $request->input('password','');

        //查找帐号是否存在
        $checkResult = DB::table('email_info')
            ->where('account', $account)
            ->where('is_delete', 0)
            ->get();

        if (!empty($checkResult)) {
            return response()->json([
                'code' => '1002',
                'msg' => '帐号已存在！'
            ]);
        }

        //插入
        $result = DB::table('email_info')
            ->insert(
                ['account' => $account, 'email' => $email, 'password' => $password]
            );
        if (!$result) {
            return resopnse()->json([
                'code' => 1003,
                'msg' => '帐号添加失败！'
            ]);
        }
    }

    public function deleteAccountAjax($id)
    {
        $id = isset($id) ? $id : '';
        if (!$id) {
            return resopnse()->json([
                'code' => 2002,
                'msg' => '参数错误！'
            ]);
        }
        $result = DB::table('email_info')
            ->where('id', $id)
            ->update(
                ['is_delete' => 1]
            );
        if (!$result) {
            return resopnse()->json([
                'code' => 2003,
                'msg' => '删除失败！'
            ]);
        }
        return resopnse()->json([
                'code' => 2001,
                'msg' => '添加成功！'
        ]);
    }

    /**
     *  回复
     */
    public function reply(Request $request)
    {
        $msgid = $request->input('msgid','');
        $replycontent = $request->input('replycontent','');

        //保存为html
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= $replycontent;
        $html .= '<includetail><!--<![endif]--></includetail></div>';
        $reply_html_path = '/reply_html/'.$msgid.'-'.date('Y-m-d').'.html';
        file_put_contents(storage_path() . $reply_html_path, $html);

        //获取邮件信息
        $msgInfo = DB::table('message')
            ->where('id', $msgid)
            ->first();

        //更新message记录，发送队列
        $updateDate = array();
        $status = 1;
        $result1 = DB::table('message')
            ->where('id', $msgid)
            ->update(
                [
                    'status' => 1,
                    'replycontent' => $replycontent,
                    'replyuser_id' => 1,
                    'replytime' => time(),
                    'replyhtml' => $reply_html_path
                ]
            );

        if ($result1) {
            $conn = new AMQPConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_LOGIN'), env('RABBITMQ_PASSWORD'), env('RABBITMQ_MESSAGE_VHOST'));
            $channel = $conn->channel();
            //交换机
            $channel -> exchange_declare('message_exchenge', 'fanout', false, true, false);
            //声明queue
            $channel -> queue_declare('message_queue', false, true, false, false);
            $channel->queue_bind('message_queue', 'message_exchenge');

            $data = json_encode(array(
                'mid'       => $msgid,
                'msgbody'   => $replycontent,
                'subject'   => $msgInfo->subject,
                'sendid'    => $msgInfo->sendid,
                'receiveid' => $msgInfo->receiveid,
                'msg_uid'   => $msgInfo->message_id,
                'replyhtml' => $reply_html_path
            ));

            //消息持久化
            $msg = new AMQPMessage($data, array('delivery_mode' => 2));
            $channel->basic_publish($msg, 'message_exchenge');

            $logPath = storage_path() . '/logs/message/reply_queue.log';
            $log = date('Y-m-d', time()).'  发布消息：'.$msgid.' to '. $msgInfo->receiveid . "\r\n";
            file_put_contents($logPath, $log, FILE_APPEND);

            $channel -> close();
            $conn -> close();
            return resopnse()->json([
                'code' => 3001,
                'msg' => '发送成功！'
            ]);
        } else {
            return resopnse()->json([
                'code' => 3002,
                'msg' => '发送失败！'
            ]);
        }
    }
}
