<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController
{
    public function register(Request $request)
    {
        Log::channel('stderr')->info('Request register');

        $validator = Validator::make($request->all(), [
            'nickname' => 'required|unique:users,nickname|max:25|min:3',
            'nom' => 'string|max:30',
            'prenom' => 'string|max:30',
            'email' => 'required|email|unique:users,email|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'type' => 'validation',
                'errors' => $validator->errors()
            ], 200);
        }

        $user = User::create([
            'nickname' => $request->nickname,
            'nom' => str($request->nom)->upper(),
            'prenom' => str($request->prenom)->ucfirst(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'last_update' => now(),
            'last_connexion' => now(),
        ]);

        if(!$user) {
            Log::channel('stderr')->info("Failed to create user", $request->all());

            return response()->json([
                'status' => 'success',
                'type' => 'userCreation',
                'error' => 'Failed to create user',
            ], 200);
        }

        Log::channel('stderr')->info("Successfully created user", $request->all());
        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {
        Log::channel('stderr')->info('Request login');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'type' => 'validation',
                'errors' => $validator->errors()
            ], 200);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {            
            return response()->json([
                'status' => 'error',
                'type' => 'login',
                'error' => 'Identifiants incorrects.',
            ], 200);
        }

        $user->last_connexion = now();
        $user->save();

        $user->tokens()->delete(); // supprime tous les anciens tokens

        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    public function getUser(Request $request)
    {
        Log::channel('stderr')->info('Request getUser');

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'type' => 'user',
                'error' => 'Utilisateur non trouvé.',
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }
}