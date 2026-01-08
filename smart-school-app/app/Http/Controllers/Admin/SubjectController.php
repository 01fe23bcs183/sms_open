<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $subjects = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'type' => ['required', 'in:theory,practical,both'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $subject = Subject::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function show(Subject $subject)
    {
        $subject->load([
            'classSubjects' => function ($query) {
                $query->with(['schoolClass', 'section', 'teacher']);
            },
            'examSchedules' => function ($query) {
                $query->with(['exam', 'schoolClass', 'section'])
                    ->orderBy('exam_date', 'desc')
                    ->take(10);
            },
            'homework' => function ($query) {
                $query->with(['schoolClass', 'section', 'teacher'])
                    ->orderBy('created_at', 'desc')
                    ->take(10);
            },
        ]);

        $statistics = [
            'totalClasses' => $subject->classSubjects->unique('class_id')->count(),
            'totalSections' => $subject->classSubjects->count(),
            'totalTeachers' => $subject->classSubjects->unique('teacher_id')->count(),
            'totalExams' => $subject->examSchedules->count(),
            'totalHomework' => $subject->homework->count(),
        ];

        return view('admin.subjects.show', compact('subject', 'statistics'));
    }

    /**
     * Show the form for editing the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'type' => ['required', 'in:theory,practical,both'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject from storage (soft delete).
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subject $subject)
    {
        if ($subject->classSubjects()->exists()) {
            return back()->with('error', 'Cannot delete subject assigned to classes.');
        }

        if ($subject->examSchedules()->exists()) {
            return back()->with('error', 'Cannot delete subject with exam schedules.');
        }

        $subject->update(['is_active' => false]);
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Toggle the active status of the specified subject.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Subject $subject)
    {
        if ($subject->is_active && $subject->classSubjects()->exists()) {
            return back()->with('error', 'Cannot deactivate subject assigned to classes.');
        }

        $subject->update(['is_active' => !$subject->is_active]);

        $status = $subject->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Subject {$status} successfully.");
    }

    /**
     * Get all active subjects (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $subjects = Subject::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        return response()->json($subjects);
    }

    /**
     * Get subjects by type (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType(Request $request)
    {
        $type = $request->get('type');

        $query = Subject::active()->orderBy('name');

        if ($type && in_array($type, ['theory', 'practical', 'both'])) {
            $query->where('type', $type);
        }

        $subjects = $query->get(['id', 'name', 'code', 'type']);

        return response()->json($subjects);
    }
}
