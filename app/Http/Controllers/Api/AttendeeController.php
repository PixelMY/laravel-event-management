<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;

    private array $relations = ['user'];

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show', 'update']),
            new Middleware('throttle:60,1', only: ['store', 'destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        Gate::authorize('viewAny', Attendee::class);

        $attendees = $this->loadRelationships($event->attendees()->latest());

        return AttendeeResource::collection($attendees->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        Gate::authorize('create', Attendee::class);

        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => $request->user->id,
            ])
        );

        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        Gate::authorize('view', $attendee);

        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        // Gate::authorize('delete-attendee', [$event, $attendee]);

        Gate::authorize('delete', $attendee);

        $attendee->delete();

        return response(status: 204);
    }
}
