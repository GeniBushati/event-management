<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;


class EventController extends Controller
{
    
    use CanLoadRelationships;
    private array $relations = ['user', 'attendees', 'attendees.user'];
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:60, 1')->only(['store', 'destroy','update']);
        $this->authorizeResource(Event::class, 'event');
    }
    public function index()
    {    
        
        
        $query = $this->loadRelationships(Event::query());

       
        return EventResource::collection($query->latest()->paginate());
    }



    public function store(Request $request)
    {
        $event = Event::create([
        ... $request->validate([
        'name'=>'required|string|max:255',
        'description'=>'nullable|string',
        'start_time'=>'required|date',
        'end_time'=>'required|date|after:start_time'
        ]),
        'user_id' => 1
    ]);
        return new EventResource($this->loadRelationships($event));

    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {

        $event->load('user');
     return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {      

        // if (! Gate::allows('update-event', $event)) {
        //     abort(403,'Not authorized to change this');
        // }
           $validated_data = 
             $request->validate([
            'name'=>'sometimes|string|max:255',
            'description'=>'nullable|string',
            'start_time'=>'sometimes|date',
            'end_time'=>'sometimes|date|after:start_time'
             ]);

             $event->update($validated_data);

             return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
       $event->delete();

       return response()->json([
        'message' => 'Event deleted sucessfully'
       ]);
    }
}
