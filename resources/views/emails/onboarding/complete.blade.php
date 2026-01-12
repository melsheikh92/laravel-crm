@component('admin::emails.layout')
    <!-- Congratulations Header -->
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('admin::app.emails.onboarding.complete.greeting', ['username' => $user_name]) 🎉
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 16px;">
            @lang('admin::app.emails.onboarding.complete.intro')
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('admin::app.emails.onboarding.complete.completion-summary', [
                'completed_steps' => $completed_steps,
                'total_steps' => $completed_steps + $skipped_steps
            ])
        </p>
    </div>

    <!-- Next Steps Section -->
    <div style="margin-bottom: 34px;">
        <h2 style="font-weight: 600;font-size: 18px;color: #121A26;line-height: 24px;margin-bottom: 16px;">
            @lang('admin::app.emails.onboarding.complete.next-steps.title')
        </h2>

        <div style="background-color: #F9FAFB;border-radius: 8px;padding: 20px;margin-bottom: 16px;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 8px 0;">
                        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                            <strong style="color: #121A26;">1.</strong>
                            @lang('admin::app.emails.onboarding.complete.next-steps.add-contacts')
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">
                        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                            <strong style="color: #121A26;">2.</strong>
                            @lang('admin::app.emails.onboarding.complete.next-steps.create-deals')
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">
                        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                            <strong style="color: #121A26;">3.</strong>
                            @lang('admin::app.emails.onboarding.complete.next-steps.invite-team')
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">
                        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                            <strong style="color: #121A26;">4.</strong>
                            @lang('admin::app.emails.onboarding.complete.next-steps.explore-features')
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Quick Start Guide Section -->
    <div style="margin-bottom: 34px;">
        <h2 style="font-weight: 600;font-size: 18px;color: #121A26;line-height: 24px;margin-bottom: 16px;">
            @lang('admin::app.emails.onboarding.complete.quick-guide.title')
        </h2>

        <div style="border-left: 4px solid #3B82F6;padding-left: 16px;margin-bottom: 12px;">
            <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                <strong style="color: #121A26;">@lang('admin::app.emails.onboarding.complete.quick-guide.contacts.title')</strong><br>
                @lang('admin::app.emails.onboarding.complete.quick-guide.contacts.description')
            </p>
        </div>

        <div style="border-left: 4px solid #10B981;padding-left: 16px;margin-bottom: 12px;">
            <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                <strong style="color: #121A26;">@lang('admin::app.emails.onboarding.complete.quick-guide.deals.title')</strong><br>
                @lang('admin::app.emails.onboarding.complete.quick-guide.deals.description')
            </p>
        </div>

        <div style="border-left: 4px solid #F59E0B;padding-left: 16px;margin-bottom: 12px;">
            <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                <strong style="color: #121A26;">@lang('admin::app.emails.onboarding.complete.quick-guide.activities.title')</strong><br>
                @lang('admin::app.emails.onboarding.complete.quick-guide.activities.description')
            </p>
        </div>

        <div style="border-left: 4px solid #8B5CF6;padding-left: 16px;">
            <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
                <strong style="color: #121A26;">@lang('admin::app.emails.onboarding.complete.quick-guide.reports.title')</strong><br>
                @lang('admin::app.emails.onboarding.complete.quick-guide.reports.description')
            </p>
        </div>
    </div>

    <!-- Resources Section -->
    <div style="margin-bottom: 34px;">
        <h2 style="font-weight: 600;font-size: 18px;color: #121A26;line-height: 24px;margin-bottom: 16px;">
            @lang('admin::app.emails.onboarding.complete.resources.title')
        </h2>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 12px;">
            @lang('admin::app.emails.onboarding.complete.resources.intro')
        </p>

        <table style="width: 100%;">
            <tr>
                <td style="padding: 8px 0;">
                    <a href="{{ config('app.url') }}/help/documentation" style="font-size: 16px;color: #3B82F6;text-decoration: none;line-height: 24px;">
                        📚 @lang('admin::app.emails.onboarding.complete.resources.documentation')
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <a href="{{ config('app.url') }}/help/video-tutorials" style="font-size: 16px;color: #3B82F6;text-decoration: none;line-height: 24px;">
                        🎥 @lang('admin::app.emails.onboarding.complete.resources.video-tutorials')
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <a href="{{ config('app.url') }}/help/support" style="font-size: 16px;color: #3B82F6;text-decoration: none;line-height: 24px;">
                        💬 @lang('admin::app.emails.onboarding.complete.resources.support')
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <a href="{{ config('app.url') }}/help/community" style="font-size: 16px;color: #3B82F6;text-decoration: none;line-height: 24px;">
                        👥 @lang('admin::app.emails.onboarding.complete.resources.community')
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <!-- Call to Action Button -->
    <div style="margin-bottom: 34px;text-align: center;">
        <a href="{{ config('app.url') }}/admin/dashboard"
           style="display: inline-block;background-color: #3B82F6;color: #FFFFFF;padding: 12px 32px;text-decoration: none;border-radius: 8px;font-weight: 600;font-size: 16px;">
            @lang('admin::app.emails.onboarding.complete.cta-button')
        </a>
    </div>

    <!-- Support Section -->
    <div style="background-color: #F9FAFB;border-radius: 8px;padding: 20px;margin-bottom: 24px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;text-align: center;">
            @lang('admin::app.emails.onboarding.complete.support-message')
        </p>
    </div>

    <!-- Closing Message -->
    <div>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('admin::app.emails.onboarding.complete.closing')
        </p>
    </div>
@endcomponent
