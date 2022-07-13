<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Link;

class UserController extends Controller
{
    // get user by id
    public function get(Request $request)
    {

            $user = Auth::user();
            if($user != null){

                return $user;
            }else{
                return response(["message"=>"user not found"],400);
            }

    }
    // register user

    /**
     * @throws \Throwable
     * @throws \Illuminate\Validation\ValidationException
     * register user
     */
    public function register(Request $request)
    {
        // validates inputs :
        $validate = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:50',
                'email' => 'required|email|unique:users|max:50',
                'password' => 'required|max:50',
                'icon' => 'required|image|mimes:png,jpg,jpeg|max:2048'
            ]
        );
        // if validate = true
        if (!$validate->fails()) {

            $iconPath = "public/icons";
            // user arg :
            $name = $validate->validated()["name"];
            $email = $validate->validated()["email"];
            $password = $validate->validated()["password"];

            // input icon
            $icon = $request->file('icon');
            if ($icon){
                //
                $iconName = $icon->getBasename() . time() .'.'. $icon->getClientOriginalExtension();
                $icon->storeAs($iconPath,$iconName);

                $user = new User(
                    [
                        "name" => $name,
                        "email" => $email,
                        "password" => bcrypt($password),
                        "icon" => asset('/storage/icons/'. $iconName)
                    ]
                );
                $user->saveOrFail();
                $token = $user->createToken("salt". $validate->validated()["name"] ."sugar")->plainTextToken;
                return response([
                    $user,
                    $token

                ],200);
            }

        } else {
            return response([
                "message" => "could not validate request",
                "errors" => $validate->errors()
            ], 400);
        }

    }

    // update user data
    public function update(Request $request){

        $validate = Validator::make(
            $request->all(),
            [
                'name' => 'max:50',
                'email' => 'email|unique:users|max:50',
                'password' => 'max:50',
                'last_password' => 'required|max:50'
            ]
        );

        if(!$validate->fails()){
            $user = Auth::user();
            if($user != null){
                if(Hash::check($validate->validated()['last_password'],$user->password)){

                    if(isset($validate->validated()['name'])){

                        $user->name = $validate->validated()['name'];
                    }

                    if(isset($validate->validated()['email'])){

                        $user->email = $validate->validated()['email'];
                    }

                    if(isset($validate->validated()['password'])){
                        $password = $validate->validated()['password'];
                        $user->password = bcrypt($password);

                    }
                    $user->saveOrFail();
                    return response([$user],200);
                }else{
                    return response(
                        ["message"=>"Password is not correct !"]
                        ,401
                    );
                }

            }else{
                return response(
                    ["message"=>"there is not any user with this info"]
                    ,401
                );
            }
        }else{
            return response(
                ["message"=>"cant validate input","errors"=>$validate->failed()]
            ,401
            );
        }

    }

    // login user :
    public function login(Request $request){
        $validator = Validator::make($request->all(),
        [
            'email' => 'required|email|exists:users|max:50',
            'password' => 'required|max:50',

        ]);

        if(!$validator->fails()){
            $email = $validator->validated()['email'];
            $password = $validator->validated()['password'];
            $user = User::where('email',$email)->first();
            // checks pwd
            if(Hash::check($password,$user->password)){
                $user->tokens()->delete();
                $token = $user->createToken("salt". $validator->validated()["email"] ."sugar")->plainTextToken;
                return response([$user,$token],200);

            }else{
                return response(["message"=>"Password is Incorrect"],401);
            }
        }else{
            return response(["message"=>"Cant Validate Request",$validator->errors()],400);
        }

    }

    // log out :
    public function logout(){
        Auth::user()->tokens()->delete();
        return response(["message"=>"User Logged Out !"],200);
    }


}
