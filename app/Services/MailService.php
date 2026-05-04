<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherAccountCreated;
use App\Mail\StudentAccountCreated;
use App\Mail\GuardianAccountCreated;
use App\Mail\AdminAccountCreated;
use App\Models\User;

class MailService
{
    /**
     * Send email with account details to the newly created teacher.
     */
    public function sendTeacherAccountCreatedInfo(User $user, string $password)
    {
        Mail::to($user->email)->send(new TeacherAccountCreated($user, $password));
    }

    /**
     * Send email with account details to the newly created student.
     */
    public function sendStudentAccountCreatedInfo(User $user, string $password)
    {
        Mail::to($user->email)->send(new StudentAccountCreated($user, $password));
    }

    /**
     * Send email with account details to the newly created guardian.
     */
    public function sendGuardianAccountCreatedInfo(User $user, string $password)
    {
        Mail::to($user->email)->send(new GuardianAccountCreated($user, $password));
    }

    /**
     * Send email with account details to the newly created admin.
     */
    public function sendAdminAccountCreatedInfo(User $user, string $password)
    {
        Mail::to($user->email)->send(new AdminAccountCreated($user, $password));
    }
}
