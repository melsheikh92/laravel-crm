<x-admin::layouts>
    <x-slot:title>
        {{ $extension->name }}
    </x-slot>

    <div class="flex gap-4 max-lg:flex-wrap">
        <!-- Left Panel -->
        {!! view_render_event('admin.marketplace.extensions.show.left.before', ['extension' => $extension]) !!}

        <div class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Extension Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <!-- Breadcrumbs -->
                <div class="flex items-center justify-between">
                    <x-admin::breadcrumbs
                        name="marketplace.extensions.show"
                        :entity="$extension"
                    />
                </div>

                {!! view_render_event('admin.marketplace.extensions.show.left.logo.before', ['extension' => $extension]) !!}

                <!-- Extension Logo -->
                @if($extension->logo)
                    <div class="flex items-center justify-center p-4">
                        <img
                            src="{{ Storage::url($extension->logo) }}"
                            alt="{{ $extension->name }}"
                            class="max-h-32 max-w-full rounded-lg"
                        />
                    </div>
                @endif

                {!! view_render_event('admin.marketplace.extensions.show.left.logo.after', ['extension' => $extension]) !!}

                {!! view_render_event('admin.marketplace.extensions.show.left.title.before', ['extension' => $extension]) !!}

                <!-- Title -->
                <h3 class="text-lg font-bold dark:text-white">
                    {{ $extension->name }}
                </h3>

                {!! view_render_event('admin.marketplace.extensions.show.left.title.after', ['extension' => $extension]) !!}

                <!-- Description -->
                @if($extension->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $extension->description }}
                    </p>
                @endif

                {!! view_render_event('admin.marketplace.extensions.show.left.actions.before', ['extension' => $extension]) !!}

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2 pt-2">
                    @if (bouncer()->hasPermission('marketplace.extensions.edit'))
                        <!-- Enable/Disable Button -->
                        @if($extension->status === 'approved')
                            <button
                                type="button"
                                class="secondary-button"
                                @click="$refs.extensionUpdateModal.toggle('{{ route('admin.marketplace.extensions.disable', $extension->id) }}', 'post', '{{ trans('marketplace::app.admin.extensions.show.disable-confirm') }}')"
                            >
                                @lang('marketplace::app.admin.extensions.show.disable-btn')
                            </button>
                        @else
                            <button
                                type="button"
                                class="primary-button"
                                @click="$refs.extensionUpdateModal.toggle('{{ route('admin.marketplace.extensions.enable', $extension->id) }}', 'post', '{{ trans('marketplace::app.admin.extensions.show.enable-confirm') }}')"
                            >
                                @lang('marketplace::app.admin.extensions.show.enable-btn')
                            </button>
                        @endif

                        <!-- Feature/Unfeature Button -->
                        @if($extension->featured)
                            <button
                                type="button"
                                class="secondary-button"
                                @click="$refs.extensionUpdateModal.toggle('{{ route('admin.marketplace.extensions.unfeature', $extension->id) }}', 'post', '{{ trans('marketplace::app.admin.extensions.show.unfeature-confirm') }}')"
                            >
                                @lang('marketplace::app.admin.extensions.show.unfeature-btn')
                            </button>
                        @else
                            <button
                                type="button"
                                class="secondary-button"
                                @click="$refs.extensionUpdateModal.toggle('{{ route('admin.marketplace.extensions.feature', $extension->id) }}', 'post', '{{ trans('marketplace::app.admin.extensions.show.feature-confirm') }}')"
                            >
                                @lang('marketplace::app.admin.extensions.show.feature-btn')
                            </button>
                        @endif

                        <!-- Edit Button -->
                        <a
                            href="{{ route('admin.marketplace.extensions.edit', $extension->id) }}"
                            class="secondary-button"
                        >
                            @lang('marketplace::app.admin.extensions.show.edit-btn')
                        </a>
                    @endif

                    @if (bouncer()->hasPermission('marketplace.extensions.delete'))
                        <!-- Delete Button -->
                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.extensionUpdateModal.toggle('{{ route('admin.marketplace.extensions.destroy', $extension->id) }}', 'delete', '{{ trans('marketplace::app.admin.extensions.show.delete-confirm') }}')"
                        >
                            @lang('marketplace::app.admin.extensions.show.delete-btn')
                        </button>
                    @endif
                </div>

                {!! view_render_event('admin.marketplace.extensions.show.left.actions.after', ['extension' => $extension]) !!}
            </div>

            <!-- General Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.extensions.show.general-info')
                </h4>

                <!-- Type -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.type')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ ucfirst($extension->type) }}
                    </span>
                </div>

                <!-- Category -->
                @if($extension->category)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.extensions.show.category')
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $extension->category->name }}
                        </span>
                    </div>
                @endif

                <!-- Author -->
                @if($extension->author)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.extensions.show.author')
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $extension->author->name }}
                        </span>
                    </div>
                @endif

                <!-- Price -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.price')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        @if($extension->price > 0)
                            {{ core()->formatBasePrice($extension->price) }}
                        @else
                            @lang('marketplace::app.admin.extensions.show.free')
                        @endif
                    </span>
                </div>

                <!-- Status -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.status')
                    </span>
                    <span class="rounded px-2 py-1 text-xs font-medium
                        @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @elseif($extension->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @elseif($extension->status === 'disabled') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                        @endif
                    ">
                        {{ ucfirst($extension->status) }}
                    </span>
                </div>

                <!-- Featured -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.featured')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        @if($extension->featured)
                            <span class="text-yellow-500">★ @lang('marketplace::app.admin.extensions.show.yes')</span>
                        @else
                            @lang('marketplace::app.admin.extensions.show.no')
                        @endif
                    </span>
                </div>
            </div>

            <!-- Statistics -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.extensions.show.statistics')
                </h4>

                <!-- Downloads -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.downloads')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ number_format($extension->downloads_count) }}
                    </span>
                </div>

                <!-- Average Rating -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.rating')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ number_format($extension->average_rating, 1) }} / 5.0
                    </span>
                </div>

                <!-- Reviews Count -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.extensions.show.reviews-count')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $extension->reviews_count ?? $extension->reviews->count() }}
                    </span>
                </div>
            </div>

            <!-- Links -->
            <div class="flex w-full flex-col gap-2 p-4">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.extensions.show.links')
                </h4>

                @if($extension->documentation_url)
                    <a
                        href="{{ $extension->documentation_url }}"
                        target="_blank"
                        class="text-sm text-brandColor hover:underline"
                    >
                        @lang('marketplace::app.admin.extensions.show.documentation')
                    </a>
                @endif

                @if($extension->demo_url)
                    <a
                        href="{{ $extension->demo_url }}"
                        target="_blank"
                        class="text-sm text-brandColor hover:underline"
                    >
                        @lang('marketplace::app.admin.extensions.show.demo')
                    </a>
                @endif

                @if($extension->repository_url)
                    <a
                        href="{{ $extension->repository_url }}"
                        target="_blank"
                        class="text-sm text-brandColor hover:underline"
                    >
                        @lang('marketplace::app.admin.extensions.show.repository')
                    </a>
                @endif

                @if($extension->support_email)
                    <a
                        href="mailto:{{ $extension->support_email }}"
                        class="text-sm text-brandColor hover:underline"
                    >
                        @lang('marketplace::app.admin.extensions.show.support')
                    </a>
                @endif
            </div>
        </div>

        {!! view_render_event('admin.marketplace.extensions.show.left.after', ['extension' => $extension]) !!}

        {!! view_render_event('admin.marketplace.extensions.show.right.before', ['extension' => $extension]) !!}

        <!-- Right Panel -->
        <div class="flex w-full flex-col gap-4 rounded-lg">
            {!! view_render_event('admin.marketplace.extensions.show.right.description.before', ['extension' => $extension]) !!}

            <!-- Long Description -->
            @if($extension->long_description)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.extensions.show.description')
                    </h3>
                    <div class="prose prose-sm max-w-none dark:prose-invert text-gray-600 dark:text-gray-400">
                        {!! nl2br(e($extension->long_description)) !!}
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.extensions.show.right.description.after', ['extension' => $extension]) !!}

            {!! view_render_event('admin.marketplace.extensions.show.right.versions.before', ['extension' => $extension]) !!}

            <!-- Versions -->
            @if($extension->versions && $extension->versions->count() > 0)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.extensions.show.versions')
                    </h3>
                    <div class="space-y-3">
                        @foreach($extension->versions->sortByDesc('created_at')->take(5) as $version)
                            <div class="flex items-start justify-between border-b border-gray-200 pb-3 last:border-b-0 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $version->version }}
                                    </p>
                                    @if($version->changelog)
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ Str::limit($version->changelog, 100) }}
                                        </p>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $version->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.extensions.show.right.versions.after', ['extension' => $extension]) !!}

            {!! view_render_event('admin.marketplace.extensions.show.right.reviews.before', ['extension' => $extension]) !!}

            <!-- Recent Reviews -->
            @if($extension->reviews && $extension->reviews->count() > 0)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.extensions.show.recent-reviews')
                    </h3>
                    <div class="space-y-4">
                        @foreach($extension->reviews->sortByDesc('created_at')->take(5) as $review)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0 dark:border-gray-800">
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $review->user->name ?? 'Anonymous' }}
                                        </span>
                                        <span class="text-yellow-500">
                                            @for($i = 0; $i < $review->rating; $i++)★@endfor
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $review->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                                @if($review->title)
                                    <p class="mb-1 text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $review->title }}
                                    </p>
                                @endif
                                @if($review->comment)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ Str::limit($review->comment, 200) }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.extensions.show.right.reviews.after', ['extension' => $extension]) !!}
        </div>

        {!! view_render_event('admin.marketplace.extensions.show.right.after', ['extension' => $extension]) !!}
    </div>

    <!-- Confirmation Modal for Actions -->
    <x-admin::form
        v-slot="{ meta, errors, handleSubmit }"
        as="div"
    >
        <form
            @submit="handleSubmit($event, updateExtension)"
            ref="extensionUpdateModal"
        >
            <x-admin::modal ref="extensionUpdateModal">
                <x-slot:header>
                    @lang('marketplace::app.admin.extensions.show.confirm-action')
                </x-slot>

                <x-slot:content>
                    <p class="text-gray-600 dark:text-gray-400">
                        @{{ confirmMessage }}
                    </p>
                </x-slot>

                <x-slot:footer>
                    <div class="flex items-center gap-x-2.5">
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('marketplace::app.admin.extensions.show.confirm')
                        </button>

                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.extensionUpdateModal.close()"
                        >
                            @lang('marketplace::app.admin.extensions.show.cancel')
                        </button>
                    </div>
                </x-slot>
            </x-admin::modal>
        </form>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="module">
            app.component('x-admin-modal', {
                template: '#x-admin-modal-template',

                data() {
                    return {
                        isOpen: false,
                        actionUrl: '',
                        actionMethod: 'post',
                        confirmMessage: '',
                    };
                },

                methods: {
                    toggle(url, method, message) {
                        this.actionUrl = url;
                        this.actionMethod = method.toLowerCase();
                        this.confirmMessage = message;
                        this.isOpen = true;
                    },

                    close() {
                        this.isOpen = false;
                    },

                    async updateExtension(params, { resetForm, setErrors }) {
                        try {
                            const response = await this.$axios({
                                method: this.actionMethod,
                                url: this.actionUrl,
                            });

                            if (response.data.message) {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            }

                            this.close();

                            // Reload page to show updated data
                            window.location.reload();
                        } catch (error) {
                            if (error.response?.data?.message) {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            }

                            setErrors(error.response?.data?.errors || {});
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
