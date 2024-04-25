<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiagramRequest;
use App\Http\Requests\UpdateDiagramRequest;
use App\Models\Diagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DiagramController extends Controller
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
    public function store(StoreDiagramRequest $request)
    {
        try {
            $request->merge(['content' => '']);
            Diagram::create($request->all());
            return redirect()->route('projects.show', $request->project_id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagram $diagram)
    {
        $project = $diagram->project;
        $collaborators = $project->users;
        $collaborators->push($project->user);
        if ($collaborators->firstWhere('id', Auth::user()->id)) {
            return Inertia::render('DiagramEditor', compact('diagram', 'project'));
        } else {
            return 'Este usuario no tiene acceso a este diagrama';
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diagram $diagram)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiagramRequest $request, Diagram $diagram)
    {
        try {
            $diagram->update($request->all());
            return redirect()->route('projects.show', $request->project_id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagram $diagram)
    {
        try {
            $diagram->delete();
            return redirect()->back()->with('message', [
                'text' => 'Proyecto eliminado correctamente',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with(
                'message',
                [
                    'text' => 'Ha ocurrido un error' . $e->getMessage(),
                    'type' => 'error'
                ]
            );
        }
    }
}
