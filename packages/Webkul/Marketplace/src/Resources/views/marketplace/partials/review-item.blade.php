<div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-start justify-between">
        <!-- User Info -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                <span class="icon-user text-blue-600 dark:text-blue-400"></span>
            </div>
            <div>
                <h4 class="font-medium text-gray-900 dark:text-white">
                    {{ $review->user->name }}
                    @if($review->verified_purchase)
                        <span class="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                            <span class="icon-checkmark text-xs"></span>
                            @lang('marketplace::app.marketplace.detail.verified-purchase')
                        </span>
                    @endif
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $review->created_at->format('F d, Y') }}
                </p>
            </div>
        </div>

        <!-- Rating -->
        <div class="flex gap-1">
            @for($i = 1; $i <= 5; $i++)
                <span class="icon-star {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }}"></span>
            @endfor
        </div>
    </div>

    <!-- Review Title -->
    @if($review->title)
        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">
            {{ $review->title }}
        </h3>
    @endif

    <!-- Review Content -->
    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
        {{ $review->comment }}
    </p>

    <!-- Review Meta -->
    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
        <!-- Helpful Count -->
        @if($review->helpful_count > 0)
            <span class="text-gray-600 dark:text-gray-400">
                {{ $review->helpful_count }} {{ Str::plural('person', $review->helpful_count) }} @lang('marketplace::app.marketplace.detail.found-helpful')
            </span>
        @endif

        <!-- Actions -->
        @auth
            @if($review->user_id !== auth()->id())
                <div class="flex gap-3">
                    <form action="{{ route('marketplace.reviews.helpful', $review->id) }}" method="POST" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="text-blue-600 hover:underline dark:text-blue-400"
                        >
                            @lang('marketplace::app.marketplace.detail.mark-helpful')
                        </button>
                    </form>

                    <button
                        type="button"
                        class="text-red-600 hover:underline dark:text-red-400"
                        @click="$refs.reportModal{{ $review->id }}.open()"
                    >
                        @lang('marketplace::app.marketplace.detail.report')
                    </button>
                </div>
            @else
                <a
                    href="{{ route('marketplace.reviews.show', $review->id) }}"
                    class="text-blue-600 hover:underline dark:text-blue-400"
                >
                    @lang('marketplace::app.marketplace.detail.edit-review')
                </a>
            @endif
        @endauth
    </div>

    <!-- Developer Response -->
    @if($review->developer_response)
        <div class="mt-4 border-l-4 border-blue-500 bg-white p-3 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                @lang('marketplace::app.marketplace.detail.developer-response')
            </p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                {{ $review->developer_response }}
            </p>
            @if($review->developer_response_at)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $review->developer_response_at->format('F d, Y') }}
                </p>
            @endif
        </div>
    @endif
</div>
