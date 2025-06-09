<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Place;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PlaceController
{
    
    public function createHouse(Request $request)
    {
        Log::channel('stderr')->info('Request createHouse');

        if (!$request->user()) {
            Log::channel('stderr')->info('Unauthorized access attempt');
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Effectuer la validation des données
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'nullable|string|max:150',
            'adresse' => 'required|string|max:100|min:10',
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
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Effectuer la validation des données
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:40|min:5',
            'message' => 'nullable|string|max:150',
            'adresse' => 'required|string|max:100|min:10',
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

    public function getPlaces(Request $request)
    {
        Log::channel('stderr')->info('Request getPlaces');

        if (!$request->user()) {
            Log::channel('stderr')->info('Unauthorized access attempt');
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Effectuer la validation des données
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation échoué", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Récuperer les emplacement dans un rayon de 5km
        $allPlaces = Place::whereRaw("ABS(latitude - ?) <= ?", [$request->latitude, 0.3])
            ->whereRaw("ABS(longitude - ?) <= ?", [$request->longitude, 0.3])
            ->get();

        Log::channel('stderr')->info('Nombre de places récupérées : ' . $allPlaces->count());

        if ($allPlaces->isEmpty()) { // S'il y a pas d'alerte à proximité
            return response()->json(['message' => 'Aucun emplacement a proximite', 'hasPlaces' => false], 200);
        }

        // Récuperer les alertes
        $places = [];
        foreach ($allPlaces as $place) {
            $marks = Alert::where('place_id', $place->id)
                ->orderBy('created_at', 'desc')
                ->get();

            if (!$marks->isEmpty()) {

                Log::channel('stderr')->info('Nombre d\'alertes récupérées : ' . $marks->count(), $marks->toArray());

                // Préparer des l'marquages
                $markDataList = [];
                foreach ($marks as $mark) {
                    $markDataList[] = [
                        'id' => $mark->id,
                        'message' => $mark->message,
                        'is_celebrated' => $mark->is_celebrated,
                        'is_decorated' => $mark->is_decorated,
                        'author_id' => $mark->user_id,
                        'author_nickname' => $mark->user ? $mark->user->nickname : 'Inconnu',
                        'created_at' => $mark->created_at,
                    ];
                }

                if (!isset($places[$place->id])) {
                    $places[$place->id] = [
                        'id' => $place->id,
                        'is_alert' => $place->is_alert,
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                        'designation' => $place->designation,
                        'is_house' => $place->is_house,
                        'is_event' => $place->is_event,
                        'adresse' => $place->adresse,
                        'author_id' => $place->id_user,
                        'author_nickname' => $place->user ? $place->user->nickname : 'Inconnu',
                        'marks' => []
                    ];
                }

                // Affecter la liste triée des marks
                $places[$place->id]['marks'] = $markDataList;
            }
        }

        Log::channel('stderr')->info('Nombre d\'alertes récupérées : ' . count($places), $places);

        if (!$places) { // S'il y a pas d'alerte à proximité
            return response()->json(['message' => 'Aucune alerte a proximite', 'hasPlaces' => false], 200);
        }

        return response()->json(['message' => 'Places found', 'places' => $places, 'hasPlaces' => true], 200);
    }
}