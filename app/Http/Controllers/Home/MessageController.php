<?php

namespace App\Http\Controllers\Home;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
	public function index()
	{
		echo 'index';
	}

	public function getMessage()
	{
		echo 'getMessage';
	}
}