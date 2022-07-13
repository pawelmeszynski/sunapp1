<?php

namespace SunAppModules\SunBet\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use SunAppModules\SunBet\Entities\SunbetUser;
use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use SunAppModules\Core\src\FormBuilder\Form;
use SunAppModules\Core\Entities\Model;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $prefix = 'sunbet:user';
    protected $class = Model::class;
    protected $formClass = Form::class;

    /**
     * Controller constructor.
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;

        $this->repository->setModel($this->class)->setSearchable([
            //
        ]);
    }

    /**
     * Handles user logins
     *
     * @return JsonResponse
     */
//    public function login(Request $request): JsonResponse
//    {
//        $request->validate([
//            'email' => 'required|email|exists:sunbet_users',
//            'password' => 'required|string'
//        ]);
//
//        $credentials = request(['email', 'password']);
//
//        if(Auth::attempt($credentials)){
//            return response()->json([
//                "message" => "Invalid email or password"
//            ], 401);
//        }
//
//        $user = $request->user();
//
//        $token = $user->createToken('AccessToken');
//
//        $user->access_token = $token->accessToken;
//
//        return response()->json([
//            "user" => $user
//        ],200);
//    }
//
//    /**
//     * Register api
//     *
//     * @return JsonResponse
//     */
//    public function signup(Request $request): JsonResponse
//    {
//        $request->validate([
//            'name' => 'required|string',
//            'email' => 'required|email|unique:sunbet_users',
//            'password' => 'required|string'
//        ]);
//
//        $user = new SunbetUser([
//            'name' => $request->name,
//            'email' => $request->email,
//            'password' =>Hash::make($request->password)
//        ]);
//
//        $user->save();
//        return response()->json([
//            "message" => "User registered successfully"
//        ],201);
//    }
//
//    /**
//     * details api
//     *
//     * @return JsonResponse
//     */
//    public function details(): JsonResponse
//    {
//
//    }
}

