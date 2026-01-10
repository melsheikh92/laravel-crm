<x-admin::layouts>
    <x-slot:title>
        {{ $campaign->name }} - Analytics
        </x-slot>

        <!-- Header -->
        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-x-2">
                    <a href="{{ route('admin.marketing.campaigns.index') }}"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Marketing</a>
                    <span class="text-gray-400">/</span>
                    <span class="font-semibold">{{ $campaign->name }}</span>
                </div>
                <div class="text-xl font-bold dark:text-white">
                    {{ $campaign->subject }}
                </div>
            </div>

            <div class="flex gap-2">
                @if($campaign->status == 'draft')
                    <a href="{{ route('admin.marketing.campaigns.edit', $campaign->id) }}" class="secondary-button">
                        Edit
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="mt-4 grid grid-cols-4 gap-4 max-xl:grid-cols-2 max-sm:grid-cols-1">
            <!-- Sent -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sent</div>
                <div class="mt-2 text-3xl font-bold dark:text-white">{{ $statistics['sent'] }}</div>
                <div class="text-xs text-gray-400">Out of {{ $statistics['total_recipients'] }} recipients</div>
            </div>

            <!-- Open Rate -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $statistics['open_rate'] }}%</div>
                <div class="text-xs text-gray-400">{{ $statistics['opened'] }} opened</div>
            </div>

            <!-- Click Rate -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ $statistics['click_rate'] }}%</div>
                <div class="text-xs text-gray-400">{{ $statistics['clicked'] }} clicked</div>
            </div>

            <!-- Failed/Bounced -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed / Bounced</div>
                <div class="mt-2 text-3xl font-bold text-red-600">{{ $statistics['failed'] + $statistics['bounced'] }}
                </div>
                <div class="text-xs text-gray-400">Delivery issues</div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Campaign Details -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold dark:text-white">Campaign Details</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd
                            class="text-sm font-medium dark:text-white capitalize badge badge-{{ $campaign->status == 'sent' || $campaign->status == 'completed' ? 'success' : 'warning' }}">
                            {{ $campaign->status }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Scheduled At</dt>
                        <dd class="text-sm font-medium dark:text-white">
                            {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y H:i') : 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Sender Name</dt>
                        <dd class="text-sm font-medium dark:text-white">{{ $campaign->sender_name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Sender Email</dt>
                        <dd class="text-sm font-medium dark:text-white">{{ $campaign->sender_email ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Timeline (Simple Table for now) -->
            <div
                class="col-span-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold dark:text-white">Open Activity (Timeline)</h3>

                @if(count($statistics['timeline']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-gray-500 dark:text-gray-400">Time</th>
                                    <th class="px-4 py-2 text-gray-500 dark:text-gray-400">Opens</th>
                                    <th class="px-4 py-2 text-gray-500 dark:text-gray-400">Visual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($statistics['timeline'] as $point)
                                    <tr>
                                        <td class="px-4 py-2 dark:text-white">
                                            {{ \Carbon\Carbon::parse($point->hour)->format('M d H:i') }}
                                        </td>
                                        <td class="px-4 py-2 dark:text-white">{{ $point->count }}</td>
                                        <td class="px-4 py-2">
                                            <div class="h-2 rounded-full bg-blue-100 dark:bg-blue-900 w-full"
                                                style="max-width: 200px;">
                                                <div class="h-2 rounded-full bg-blue-600"
                                                    style="width: {{ min(100, ($point->count * 10)) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No activity recorded yet.</p>
                @endif
            </div>
        </div>

</x-admin::layouts>