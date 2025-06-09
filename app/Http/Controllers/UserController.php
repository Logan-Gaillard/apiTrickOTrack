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

    public function updateUserPosition(Request $request)
    {
        Log::channel('stderr')->info('Request updateUserPosition');

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation échoué", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        $user = $request->user();

        if (!$request->user()) {
            Log::channel('stderr')->info('Utilisateur non authentifié');
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }

        if ($user->date_last_position && now()->diffInMinutes($user->date_last_position) < 10) {
            return response()->json([
                'status' => 'error',
                'type' => 'position',
                'error' => 'La dernière position date de moins de 10 minutes.',
            ], 200);
        }

        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->date_last_position = now();
        $user->last_activity = now();
        $user->save();

        return response()->json(['message' => 'Position du sweeter mise à jour avec succès'], 200);

    }

    public function getSweeterNearby(Request $request)
    {
        Log::channel('stderr')->info('Request getSweeterNearby');

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation échoué", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Erreur de validation
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        $user = $request->user();

        if (!$request->user()) {
            Log::channel('stderr')->info('Unauthorized access attempt');
            return response()->json(['message' => 'Unauthorized'], 401); // Non autorisé
        }

        $users = User::whereRaw("ABS(latitude - ?) <= ?", [$request->latitude, 0.1])
            ->whereRaw("ABS(longitude - ?) <= ?", [$request->longitude, 0.1])
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->get();

        $sweeters = [];
        foreach ($users as $sweeter) {
            if ($sweeter->id !== $user->id) { // Exclure l'utilisateur lui-même
                $sweeters[] = [
                    'id' => $sweeter->id,
                    'nickname' => $sweeter->nickname,
                    'latitude' => $sweeter->latitude,
                    'longitude' => $sweeter->longitude,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'sweeters' => $sweeters
        ]);
    }
}