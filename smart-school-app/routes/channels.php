<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Broadcast Channels
 * 
 * Prompt 307: Enable Real-Time Events for UI Updates
 * 
 * Defines private and presence channels with authorization callbacks.
 * These channels are used for real-time notifications and updates.
 */

/*
|--------------------------------------------------------------------------
| User Private Channel
|--------------------------------------------------------------------------
|
| Private channel for user-specific notifications and updates.
| Only the authenticated user can subscribe to their own channel.
|
*/
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Notifications Channel
|--------------------------------------------------------------------------
|
| Private channel for user notifications.
| Used by the notification bell icon in the header.
|
*/
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/*
|--------------------------------------------------------------------------
| Dashboard Channel
|--------------------------------------------------------------------------
|
| Private channel for dashboard updates.
| Broadcasts metrics updates, new activities, etc.
|
*/
Broadcast::channel('dashboard.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/*
|--------------------------------------------------------------------------
| Admin Channel
|--------------------------------------------------------------------------
|
| Private channel for admin-only broadcasts.
| Only users with admin role can subscribe.
|
*/
Broadcast::channel('admin', function ($user) {
    return $user->hasRole('admin');
});

/*
|--------------------------------------------------------------------------
| Class Channel
|--------------------------------------------------------------------------
|
| Private channel for class-specific updates.
| Teachers and students of the class can subscribe.
|
*/
Broadcast::channel('class.{classId}', function ($user, $classId) {
    if ($user->hasRole('admin')) {
        return true;
    }
    
    if ($user->hasRole('teacher')) {
        return $user->classSubjects()->where('class_id', $classId)->exists();
    }
    
    if ($user->hasRole('student')) {
        return $user->student?->class_id === (int) $classId;
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Attendance Channel
|--------------------------------------------------------------------------
|
| Private channel for attendance updates.
| Teachers and admins can subscribe.
|
*/
Broadcast::channel('attendance.{classId}.{sectionId}', function ($user, $classId, $sectionId) {
    if ($user->hasRole('admin')) {
        return true;
    }
    
    if ($user->hasRole('teacher')) {
        return $user->classSubjects()
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Fees Channel
|--------------------------------------------------------------------------
|
| Private channel for fee-related updates.
| Admins and accountants can subscribe.
|
*/
Broadcast::channel('fees', function ($user) {
    return $user->hasRole(['admin', 'accountant']);
});

/*
|--------------------------------------------------------------------------
| Student Channel
|--------------------------------------------------------------------------
|
| Private channel for student-specific updates.
| The student and their parents can subscribe.
|
*/
Broadcast::channel('student.{studentId}', function ($user, $studentId) {
    if ($user->hasRole('admin')) {
        return true;
    }
    
    if ($user->hasRole('student')) {
        return $user->student?->id === (int) $studentId;
    }
    
    if ($user->hasRole('parent')) {
        return $user->children()->where('id', $studentId)->exists();
    }
    
    return false;
});
