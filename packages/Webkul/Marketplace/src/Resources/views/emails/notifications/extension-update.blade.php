@component('admin::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('marketplace::app.notifications.extension-update.dear', ['username' => $user_name]), 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 16px;">
            @lang('marketplace::app.notifications.extension-update.body', ['extension' => $extension_name])
        </p>

        <div style="background-color: #F5F5F5;padding: 20px;border-radius: 8px;margin-bottom: 16px;">
            <p style="font-size: 14px;color: #384860;margin-bottom: 8px;">
                <strong>@lang('marketplace::app.notifications.extension-update.current-version'):</strong> {{ $current_version }}
            </p>
            <p style="font-size: 14px;color: #384860;margin-bottom: 8px;">
                <strong>@lang('marketplace::app.notifications.extension-update.new-version'):</strong> {{ $new_version }}
            </p>
            @if($changelog)
                <p style="font-size: 14px;color: #384860;margin-top: 16px;">
                    <strong>@lang('marketplace::app.notifications.extension-update.changelog'):</strong>
                </p>
                <p style="font-size: 14px;color: #384860;margin-top: 8px;">
                    {{ $changelog }}
                </p>
            @endif
        </div>

        <p style="text-align: center;margin-top: 30px;">
            <a
                href="{{ $extension_url }}"
                style="display: inline-block;padding: 12px 24px;background-color: #0E90D9;color: #FFFFFF;text-decoration: none;border-radius: 6px;font-weight: 600;font-size: 16px;"
            >
                @lang('marketplace::app.notifications.extension-update.view-update')
            </a>
        </p>
    </div>
@endcomponent
