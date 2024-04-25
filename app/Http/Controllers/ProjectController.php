<?php

namespace App\Http\Controllers;

use App\Events\InvitationSent;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $myProjects = $user->myProjects;
        $user_id = $user->id;
        return Inertia::render('Projects', compact('myProjects', 'user_id'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('welcome');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        Project::create($request->all());
        return redirect('projects');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $diagrams = $project->diagrams;
        $collaborators = $project->users;
        $collaborators->push($project->user);
        if ($collaborators->firstWhere('id', Auth::user()->id)) {
            return Inertia::render('ShowProjects', compact('project', 'diagrams', 'collaborators'));
        }
        else {
            return 'Este usuario no tiene acceso a este proyecto';
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->all());
        return redirect('projects');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return redirect('projects')->with('message', [
                'text' => 'Proyecto eliminado correctamente',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect('projects')->with(
                'message',
                [
                    'text' => 'Ha ocurrido un error' . $e->getMessage(),
                    'type' => 'error'
                ]
            );
        }
    }
}
