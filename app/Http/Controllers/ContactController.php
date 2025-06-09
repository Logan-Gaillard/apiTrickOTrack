<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContactController
{
    function getOrCreate(Request $request){
        // Log the request for debugging
        Log::channel('stderr')->info('Contact create request', $request->all());

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'sweeter_id' => 'required|integer|max:255',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        //regarder tout d'abord s'il n'existe pas déjà un contact entre l'émetteur et le récepteur (l'utilisateur peut être émetteur ou récepteur)
        $existingContact = Contact::where(function ($query) use ($request) {
            $query->where('id_recepteur', $request->user()->id)
                  ->where('id_emetteur', $request->sweeter_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('id_recepteur', $request->sweeter_id)
                  ->where('id_emetteur', $request->user()->id);
        })->first();

        if($existingContact){
            Log::channel('stderr')->info("Contact already exists", $existingContact->toArray());
            return response()->json([
                'status' => 'success',
                'message' => 'Contact already exists',
                'data' => $existingContact
            ]);
        }

        //Création du contact
        $contact = new Contact();
        $contact->id_recepteur = $request->user()->id;
        $contact->id_emetteur = $request->sweeter_id;
        $contact->save();

        Log::channel('stderr')->info("Contact message created successfully", $request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Contact message created successfully',
            'data' => $contact
        ]);
    }

    function getMessages(Request $request){
        // Log the request for debugging
        Log::channel('stderr')->info('Contact getMessages request', $request->all());

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'sweeter_id' => 'required|integer|max:255',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Retrieve messages between the receiver and sender
        $contact = Contact::where(function ($query) use ($request) {
            $query->where('id_recepteur', $request->user()->id)
                  ->where('id_emetteur', $request->sweeter_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('id_recepteur', $request->sweeter_id)
                  ->where('id_emetteur', $request->user()->id);
        })->first();

        if (empty($contact)) {
            //Si aucun contact alors le créer
            Log::channel('stderr')->info("Création d'un nouveau contact", $request->all());
            $contact = new Contact();
            $contact->id_recepteur = $request->sweeter_id; //Celui qui reçoit le message
            $contact->id_emetteur = $request->user()->id; // Celui qui envoie le message
            $contact->save();
        }

        $messages = $contact->messages()->get();
        Log::channel('stderr')->info("Contact messages retrieved successfully", $messages->toArray());
        return response()->json([
            'status' => 'success',
            'message' => 'Contact messages retrieved successfully',
            'messages' => empty($messages) ? [] : $messages->toArray()
        ]);
    }

    function sendMessage(Request $request){
        // Log the request for debugging
        Log::channel('stderr')->info('Contact sendMessage request', $request->all());

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }else{
            $request->user()->last_activity = now();
            $request->user()->save(); // Mettre à jour la dernière activité de l'utilisateur
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|integer|max:255',
            'content' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::channel('stderr')->info("Validation failed", $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Retrieve or create contact
        $contact = Contact::where('id', $request->contact_id)->first();

        if (empty($contact)) {
            Log::channel('stderr')->info("Création d'un nouveau contact pour l'envoi du message", $request->all());
            return response()->json([
                'status' => 'error',
                'message' => 'Contact non trouvé'
            ], 404); // Not Found
        }

        // Create and save the message
        $message = new Message();
        $message->content = $request->content;
        $message->author_id = $request->user()->id;
        $message->contact_id = $contact->id;
        $message->save();

        Log::channel('stderr')->info("Contact message sent successfully", ['message' => $message]);
        return response()->json([
            'status' => 'success',
            'message' => 'Contact message sent successfully',
            'data' => $message
        ]);
    }
}
