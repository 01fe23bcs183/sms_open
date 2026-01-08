<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Placeholder Controller
 * 
 * This controller provides placeholder responses for routes that reference
 * controllers not yet implemented. It will be removed once all controllers
 * are created in future sessions.
 */
class PlaceholderController extends Controller
{
    /**
     * Handle any action and return a placeholder response.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function __call($method, $parameters)
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

    /**
     * Index action placeholder.
     */
    public function index()
    {
        return $this->placeholder();
    }

    /**
     * Create action placeholder.
     */
    public function create()
    {
        return $this->placeholder();
    }

    /**
     * Store action placeholder.
     */
    public function store(Request $request)
    {
        return $this->placeholder();
    }

    /**
     * Show action placeholder.
     */
    public function show($id)
    {
        return $this->placeholder();
    }

    /**
     * Edit action placeholder.
     */
    public function edit($id)
    {
        return $this->placeholder();
    }

    /**
     * Update action placeholder.
     */
    public function update(Request $request, $id)
    {
        return $this->placeholder();
    }

    /**
     * Destroy action placeholder.
     */
    public function destroy($id)
    {
        return $this->placeholder();
    }

    /**
     * Generic placeholder response.
     */
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
