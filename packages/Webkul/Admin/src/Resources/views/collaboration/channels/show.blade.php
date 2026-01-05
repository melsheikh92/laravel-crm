@php
    $_title = trans('admin::app.collaboration.channels.show.title') !== 'admin::app.collaboration.channels.show.title' 
        ? trans('admin::app.collaboration.channels.show.title', ['name' => $channel->name]) 
        : 'Channel: ' . $channel->name;
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $_title }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap mb-6">
        <div class="flex gap-2.5 items-center">
            <a href="{{ route('admin.collaboration.channels.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                <i class="icon-arrow-left text-2xl"></i>
            </a>
            <p class="text-2xl dark:text-white">{{ $channel->name }}</p>
        </div>
    </div>

    <div class="flex gap-6 max-xl:flex-wrap" style="height: calc(100vh - 200px);">
        <!-- Main Content - Chat Messages -->
        <div class="flex flex-col flex-1 max-xl:flex-auto min-h-0">
            <div class="flex flex-col h-full bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ trans('admin::app.collaboration.channels.show.messages') !== 'admin::app.collaboration.channels.show.messages' ? trans('admin::app.collaboration.channels.show.messages') : 'Messages' }}
                    </h3>
                </div>

                <!-- Messages Container - Scrollable -->
                <div id="messages-container" class="flex-1 overflow-y-auto px-6 py-4" style="min-height: 0;">
                    @if($channel->messages && $channel->messages->count() > 0)
                        <div class="space-y-6">
                            @foreach($channel->messages as $message)
                                @php
                                    $isCurrentUser = $message->user_id === auth()->guard('user')->id();
                                @endphp
                                <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                                    <div 
                                        class="max-w-[70%] rounded-lg px-4 py-2 {{ $isCurrentUser ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' }}"
                                        @if($isCurrentUser) style="background-color: #2563eb !important; color: #ffffff !important;" @endif
                                    >
                                        <div class="text-xs mb-1 {{ $isCurrentUser ? 'text-blue-100' : 'text-gray-600 dark:text-gray-400' }}" @if($isCurrentUser) style="color: #dbeafe !important;" @endif>
                                            {{ $message->user->name ?? 'Unknown' }} - {{ $message->created_at->format('M d, Y H:i') }}
                                        </div>
                                        <div class="text-sm whitespace-pre-wrap {{ $isCurrentUser ? 'text-white' : 'text-gray-900 dark:text-white' }}" @if($isCurrentUser) style="color: #ffffff !important;" @endif>{{ $message->content }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <p>{{ trans('admin::app.collaboration.channels.show.no-messages') !== 'admin::app.collaboration.channels.show.no-messages' ? trans('admin::app.collaboration.channels.show.no-messages') : 'No messages yet. Start the conversation!' }}</p>
                        </div>
                    @endif
                </div>

                <!-- Message Input Form -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
                    <form id="message-form" method="POST" action="javascript:void(0);" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="channel_id" value="{{ $channel->id }}">
                        <input 
                            type="text" 
                            name="content" 
                            id="message-content"
                            placeholder="{{ trans('admin::app.collaboration.channels.show.message-placeholder') !== 'admin::app.collaboration.channels.show.message-placeholder' ? trans('admin::app.collaboration.channels.show.message-placeholder') : 'Type your message...' }}"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            required
                        >
                        <button 
                            type="button" 
                            id="send-message-button"
                            class="primary-button"
                            onclick="window.sendChannelMessage && window.sendChannelMessage(); return false;"
                        >
                            {{ trans('admin::app.collaboration.channels.show.send') !== 'admin::app.collaboration.channels.show.send' ? trans('admin::app.collaboration.channels.show.send') : 'Send' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar - Channel Info -->
        <div class="flex flex-col w-[394px] max-w-full max-xl:w-full">
            <div class="flex flex-col rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Channel Information Header -->
                <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ trans('admin::app.collaboration.channels.show.channel-info') !== 'admin::app.collaboration.channels.show.channel-info' ? trans('admin::app.collaboration.channels.show.channel-info') : 'Channel Information' }}
                    </h3>
                </div>

                <!-- Channel Details -->
                <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex flex-col gap-0.5">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ trans('admin::app.collaboration.channels.show.name') !== 'admin::app.collaboration.channels.show.name' ? trans('admin::app.collaboration.channels.show.name') : 'Name' }}
                        </label>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $channel->name }}</p>
                    </div>

                    @if($channel->description)
                    <div class="flex flex-col gap-0.5 mt-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ trans('admin::app.collaboration.channels.show.description') !== 'admin::app.collaboration.channels.show.description' ? trans('admin::app.collaboration.channels.show.description') : 'Description' }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $channel->description }}</p>
                    </div>
                    @endif

                    <div class="flex flex-col gap-0.5 mt-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ trans('admin::app.collaboration.channels.show.type') !== 'admin::app.collaboration.channels.show.type' ? trans('admin::app.collaboration.channels.show.type') : 'Type' }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white capitalize">{{ $channel->type }}</p>
                    </div>

                    <div class="flex flex-col gap-0.5 mt-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ trans('admin::app.collaboration.channels.show.created-by') !== 'admin::app.collaboration.channels.show.created-by' ? trans('admin::app.collaboration.channels.show.created-by') : 'Created By' }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $channel->creator->name ?? 'Unknown' }}</p>
                    </div>

                    <div class="flex flex-col gap-0.5 mt-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ trans('admin::app.collaboration.channels.show.created-at') !== 'admin::app.collaboration.channels.show.created-at' ? trans('admin::app.collaboration.channels.show.created-at') : 'Created At' }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $channel->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <!-- Members Section -->
                @if($channel->members && $channel->members->count() > 0)
                <div class="flex w-full flex-col gap-2 p-4">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                        {{ trans('admin::app.collaboration.channels.show.members') !== 'admin::app.collaboration.channels.show.members' ? trans('admin::app.collaboration.channels.show.members') : 'Members' }} ({{ $channel->members->count() }})
                    </label>
                    <div class="flex flex-col gap-2 mt-1">
                        @foreach($channel->members as $member)
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brandColor text-sm font-medium text-white">
                                    {{ substr($member->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $member->user->name ?? 'Unknown' }}
                                    </p>
                                    @if($member->role === 'admin')
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Admin</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('load', function() {
            console.log('Page loaded, initializing message form handler');
            
            const messageForm = document.getElementById('message-form');
            const contentInput = document.getElementById('message-content');
            const messagesContainer = document.getElementById('messages-container');
            const sendButton = document.getElementById('send-message-button');
            
            if (!messageForm) {
                console.error('Message form not found!');
                return;
            }
            
            if (!sendButton) {
                console.error('Send button not found!');
                return;
            }

            console.log('Message form and button found');

            // Auto-scroll to bottom of messages on load
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Handle send button click directly
            window.sendChannelMessage = async function() {
                console.log('Send button clicked - sendChannelMessage function called');
                
                // Re-get elements in case DOM changed
                const input = document.getElementById('message-content');
                const form = document.getElementById('message-form');
                const button = document.getElementById('send-message-button');
                
                if (!input || !form || !button) {
                    console.error('Required elements not found');
                    return;
                }
                
                const content = input.value.trim();
                console.log('Content to send:', content, 'Input value:', input.value);
                
                if (!content) {
                    console.log('No content to send');
                    return;
                }

                const formData = new FormData(form);
                const originalButtonText = button.textContent || 'Send';
                
                // Disable form while sending
                button.disabled = true;
                button.textContent = 'Sending...';
                input.disabled = true;
                
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const channelId = parseInt(formData.get('channel_id'));
                    
                    console.log('Sending message:', { channel_id: channelId, content: content });
                    
                    const requestBody = {
                        channel_id: channelId,
                        content: content,
                    };
                    
                    const response = await fetch("{{ route('admin.collaboration.chat.send') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || formData.get('_token'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(requestBody)
                    });

                    console.log('Response status:', response.status, response.statusText);

                    let data;
                    try {
                        data = await response.json();
                        console.log('Response data:', data);
                    } catch (jsonError) {
                        const text = await response.text();
                        console.error('Failed to parse JSON response:', text);
                        throw new Error('Invalid response from server: ' + text.substring(0, 100));
                    }

                    if (response.ok && data.data) {
                        console.log('Message sent successfully');
                        // Clear input
                        input.value = '';
                        
                        // Reload page to show new message - use href for full refresh
                        window.location.href = "{{ route('admin.collaboration.channels.show', $channel->id) }}";
                    } else {
                        const errorMessage = data.message || data.error || 'Failed to send message';
                        console.error('Error response:', errorMessage);
                        alert(errorMessage);
                        // Re-enable form on error
                        button.disabled = false;
                        button.textContent = originalButtonText;
                        input.disabled = false;
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Error: ' + (error.message || 'An error occurred while sending the message'));
                    // Re-enable form on error
                    button.disabled = false;
                    button.textContent = originalButtonText;
                    input.disabled = false;
                }
            }

            // Also attach event listener as backup (inline onclick should work)
            sendButton.addEventListener('click', function(e) {
                console.log('Send button clicked - event listener fired (backup)');
                e.preventDefault();
                e.stopPropagation();
                if (window.sendChannelMessage) {
                    window.sendChannelMessage();
                }
            });
            
            // Also handle Enter key in input
            contentInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    console.log('Enter key pressed in input');
                    if (window.sendChannelMessage) {
                        window.sendChannelMessage();
                    }
                }
            });
            
            console.log('Message form handler initialized successfully');
        });
    </script>
    @endpush
</x-admin::layouts>

