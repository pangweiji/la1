<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Message;

class TestController extends Controller
{
    //
    public function test()
    {
        $messages = Message::find(1)->message_id;

        return view('home.test.index');
    }

    public function show()
    {
        dd('aaa');
        $filePath = 'C:\Users\sw9999\Desktop\test\1111.jpg';
        $headers = array();
        return response()->file($filePath, $headers);
    }
}
