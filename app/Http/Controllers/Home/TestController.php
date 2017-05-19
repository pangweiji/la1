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
        dd($messages);
        return view('home.test.index');
    }
}
