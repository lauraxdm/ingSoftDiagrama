<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project = Project::find($request->project_id);
        return User::all()->except($project->user_id);
    }

    /**
     * Display a listing of the resource.
     */
    public function invitations(Request $request)
    {
        $user = User::find($request->user_id);
        $invitations = $user->invitations->where('status', '=', '0');
        foreach ($invitations as $key => &$inv) {
           $inv->user_name =$inv->project->user->name;
           $inv->project_name = Project::find($inv->project_id)->name;
        }
        return $invitations;
    }

    /**
     * Display a listing of the resource.
     */
    public function collaborations(User $user)
    {
        $collaborations = $user->projectCollaborations;
        return $collaborations;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
