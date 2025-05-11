<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Place;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AlertController
{
    
    public function createHouse(Request $request)
    {
        Log::channel('stderr')->info('Request createHouse');

        if (!$request->user()) {
            Log::channel('stderr')->info('Unauthorized access attempt');
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }

        // Effectuer la validation des données
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'nullable|string|max:150',
            'adresse' => 'required|string|max:50',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_celebrated' => 'required|boolean',
            'is_decorated' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Récuperer l'id de l'utilisateur
        $userId = $request->user()->id;

        //Regarder si la place existe grâce à l'adresse
        $place = Place::where('adresse', $request->adresse)
            ->where('is_house', 1) // 1 pour alerte
            ->whereRaw("ABS(latitude - ?) <= 0.0005", $request->latitude) // Vérification de la latitude (0.0005 degré)
            ->whereRaw("ABS(longitude - ?) <= 0.0005", $request->longitude) // Vérification de la longitude (0.0005 degré)
            ->first();
        if (!$place) {
            // Si la place n'existe pas, on la crée
            $place = new Place();
            $place->is_alert = 1; // 1 pour alerte
            $place->id_user = $userId;
            $place->designation = $request->title;
            $place->is_house = 1; // 1 pour pas une maison
            $place->is_event = 0; // 0 pour pas un événement
            $place->adresse = $request->adresse;
            $place->latitude = $request->latitude;
            $place->longitude = $request->longitude;
            $place->save();
        }else{
            //Si la place existe déjà, nous allons voir si l'utilisateur a déjà créé une alerte
            $alert = Alert::where('place_id', $place->id)
                ->where('user_id', $userId)
                ->first();
            if ($alert) {
                // Si l'utilisateur a déjà créé une alerte, on ne crée pas de nouvelle alerte
                Log::channel('stderr')->info('Alert already exists for this place', ['user_id' => $userId, 'place_id' => $place->id, 'alert_id' => $alert->id,]);
                return response()->json(['message' => 'Alert already exists for this place'], 409); // Conflict
            }

        }

        // Create the alert
        $alert = new Alert();
        $alert->message = $request->message;
        $alert->is_celebrated = $request->is_celebrated;
        $alert->is_decorated = $request->is_decorated;
        $alert->place_id = $place->id; // Associer l'alerte à la place

        $alert->user_id = $userId;

        $alert->save();

        Log::channel('stderr')->info('Alert created successfully', [
            'user_id' => $userId,
            'place_id' => $place->id,
            'alert_id' => $alert->id,
        ]);
        return response()->json(['message' => 'Alert created successfully'], 201);
    }

    public function createEvent(Request $request)
    {
        Log::channel('stderr')->info('Request register');
        // Regarder si l'utilisateur est authentifié (en utilisant Sanctum)
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }

        // Effectuer la validation des données
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:40|min:5',
            'message' => 'nullable|string|max:150',
            'adresse' => 'required|string|max:40|min:5',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Récuperer l'id de l'utilisateur
        $userId = $request->user()->id;

        //Regarder si la place existe grâce à l'adresse
        $place = Place::where('adresse', $request->adresse)
            ->where('is_event', 1) // 1 pour alerte
            ->whereRaw("ABS(latitude - ?) <= 0.0005", $request->latitude) // Vérification de la latitude (0.0001 degré)
            ->whereRaw("ABS(longitude - ?) <= 0.0005", $request->longitude) // Vérification de la longitude (0.0001 degré)
            ->first();
        if (!$place) {
            // Si la place n'existe pas, on la crée
            $place = new Place();
            $place->is_alert = 1; // 1 pour alerte
            $place->id_user = $userId;
            $place->designation = $request->title;
            $place->is_house = 0; // 1 pour pas une maison
            $place->is_event = 1; // 0 pour pas un événement
            $place->adresse = $request->adresse;
            $place->latitude = $request->latitude;
            $place->longitude = $request->longitude;
            $place->save();
        }

        // Create the alert
        $alert = new Alert();
        $alert->message = $request->message;
        $alert->place_id = $place->id; // Associer l'alerte à la place

        $alert->user_id = $userId;

        $alert->save();

        return response()->json(['message' => 'Alert created successfully'], 201);
    }
}
