@component('admin::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('marketplace::app.notifications.submission-rejected.dear', ['username' => $user_name]), 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 16px;">
            @lang('marketplace::app.notifications.submission-rejected.body', [
                'extension' => $extension_name,
                'version' => $version
            ])
        </p>

        @if($review_notes)
            <div style="background-color: #FEF2F2;border-left: 4px solid #DC2626;padding: 16px;margin-bottom: 16px;">
                <p style="font-size: 14px;color: #384860;margin-bottom: 8px;">
                    <strong>@lang('marketplace::app.notifications.submission-rejected.review-notes'):</strong>
                </p>
                <p style="font-size: 14px;color: #384860;">
                    {{ $review_notes }}
                </p>
            </div>
        @endif

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('marketplace::app.notifications.submission-rejected.next-steps')
        </p>

        <p style="text-align: center;margin-top: 30px;">
            <a
                href="{{ $submission_url }}"
                style="display: inline-block;padding: 12px 24px;background-color: #0E90D9;color: #FFFFFF;text-decoration: none;border-radius: 6px;font-weight: 600;font-size: 16px;"
            >
                @lang('marketplace::app.notifications.submission-rejected.view-submission')
            </a>
        </p>
    </div>
@endcomponent
