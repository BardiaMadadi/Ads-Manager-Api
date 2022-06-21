<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function get (Request $request){

        $validated = Validator::make(
            $request->route()->parameters(),
            [
                "id" => "required|exists:users"
            ]
        );

        if(!$validated->fails()){
            $id = $validated->validated()["id"];
            $user = User::find($id);
            return $user;
        }else{
            return response(["message"=>"cant validate your request", "error"=>[$validated->errors()]],400);
        }

    }

}
