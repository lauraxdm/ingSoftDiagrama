<?php

namespace App\Http\Controllers\Api;

use App\Events\Diagram as EventsDiagram;
use App\Http\Controllers\Controller;
use App\Models\Diagram;
use App\Services\ExportCodeService;
use App\Services\ExportXMIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiagramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get(Diagram $diagram)
    {
        if (Cache::has('diagram' . $diagram->id)) {
            $diagram->content = Cache::get('diagram' . $diagram->id);
        }
        return $diagram;
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagram $diagram)
    {
        //
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
    public function update(Request $request, Diagram $diagram)
    {
        if ($request->content != $diagram->content) {
            try {
                EventsDiagram::broadcast(
                    $diagram->id,
                    $request->content,
                );
            } catch (\Throwable $th) {
                
            }
            

            if ($request->cache) {
                Cache::put('diagram' . $diagram->id, $request->content, now()->addDay());
                return response()->json([
                    'message' => 'Diagrama actualizado en cache'
                ]);
            } else {

                $diagram->update($request->all());
                return response()->json([
                    'message' => 'Diagrama actualizado con exito'
                ]);
            }
        } else {
            Cache::forget('diagram' . $diagram->id);
            return response()->json([
                'message' => 'No se encontraron cambios'
            ]);
        };
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagram $diagram)
    {
        //
    }

    /**
     * 
     */
    public function exportJava(Diagram $diagram)
    {
        $content = '';
        if (Cache::has('diagram' . $diagram->id)) {
            $content = Cache::get('diagram' . $diagram->id);
        } else {
            $content = $diagram->content;
        }
        $content = ExportCodeService::generateCode('java', $diagram->name, $content);
        return ($content);
    }

    /**
     * 
     */
    public function exportPHP(Diagram $diagram)
    {
        $content = '';
        if (Cache::has('diagram' . $diagram->id)) {
            $content = Cache::get('diagram' . $diagram->id);
        } else {
            $content = $diagram->content;
        }

        $content = ExportCodeService::generateCode('php', $diagram->name, $content);
        return ($content);
    }

    /**
     * 
     */
    public function exportCSharp(Diagram $diagram)
    {
        $content = '';
        if (Cache::has('diagram' . $diagram->id)) {
            $content = Cache::get('diagram' . $diagram->id);
        } else {
            $content = $diagram->content;
        }

        $content = ExportCodeService::generateCode('csharp', $diagram->name, $content);
        return ($content);
    }

    /**
     * 
     */
    public function exportXMI(Diagram $diagram)
    {
        $content = '';
        if (Cache::has('diagram' . $diagram->id)) {
            $content = Cache::get('diagram' . $diagram->id);
        } else {
            $content = $diagram->content;
        }

        $content = ExportXMIService::generate($diagram->name, $content);
        return ($content);
    }

    
    /**
     * 
     */
    public function exportCpp(Diagram $diagram)
    {
        $content = '';
        if (Cache::has('diagram' . $diagram->id)) {
            $content = Cache::get('diagram' . $diagram->id);
        } else {
            $content = $diagram->content;
        }

        $content = ExportCodeService::generateCode('cpp', $diagram->name, $content);
        return ($content);
    }
}
