<!-- AI Copilot Chat Widget -->
<v-copilot-widget></v-copilot-widget>

@pushOnce('scripts')
<script type="text/x-template" id="v-copilot-widget-template">
    <div>
        <!-- Floating Button -->
        <button
            v-if="!isOpen"
            @click="toggleWidget"
            class="fixed bottom-6 right-6 z-[10005] flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg transition-all hover:bg-blue-700 hover:shadow-xl"
            style="position: fixed !important; bottom: 1.5rem !important; right: 1.5rem !important; z-index: 10005 !important; display: flex !important; visibility: visible !important; opacity: 1 !important; background-color: #2563eb !important;"
            title="AI Copilot"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </button>

        <!-- Chat Widget -->
        <div
            v-if="isOpen"
            class="fixed bottom-6 right-6 z-[10005] flex h-[700px] w-[400px] flex-col rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            style="position: fixed !important; bottom: 1.5rem !important; right: 1.5rem !important; z-index: 10005 !important; display: flex !important; visibility: visible !important; opacity: 1 !important;"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white leading-tight">AI Copilot</h3>
                        <p class="text-[10px] text-blue-100 leading-tight">Your intelligent assistant</p>
                    </div>
                </div>
                <button
                    @click="toggleWidget"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-gray-700 shadow-sm transition-all hover:bg-gray-100 hover:scale-105 active:scale-95"
                    title="Close"
                    style="background-color: white !important; color: #374151 !important;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gradient-to-b from-gray-50 to-white dark:from-gray-950 dark:to-gray-900">
                <div v-if="messages.length === 0" class="flex flex-col items-center justify-start h-full text-center px-3 pt-6">
                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1.5">Welcome! 👋</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5 px-2 leading-relaxed">
                        Your intelligent CRM assistant is ready to help. Ask me anything about your CRM.
                    </p>
                    
                    <div class="w-full space-y-2.5">
                        <div class="rounded-xl bg-white dark:bg-gray-900 p-3 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-left">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-xs mb-0.5">Quick Answers</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Ask about leads, contacts, deals</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-white dark:bg-gray-900 p-3 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-left">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-xs mb-0.5">Analyze Data</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Get insights and recommendations</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-white dark:bg-gray-900 p-3 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-left">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-xs mb-0.5">Smart Suggestions</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Receive intelligent recommendations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-4 px-2">
                        💡 <strong>Try:</strong> "Show high-priority leads" or "Top deals this month"
                    </p>
                </div>

                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    class="flex"
                    :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[80%] rounded-lg px-4 py-2"
                        :class="message.role === 'user' 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white'"
                    >
                        <p class="text-sm whitespace-pre-wrap">@{{ message.content }}</p>
                    </div>
                </div>

                <div v-if="isLoading" class="flex justify-start">
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg px-4 py-2">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="border-t border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input
                        v-model="inputMessage"
                        type="text"
                        placeholder="Ask me anything..."
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400"
                        :disabled="isLoading"
                    />
                    <button
                        type="submit"
                        :disabled="!inputMessage.trim() || isLoading"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white transition-all hover:bg-blue-700 hover:shadow-md active:scale-95 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:bg-gray-300"
                        title="Send message"
                        style="background-color: #2563eb !important;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</script>

<script type="module">
    console.log('Registering v-copilot-widget component...', typeof app, app);
    if (typeof app !== 'undefined' && app.component) {
        app.component('v-copilot-widget', {
        template: '#v-copilot-widget-template',

        data() {
            return {
                isOpen: false,
                messages: [],
                inputMessage: '',
                isLoading: false,
                conversationId: null,
            };
        },

        mounted() {
            console.log('v-copilot-widget mounted!', this.isOpen);
        },

        watch: {
            isOpen(newVal) {
                console.log('isOpen changed to:', newVal);
            }
        },

        methods: {
            toggleWidget() {
                this.isOpen = !this.isOpen;
                if (this.isOpen && this.messages.length === 0) {
                    this.loadConversations();
                }
            },

            async sendMessage() {
                if (!this.inputMessage.trim() || this.isLoading) return;

                const userMessage = this.inputMessage.trim();
                this.inputMessage = '';
                this.messages.push({ role: 'user', content: userMessage });
                this.isLoading = true;

                try {
                    const response = await this.$axios.post("{{ route('admin.ai.copilot.message') }}", {
                        message: userMessage,
                        conversation_id: this.conversationId,
                    });

                    if (response.data.data) {
                        this.messages.push({
                            role: 'assistant',
                            content: response.data.data.message,
                        });

                        if (response.data.data.conversation_id) {
                            this.conversationId = response.data.data.conversation_id;
                        }
                    }
                } catch (error) {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message: error.response?.data?.message || 'Failed to send message'
                    });
                    this.messages.pop(); // Remove user message on error
                } finally {
                    this.isLoading = false;
                }
            },

            async loadConversations() {
                try {
                    const response = await this.$axios.get("{{ route('admin.ai.copilot.conversations') }}");
                    if (response.data.data && response.data.data.length > 0) {
                        const latestConversation = response.data.data[0];
                        this.conversationId = latestConversation.id;
                        await this.loadMessages(latestConversation.id);
                    }
                } catch (error) {
                    console.error('Error loading conversations:', error);
                }
            },

            async loadMessages(conversationId) {
                try {
                    const response = await this.$axios.get(
                        `{{ url('admin/ai/copilot/conversations') }}/${conversationId}/messages`
                    );
                    if (response.data.data) {
                        this.messages = response.data.data.map(msg => ({
                            role: msg.role,
                            content: msg.content,
                        }));
                    }
                } catch (error) {
                    console.error('Error loading messages:', error);
                }
            },
        },
        });
        console.log('v-copilot-widget component registered successfully');
    } else {
        console.error('app is not available or app.component is not a function');
    }
</script>
@endpushOnce
