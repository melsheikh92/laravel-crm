<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.tickets.edit.title')
        </x-slot>

        <x-admin::form :action="route('admin.support.tickets.update', $ticket->id)" method="PUT">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="support.tickets.edit" />
                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.support.tickets.edit.title') - {{ $ticket->ticket_number }}
                    </div>
                </div>

                <button type="submit" class="primary-button">
                    @lang('admin::app.support.tickets.edit.update-btn')
                </button>
            </div>

            <div class="mt-3.5">
                <div
                    class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    @include('admin::support.tickets.create')
                </div>
            </div>
        </x-admin::form>
</x-admin::layouts>