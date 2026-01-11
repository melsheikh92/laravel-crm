<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.developer-applications.show.title')
        </x-slot>

        <div class="flex flex-col gap-4">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <div class="flex cursor-pointer items-center">
                        <x-admin::breadcrumbs name="marketplace.developer-applications.show"
                            entity="{{ $developer }}" />
                    </div>

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.developer-applications.show.title')
                    </div>
                </div>

                <div class="flex gap-x-2.5">
                    @if($developer->developer_status === 'pending')
                        <form action="{{ route('admin.marketplace.developer-applications.approve', $developer->id) }}"
                            method="POST"
                            onsubmit="return confirm('@lang('marketplace::app.admin.developer-applications.show.confirm-approve')')">
                            @csrf
                            <button type="submit" class="primary-button">
                                @lang('marketplace::app.admin.developer-applications.show.approve')
                            </button>
                        </form>

                        <form action="{{ route('admin.marketplace.developer-applications.reject', $developer->id) }}"
                            method="POST"
                            onsubmit="return confirm('@lang('marketplace::app.admin.developer-applications.show.confirm-reject')')">
                            @csrf
                            <button type="submit" class="secondary-button bg-red-600 text-white hover:bg-red-700">
                                @lang('marketplace::app.admin.developer-applications.show.reject')
                            </button>
                        </form>
                    @elseif($developer->developer_status === 'approved')
                        <form action="{{ route('admin.marketplace.developer-applications.suspend', $developer->id) }}"
                            method="POST"
                            onsubmit="return confirm('@lang('marketplace::app.admin.developer-applications.show.confirm-suspend')')">
                            @csrf
                            <button type="submit" class="secondary-button bg-red-600 text-white hover:bg-red-700">
                                @lang('marketplace::app.admin.developer-applications.show.suspend')
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.admin.developer-applications.show.details')
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium">@lang('marketplace::app.admin.developer-applications.show.name')</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $developer->name }}</p>
                    </div>
                    <div>
                        <p class="font-medium">@lang('marketplace::app.admin.developer-applications.show.email')</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $developer->email }}</p>
                    </div>
                    <div>
                        <p class="font-medium">@lang('marketplace::app.admin.developer-applications.show.company')</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $developer->developer_company ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="font-medium">@lang('marketplace::app.admin.developer-applications.show.website')</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $developer->developer_website ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="font-medium">@lang('marketplace::app.admin.developer-applications.show.bio')</p>
                    <p class="text-gray-600 dark:text-gray-400">{{ $developer->developer_bio ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
</x-admin::layouts>