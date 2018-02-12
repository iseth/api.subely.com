<?php

namespace App\Http\Controllers;

use App\Subs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

use Spatie\FlysystemDropbox\DropboxAdapter;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;

class WebhookController extends Controller{

	public function __construct(){

		// $this->middleware('oauth', ['except' => ['verify']]);
		// $this->middleware('authorize:' . __CLASS__, ['except' => ['verify']]);
	}

	public function index($uid){

		$subs = Subs::where('owner', '=', $uid)->get();

		if(!$subs){
			return $this->error("The user has no subs", 404);
		}
		return $this->success($subs, 200);
	}

	public function webhookverify(Request $request)
	{

		return ($request->challenge);
	}
	public function webhook(Request $request)
	{
		$data = file_get_contents('php://input');
		$accounts = json_decode($data)->list_folder->accounts;
		foreach ($accounts as $account) {
			var_dump(file_put_contents("./test.log", $account));
		}
		return ($request);
	}
	public function list()
	{
		$subs = Subs::all();
		return $this->success($subs, 200);
	}

}
