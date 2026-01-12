<?php

namespace App\Notifications;

use App\Models\OnboardingProgress;
use Illuminate\Mail\Mailable;

class OnboardingComplete extends Mailable
{
    /**
     * @param  \App\Models\OnboardingProgress  $progress
     * @return void
     */
    public function __construct(public OnboardingProgress $progress) {}

    /**
     * Build the mail representation of the notification.
     */
    public function build()
    {
        $user = $this->progress->user;
        $completedSteps = $this->progress->getCompletedStepsCount();
        $skippedSteps = $this->progress->getSkippedStepsCount();
        $durationHours = $this->progress->getDurationInHours();

        return $this
            ->to($user->email)
            ->subject(trans('admin::app.emails.onboarding.complete.subject'))
            ->view('emails.onboarding.complete', [
                'user_name' => $user->name,
                'completed_steps' => $completedSteps,
                'skipped_steps' => $skippedSteps,
                'duration_hours' => $durationHours,
                'progress' => $this->progress,
            ]);
    }
}
