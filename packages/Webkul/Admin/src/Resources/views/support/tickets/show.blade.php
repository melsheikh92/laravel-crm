<x-admin::layouts>
    <x-slot:title>
        {{ $ticket->ticket_number }} - {{ $ticket->subject }}
        </x-slot>

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.tickets.view" :entity="$ticket" />
                <div class="text-xl font-bold dark:text-white">
                    {{ $ticket->ticket_number }} - {{ $ticket->subject }}
                </div>
            </div>
        </div>

        <div class="block w-full">
            <!-- Ticket Info Cards -->
            <div class="mt-4 grid grid-cols-4 gap-4">
                <!-- Status -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Status</h3>
                    <form action="{{ route('admin.support.tickets.update', $ticket->id) }}" method="POST"
                        id="status-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" id="status-input">
                        <x-admin::form.control-group.control type="select" name="status_select" :value="$ticket->status"
                            onchange="document.getElementById('status-input').value=this.value; document.getElementById('status-form').submit()">
                            @foreach(['open', 'in_progress', 'waiting_customer', 'waiting_internal', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </form>
                </div>

                <!-- Priority -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Priority</h3>
                    <form action="{{ route('admin.support.tickets.update', $ticket->id) }}" method="POST"
                        id="priority-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="priority" id="priority-input">
                        <x-admin::form.control-group.control type="select" name="priority_select"
                            :value="$ticket->priority"
                            onchange="document.getElementById('priority-input').value=this.value; document.getElementById('priority-form').submit()">
                            @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </form>
                </div>

                <!-- Assigned To -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Assigned To</h3>
                    <form action="{{ route('admin.support.tickets.assign', $ticket->id) }}" method="POST"
                        id="assign-form">
                        @csrf
                        <input type="hidden" name="assigned_to" id="assigned-to-input">
                        <x-admin::form.control-group.control type="select" name="assigned_to_select"
                            :value="$ticket->assigned_to"
                            onchange="document.getElementById('assigned-to-input').value=this.value; document.getElementById('assign-form').submit()">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </form>
                </div>

                <!-- Customer -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold dark:text-white mb-2">Customer</h3>
                    <a href="{{ route('admin.contacts.persons.edit', $ticket->customer_id) }}"
                        class="text-blue-600 hover:underline font-medium break-words">
                        {{ $ticket->customer->name }}
                    </a>

                    @if(!empty($ticket->customer->emails))
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            @foreach($ticket->customer->emails as $email)
                                <div class="flex items-center gap-1">
                                    <span class="icon-mail text-gray-500"></span>
                                    <span>{{ $email['value'] }} ({{ $email['label'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($ticket->customer->contact_numbers))
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            @foreach($ticket->customer->contact_numbers as $phone)
                                <div class="flex items-center gap-1">
                                    <span class="icon-phone text-gray-500"></span>
                                    <span>{{ $phone['value'] }} ({{ $phone['label'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <!-- Ticket Details (Conversation) -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold dark:text-white mb-4">Conversation</h3>

                    <!-- Messages -->
                    <div class="space-y-4">
                        @foreach($ticket->messages as $message)
                            <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold dark:text-white">
                                        {{ $message->is_from_customer ? $ticket->customer->name : ($message->user->name ?? 'System') }}
                                    </span>
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
        </div>
</x-admin::layouts>