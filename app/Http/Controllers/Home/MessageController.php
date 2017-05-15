<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

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

        return view('home.message.detail');
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
        $account = $request->input('account','');
        $email = $request->input('email','');
        $password = $request->input('password','');

        //查找帐号是否存在
        $checkResult = DB::table('email_info')
            ->where('account', $account)
            ->where('is_delete', 0)
            ->get();

        if (!empty($checkResult)) {
            $response = array('code' => 1002, 'msg' => '帐号已存在！');
            return json_encode($response);
        }

        //插入
        $result = DB::table('email_info')
            ->insert(
                ['account' => $account, 'email' => $email, 'password' => $password]
            );
        if (!$result) {
            $response = array('code' => 1003, 'msg' => '帐号添加失败！');
            return json_encode($response);
        }
    }

    public function deleteAccountAjax($id)
    {
        $id = isset($id) ? $id : '';
        if (!$id) {
            $response = array('code' => 2002, 'msg' => '参数错误！');
            return json_encode($response); 
        }
        $result = DB::table('email_info')
            ->where('id', $id)
            ->update(
                ['is_delete' => 1]
            );
        if (!$result) {
            $response = array('code' => 2003, 'msg' => '删除失败！');
            return json_encode($response); 
        }
        $response = array('code' => 2001, 'msg' => '');
        return json_encode($response);
    }
}
