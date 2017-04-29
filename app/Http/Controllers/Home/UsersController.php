<?php

namespace App\Http\Controllers\Home;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use DB;

class UsersController extends Controller
{
	public function getUsers()
	{
		//$usersAll = DB::table('Users')->get();
		

		dump(base_path());

	}
}