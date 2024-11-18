<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller
{
    use CanLoadRelationships;
    private array $relations = ['user','event'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:60, 1')->only(['store', 'destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }
    public function index(Event $event)
    {

        $query = $event->attendees()->getQuery();
        $attendees = $this->loadRelationships($query,$this->relations);

        return AttendeeResource::collection($attendees->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
     $attendee = $event->attendees()->create([
            'user_id' => $request->user()->id,
     ]);

     return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {   

        // if (! Gate::allows('delete-attendee', [$event, $attendee])) {
        //     abort(403,'Not authorized to delete this');
        // }

        $attendee->delete();

        return response(status: 204);
    }
}
