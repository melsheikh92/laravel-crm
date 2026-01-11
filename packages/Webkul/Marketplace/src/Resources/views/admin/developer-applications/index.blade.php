<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.developer-applications.index.title')
        </x-slot>

        <div class="flex flex-col gap-4">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <div class="flex cursor-pointer items-center">
                        <x-admin::breadcrumbs name="marketplace.developer-applications" />
                    </div>

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.developer-applications.index.title')
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Add filters or actions here if needed -->
            </div>

            <!-- Pending Applications Section -->
            <div class="flex flex-col gap-4">
                <div class="text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.admin.developer-applications.index.pending-applications')
                </div>

                @if($pendingApplications->isEmpty())
                    <div class="grid justify-center p-4">
                        <p class="text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.developer-applications.index.no-pending')
                        </p>
                    </div>
                @else
                    <x-admin::table>
                        <x-admin::table.thead>
                            <x-admin::table.thead.tr>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.id')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.name')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.email')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.registered-at')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.actions')</x-admin::table.th>
                            </x-admin::table.thead.tr>
                        </x-admin::table.thead>

                        <x-admin::table.tbody>
                            @foreach($pendingApplications as $application)
                                <x-admin::table.tbody.tr>
                                    <x-admin::table.td>{{ $application->id }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $application->name }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $application->email }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $application->developer_registered_at }}</x-admin::table.td>
                                    <x-admin::table.td class="action">
                                        <a href="{{ route('admin.marketplace.developer-applications.show', $application->id) }}"
                                            class="icon-view text-2xl"
                                            title="@lang('marketplace::app.admin.developer-applications.index.view')"></a>
                                    </x-admin::table.td>
                                </x-admin::table.tbody.tr>
                            @endforeach
                        </x-admin::table.tbody>
                    </x-admin::table>

                    {{ $pendingApplications->links() }}
                @endif
            </div>

            <!-- Approved Developers Section -->
            <div class="flex flex-col gap-4 mt-8">
                <div class="text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.admin.developer-applications.index.approved-developers')
                </div>

                @if($approvedDevelopers->isEmpty())
                    <div class="grid justify-center p-4">
                        <p class="text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.developer-applications.index.no-approved')
                        </p>
                    </div>
                @else
                    <x-admin::table>
                        <x-admin::table.thead>
                            <x-admin::table.thead.tr>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.id')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.name')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.email')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.approved-at')</x-admin::table.th>
                                <x-admin::table.th>@lang('marketplace::app.admin.developer-applications.index.actions')</x-admin::table.th>
                            </x-admin::table.thead.tr>
                        </x-admin::table.thead>

                        <x-admin::table.tbody>
                            @foreach($approvedDevelopers as $developer)
                                <x-admin::table.tbody.tr>
                                    <x-admin::table.td>{{ $developer->id }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $developer->name }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $developer->email }}</x-admin::table.td>
                                    <x-admin::table.td>{{ $developer->developer_approved_at }}</x-admin::table.td>
                                    <x-admin::table.td class="action">
                                        <a href="{{ route('admin.marketplace.developer-applications.show', $developer->id) }}"
                                            class="icon-view text-2xl"
                                            title="@lang('marketplace::app.admin.developer-applications.index.view')"></a>
                                    </x-admin::table.td>
                                </x-admin::table.tbody.tr>
                            @endforeach
                        </x-admin::table.tbody>
                    </x-admin::table>
                    {{ $approvedDevelopers->links() }}
                @endif
            </div>

        </div>
</x-admin::layouts>