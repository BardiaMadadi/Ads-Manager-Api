<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // get user by id
    public function get(Request $request)
    {

        $validated = Validator::make(
            $request->route()->parameters(),
            [
                "id" => "required|exists:users"
            ]
        );

        if (!$validated->fails()) {
            $id = $validated->validated()["id"];
            $user = User::find($id);
            return $user;
        } else {
            return response(["message" => "cant validate your request", "error" => [$validated->errors()]], 400);
        }

    }
    // register user

    /**
     * @throws \Throwable
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:50',
                'email' => 'required|email|unique:users|max:50',
                'password' => 'required'
            ]
        );

        if (!$validate->fails()) {
            $name = $validate->validated()["name"];
            $email = $validate->validated()["email"];
            $password = $validate->validated()["password"];


            $user = new User(
                [
                    "name" => $name,
                    "email" => $email,
                    "password" => bcrypt($password)
                ]
            );
            $user->saveOrFail();
            $token = $user->createToken("salt". $validate->validated()["name"] ."suger")->plainTextToken;
            return response(
                [
                 $user,
                 $token
                ],
                201
            );

        } else {
            return response([
                "message" => "could not validate request",
                "errors" => $validate->errors()
            ], 400);
        }

    }

}
