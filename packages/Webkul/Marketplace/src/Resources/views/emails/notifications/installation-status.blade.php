@component('admin::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('marketplace::app.notifications.installation-status.dear', ['username' => $user_name]), 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 16px;">
            @if($status === 'active')
                @lang('marketplace::app.notifications.installation-status.success-body', [
                    'extension' => $extension_name,
                    'version' => $version
                ])
            @elseif($status === 'failed')
                @lang('marketplace::app.notifications.installation-status.failed-body', [
                    'extension' => $extension_name,
                    'version' => $version
                ])
            @else
                @lang('marketplace::app.notifications.installation-status.status-changed-body', [
                    'extension' => $extension_name,
                    'status' => $status
                ])
            @endif
        </p>

        @if($message)
            <div style="background-color: {{ $status === 'failed' ? '#FEF2F2' : '#F0F9FF' }};border-left: 4px solid {{ $status === 'failed' ? '#DC2626' : '#0E90D9' }};padding: 16px;margin-bottom: 16px;">
                <p style="font-size: 14px;color: #384860;">
                    {{ $message }}
                </p>
            </div>
        @endif

        <p style="text-align: center;margin-top: 30px;">
            <a
                href="{{ $extension_url }}"
                style="display: inline-block;padding: 12px 24px;background-color: #0E90D9;color: #FFFFFF;text-decoration: none;border-radius: 6px;font-weight: 600;font-size: 16px;"
            >
                @lang('marketplace::app.notifications.installation-status.view-extensions')
            </a>
        </p>
    </div>
@endcomponent
