<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TimetableController
 * 
 * Stub controller - to be implemented in future sessions.
 */
class TimetableController extends Controller
{
    public function __call($method, $parameters)
    {
        return $this->placeholder();
    }

    public function index()
    {
        return $this->placeholder();
    }

    public function create()
    {
        return $this->placeholder();
    }

    public function store(Request $request)
    {
        return $this->placeholder();
    }

    public function show($id)
    {
        return $this->placeholder();
    }

    public function edit($id)
    {
        return $this->placeholder();
    }

    public function update(Request $request, $id)
    {
        return $this->placeholder();
    }

    public function destroy($id)
    {
        return $this->placeholder();
    }

    protected function placeholder()
    {
        $routeName = request()->route()?->getName() ?? 'unknown';
        
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'info',
                'message' => 'This feature is coming soon',
                'route' => $routeName,
            ], 200);
        }

        return response()->view('errors.coming-soon', [
            'route' => $routeName,
            'message' => 'This feature is under development and will be available soon.',
        ], 200);
    }
}
