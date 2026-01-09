<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\ExamSchedule;
use App\Models\ExamGrade;
use App\Models\ExamMark;
use App\Models\ExamAttendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Exam Service
 * 
 * Prompt 327: Create Exam Service
 * 
 * Manages exams and schedules consistently. Creates exams, schedules,
 * and grading rules. Validates date conflicts and prepares marks entry templates.
 */
class ExamService
{
    /**
     * Create a new exam.
     * 
     * @param array $data
     * @return Exam
     */
    public function createExam(array $data): Exam
    {
        return DB::transaction(function () use ($data) {
            $exam = Exam::create([
                'academic_session_id' => $data['academic_session_id'],
                'exam_type_id' => $data['exam_type_id'],
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Create schedules if provided
            if (isset($data['schedules']) && is_array($data['schedules'])) {
                foreach ($data['schedules'] as $scheduleData) {
                    $this->createSchedule($exam->id, $scheduleData);
                }
            }
            
            return $exam->load(['examType', 'examSchedules']);
        });
    }

    /**
     * Update an exam.
     * 
     * @param Exam $exam
     * @param array $data
     * @return Exam
     */
    public function updateExam(Exam $exam, array $data): Exam
    {
        $exam->update($data);
        return $exam->fresh(['examType', 'examSchedules']);
    }

    /**
     * Delete an exam.
     * 
     * @param Exam $exam
     * @return bool
     * @throws \Exception
     */
    public function deleteExam(Exam $exam): bool
    {
        // Check if exam has marks entered
        $hasMarks = ExamMark::whereHas('examSchedule', function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->exists();
        
        if ($hasMarks) {
            throw new \Exception('Cannot delete exam with marks already entered.');
        }
        
        return DB::transaction(function () use ($exam) {
            // Delete schedules
            $exam->examSchedules()->delete();
            
            // Delete exam
            return $exam->delete();
        });
    }

    /**
     * Create an exam schedule.
     * 
     * @param int $examId
     * @param array $data
     * @return ExamSchedule
     * @throws \Exception
     */
    public function createSchedule(int $examId, array $data): ExamSchedule
    {
        // Check for date conflicts
        if ($this->hasScheduleConflict($examId, $data['class_id'], $data['section_id'], $data['exam_date'], $data['start_time'], $data['end_time'])) {
            throw new \Exception('Schedule conflict detected for this class/section on the given date and time.');
        }
        
        return ExamSchedule::create([
            'exam_id' => $examId,
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'subject_id' => $data['subject_id'],
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room_number' => $data['room_number'] ?? null,
            'full_marks' => $data['full_marks'],
            'passing_marks' => $data['passing_marks'],
            'theory_marks' => $data['theory_marks'] ?? null,
            'practical_marks' => $data['practical_marks'] ?? null,
        ]);
    }

    /**
     * Update an exam schedule.
     * 
     * @param ExamSchedule $schedule
     * @param array $data
     * @return ExamSchedule
     */
    public function updateSchedule(ExamSchedule $schedule, array $data): ExamSchedule
    {
        $schedule->update($data);
        return $schedule->fresh();
    }

    /**
     * Delete an exam schedule.
     * 
     * @param ExamSchedule $schedule
     * @return bool
     * @throws \Exception
     */
    public function deleteSchedule(ExamSchedule $schedule): bool
    {
        // Check if schedule has marks
        if ($schedule->examMarks()->count() > 0) {
            throw new \Exception('Cannot delete schedule with marks already entered.');
        }
        
        return $schedule->delete();
    }

    /**
     * Check for schedule conflicts.
     * 
     * @param int $examId
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeScheduleId
     * @return bool
     */
    public function hasScheduleConflict(
        int $examId,
        int $classId,
        int $sectionId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null
    ): bool {
        $query = ExamSchedule::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('exam_date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });
        
        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }
        
        return $query->exists();
    }

    /**
     * Get marks entry template for a schedule.
     * 
     * @param int $scheduleId
     * @return array
     */
    public function getMarksEntryTemplate(int $scheduleId): array
    {
        $schedule = ExamSchedule::with(['exam', 'schoolClass', 'section', 'subject'])
            ->findOrFail($scheduleId);
        
        $students = Student::with('user')
            ->where('class_id', $schedule->class_id)
            ->where('section_id', $schedule->section_id)
            ->where('is_active', true)
            ->orderBy('roll_number')
            ->get();
        
        $existingMarks = ExamMark::where('exam_schedule_id', $scheduleId)
            ->pluck('obtained_marks', 'student_id')
            ->toArray();
        
        $template = [];
        foreach ($students as $student) {
            $template[] = [
                'student_id' => $student->id,
                'student_name' => $student->user->full_name ?? '',
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'obtained_marks' => $existingMarks[$student->id] ?? null,
                'full_marks' => $schedule->full_marks,
                'passing_marks' => $schedule->passing_marks,
            ];
        }
        
        return [
            'schedule' => $schedule,
            'students' => $template,
        ];
    }

    /**
     * Get all exams.
     * 
     * @param int|null $sessionId
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllExams(?int $sessionId = null, bool $activeOnly = true)
    {
        $query = Exam::with(['examType', 'academicSession']);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('start_date', 'desc')->get();
    }

    /**
     * Get exam schedules for a class/section.
     * 
     * @param int $examId
     * @param int|null $classId
     * @param int|null $sectionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSchedules(int $examId, ?int $classId = null, ?int $sectionId = null)
    {
        $query = ExamSchedule::with(['schoolClass', 'section', 'subject'])
            ->where('exam_id', $examId);
        
        if ($classId) {
            $query->where('class_id', $classId);
        }
        
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        
        return $query->orderBy('exam_date')->orderBy('start_time')->get();
    }

    /**
     * Get exam types.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExamTypes()
    {
        return ExamType::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Get exam grades.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExamGrades()
    {
        return ExamGrade::orderBy('min_percentage', 'desc')->get();
    }

    /**
     * Get grade for a percentage.
     * 
     * @param float $percentage
     * @return ExamGrade|null
     */
    public function getGradeForPercentage(float $percentage): ?ExamGrade
    {
        return ExamGrade::where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();
    }

    /**
     * Get upcoming exams.
     * 
     * @param int|null $sessionId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingExams(?int $sessionId = null, int $limit = 5)
    {
        $query = Exam::with('examType')
            ->where('start_date', '>', now())
            ->where('is_active', true);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->orderBy('start_date')->limit($limit)->get();
    }

    /**
     * Get ongoing exams.
     * 
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOngoingExams(?int $sessionId = null)
    {
        $query = Exam::with('examType')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_active', true);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->get();
    }

    /**
     * Get exam statistics.
     * 
     * @param int|null $sessionId
     * @return array
     */
    public function getStatistics(?int $sessionId = null): array
    {
        $query = Exam::query();
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        $total = $query->count();
        $upcoming = (clone $query)->where('start_date', '>', now())->count();
        $ongoing = (clone $query)->where('start_date', '<=', now())->where('end_date', '>=', now())->count();
        $completed = (clone $query)->where('end_date', '<', now())->count();
        
        return [
            'total' => $total,
            'upcoming' => $upcoming,
            'ongoing' => $ongoing,
            'completed' => $completed,
        ];
    }
}
