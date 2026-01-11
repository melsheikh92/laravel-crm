@component('admin::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('marketplace::app.notifications.new-review.dear', ['username' => $user_name]), 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 16px;">
            @lang('marketplace::app.notifications.new-review.body', [
                'reviewer' => $reviewer_name,
                'extension' => $extension_name
            ])
        </p>

        <div style="background-color: #F5F5F5;padding: 20px;border-radius: 8px;margin-bottom: 16px;">
            <div style="margin-bottom: 12px;">
                <span style="font-size: 14px;color: #384860;">
                    <strong>@lang('marketplace::app.notifications.new-review.rating'):</strong>
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $rating)
                            ⭐
                        @else
                            ☆
                        @endif
                    @endfor
                    ({{ $rating }}/5)
                </span>
            </div>

            @if($review_title)
                <p style="font-size: 16px;color: #121A26;font-weight: 600;margin-bottom: 8px;">
                    {{ $review_title }}
                </p>
            @endif

            @if($review_text)
                <p style="font-size: 14px;color: #384860;line-height: 20px;">
                    {{ $review_text }}
                </p>
            @endif
        </div>

        <p style="text-align: center;margin-top: 30px;">
            <a
                href="{{ $extension_url }}"
                style="display: inline-block;padding: 12px 24px;background-color: #0E90D9;color: #FFFFFF;text-decoration: none;border-radius: 6px;font-weight: 600;font-size: 16px;"
            >
                @lang('marketplace::app.notifications.new-review.view-review')
            </a>
        </p>
    </div>
@endcomponent
