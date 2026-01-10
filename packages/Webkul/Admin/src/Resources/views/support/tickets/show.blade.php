<x-admin::layouts>
    <x-slot:title>
        {{ $ticket->ticket_number }} - {{ $ticket->subject }}
        </x-slot>

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.tickets.show" />
                <div class="text-xl font-bold dark:text-white">
                    {{ $ticket->ticket_number }} - {{ $ticket->subject }}
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.support.tickets.edit', $ticket->id) }}" class="secondary-button">
                    Edit
                </a>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Ticket Details -->
            <div class="lg:col-span-2">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold dark:text-white mb-4">Conversation</h3>

                    <!-- Messages -->
                    <div class="space-y-4">
                        @foreach($ticket->messages as $message)
                            <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold dark:text-white">{{ $message->user->name }}</span>
                                    <span class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-gray-700 dark:text-gray-300">{{ $message->message }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Add Message Form -->
                    <div class="mt-6">
                        <h4 class="font-semibold dark:text-white mb-2">Add Message</h4>
                        <x-admin::form :action="route('admin.support.tickets.add_message', $ticket->id)" method="POST">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.control type="textarea" name="message" rows="4"
                                    placeholder="Type your message..." />
                            </x-admin::form.control-group>
                            <button type="submit" class="primary-button mt-2">Send</button>
                        </x-admin::form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Status -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Status</h3>
                    <span class="badge badge-{{ $ticket->getStatusColor() }}">{{ ucfirst($ticket->status) }}</span>
                </div>

                <!-- Priority -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Priority</h3>
                    <span class="badge badge-{{ $ticket->getPriorityColor() }}">{{ ucfirst($ticket->priority) }}</span>
                </div>

                <!-- Assigned To -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Assigned To</h3>
                    <p class="dark:text-gray-300">{{ $ticket->assignedTo->name ?? 'Unassigned' }}</p>
                </div>

                <!-- Customer -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Customer</h3>
                    <p class="dark:text-gray-300">{{ $ticket->customer->name }}</p>
                </div>
            </div>
        </div>
</x-admin::layouts>