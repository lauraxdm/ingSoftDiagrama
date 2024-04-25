<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $exist = Invitation::where('project_id', $request->project_id)->where('user_id', $request->user_id)->where('status', '!=', 2)->first();
        if (empty($exist)) {
            Invitation::create($request->all());
            return response()->json([
                'message' => 'Invitacion enviada'
            ]);
        } else {
            return response()->json([
                'message' => 'Ya se ha invitado a dicho usuario'
            ]);
        }

        return $exist;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invitation $invitation)
    {
        $invitation->update($request->all());
        if ($request->status == 1) {
            $invitation->user->projectCollaborations()->attach($invitation->project_id);
        }

        return response()->json([
            'message' => 'Your requested data is : ' . $request->status
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
