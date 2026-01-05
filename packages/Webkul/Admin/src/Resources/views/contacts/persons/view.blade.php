<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.contacts.persons.view.title', ['name' => $person->name])
        </x-slot>

        <!-- Content -->
        <div class="flex gap-4 max-lg:flex-wrap">
            <!-- Left Panel -->
            {!! view_render_event('admin.contact.persons.view.left.before', ['person' => $person]) !!}

            <div
                class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Person Information -->
                <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                    <!-- Breadcrumbs -->
                    <div class="flex items-center justify-between">
                        <x-admin::breadcrumbs name="contacts.persons.view" :entity="$person" />
                    </div>

                    {!! view_render_event('admin.contact.persons.view.tags.before', ['person' => $person]) !!}

                    <!-- Tags -->
                    <x-admin::tags :attach-endpoint="route('admin.contacts.persons.tags.attach', $person->id)"
                        :detach-endpoint="route('admin.contacts.persons.tags.detach', $person->id)"
                        :added-tags="$person->tags" />

                    {!! view_render_event('admin.contact.persons.view.tags.after', ['person' => $person]) !!}


                    <!-- Title -->
                    <div class="mb-4 flex flex-col gap-0.5">
                        {!! view_render_event('admin.contact.persons.view.title.before', ['person' => $person]) !!}

                        <h3 class="text-lg font-bold dark:text-white">
                            {{ $person->name }}
                        </h3>

                        <p class="dark:text-white">
                            {{ $person->job_title }}
                        </p>

                        {!! view_render_event('admin.contact.persons.view.title.after', ['person' => $person]) !!}
                    </div>

                    <!-- Activity Actions -->
                    <div class="flex flex-wrap gap-2">
                        {!! view_render_event('admin.contact.persons.view.actions.before', ['person' => $person]) !!}

                        <!-- Mail Activity Action -->
                        <x-admin::activities.actions.mail :entity="$person" entity-control-name="person_id" />

                        <!-- WhatsApp Action -->
                        @if ($person->contact_numbers)
                            <button
                                class="flex h-[74px] w-[84px] flex-col items-center justify-center gap-1 rounded-lg border border-transparent bg-green-200 font-medium text-green-900 transition-all hover:border-green-400"
                                @click="$refs.whatsappModal.open()" title="Message on WhatsApp">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor" class="text-2xl dark:!text-green-900">
                                    <path
                                        d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                                </svg>
                                WhatsApp
                            </button>

                            <!-- WhatsApp Modal -->
                            <x-admin::modal ref="whatsappModal" position="bottom-right">
                                <x-slot:header>
                                    <h3 class="text-base font-semibold dark:text-white">
                                        Send WhatsApp Message to {{ $person->name }}
                                    </h3>
                                    </x-slot>

                                    <x-slot:content>
                                        <x-admin::form v-slot="{ meta, errors, handleSubmit }" as="div">
                                            <form @submit="handleSubmit($event, sendWhatsAppMessage)">
                                                <!-- Message Templates (Optional) -->
                                                <x-admin::form.control-group>
                                                    <x-admin::form.control-group.label>
                                                        Quick Templates (Optional)
                                                    </x-admin::form.control-group.label>

                                                    <select
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                                        x-data="{ selectedTemplate: '' }" x-model="selectedTemplate"
                                                        @change="if (selectedTemplate) { 
                                                                const textarea = document.getElementById('whatsapp_message');
                                                                if (textarea) {
                                                                    textarea.value = selectedTemplate;
                                                                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                                                                }
                                                            }">
                                                        <option value="">-- Select a template --</option>
                                                        <option
                                                            value="Hi {{ $person->name }}, I wanted to follow up on our previous conversation. Are you available for a quick call?">
                                                            Follow-up Message</option>
                                                        <option
                                                            value="Hello {{ $person->name }}, thank you for your interest! I'd love to discuss how we can help you.">
                                                            Introduction</option>
                                                        <option
                                                            value="Hi {{ $person->name }}, just checking in to see if you have any questions. Feel free to reach out anytime!">
                                                            Check-in</option>
                                                        <option
                                                            value="Hello {{ $person->name }}, I have some exciting updates to share with you. When would be a good time to connect?">
                                                            Update Notification</option>
                                                    </select>
                                                </x-admin::form.control-group>

                                                <!-- Message Text Area -->
                                                <x-admin::form.control-group>
                                                    <x-admin::form.control-group.label class="required">
                                                        Message
                                                    </x-admin::form.control-group.label>

                                                    <x-admin::form.control-group.control type="textarea" name="message"
                                                        id="whatsapp_message" rules="required" label="Message"
                                                        placeholder="Type your message here..." rows="5" />

                                                    <x-admin::form.control-group.error control-name="message" />
                                                </x-admin::form.control-group>

                                                <!-- Mode Selection Info -->
                                                @php
                                                    $hasBusinessAPI = auth()->guard('user')->user()->whatsapp_phone_number_id &&
                                                        auth()->guard('user')->user()->whatsapp_access_token;
                                                @endphp

                                                @if($hasBusinessAPI)
                                                    <div
                                                        class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                                            <strong>Business API Mode:</strong> Message will be sent via your
                                                            WhatsApp Business API
                                                        </p>
                                                    </div>
                                                @else
                                                    <div
                                                        class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                                                        <p class="text-sm text-green-800 dark:text-green-200">
                                                            <strong>Personal WhatsApp Mode:</strong> Your WhatsApp app will open
                                                            with the message pre-filled
                                                        </p>
                                                    </div>
                                                @endif

                                                <!-- Action Buttons -->
                                                <div class="flex justify-end gap-2 mt-4">
                                                    <button type="button" class="secondary-button"
                                                        @click="$refs.whatsappModal.close()">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="primary-button">
                                                        @if($hasBusinessAPI)
                                                            Send via API
                                                        @else
                                                            Open in WhatsApp
                                                        @endif
                                                    </button>
                                                </div>
                                            </form>
                                        </x-admin::form>
                                        </x-slot>
                            </x-admin::modal>

                            @push('scripts')
                                <script>
                                    const hasBusinessAPI = {{ $hasBusinessAPI ? 'true' : 'false' }};
                                    const contactNumbers = @json($person->contact_numbers);

                                    function sendWhatsAppMessage(params, { resetForm, setErrors }) {
                                        if (!hasBusinessAPI) {
                                            // Personal WhatsApp Mode - Open in WhatsApp app
                                            openWhatsAppWithMessage(params.message);
                                            resetForm();
                                            document.querySelector('[x-ref="whatsappModal"]').__x.$data.isOpen = false;
                                            return;
                                        }

                                        // Business API Mode - Send via API
                                        const formData = new FormData();
                                        formData.append('message', params.message);
                                        formData.append('_token', '{{ csrf_token() }}');

                                        fetch('{{ route("admin.contacts.persons.whatsapp.send", $person->id) }}', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                            }
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    window.emitter.emit('add-flash', {
                                                        type: 'success',
                                                        message: data.message
                                                    });
                                                    resetForm();
                                                    document.querySelector('[x-ref="whatsappModal"]').__x.$data.isOpen = false;
                                                } else {
                                                    window.emitter.emit('add-flash', {
                                                        type: 'error',
                                                        message: data.message
                                                    });
                                                }
                                            })
                                            .catch(error => {
                                                window.emitter.emit('add-flash', {
                                                    type: 'error',
                                                    message: 'Failed to send WhatsApp message. Please try again.'
                                                });
                                            });
                                    }

                                    function openWhatsAppWithMessage(message) {
                                        if (!Array.isArray(contactNumbers) || contactNumbers.length === 0) {
                                            window.emitter.emit('add-flash', {
                                                type: 'error',
                                                message: 'No contact number found for this person.'
                                            });
                                            return;
                                        }

                                        // Find the best phone number
                                        const clean = (num) => num.replace(/\D/g, '');
                                        let targetNumber = null;

                                        for (let contact of contactNumbers) {
                                            if (contact.label && (contact.label.toLowerCase() === 'mobile' || contact.label.toLowerCase() === 'whatsapp')) {
                                                targetNumber = clean(contact.value);
                                                break;
                                            }
                                        }

                                        if (!targetNumber && contactNumbers[0] && contactNumbers[0].value) {
                                            targetNumber = clean(contactNumbers[0].value);
                                        }

                                        if (targetNumber) {
                                            // Encode the message for URL
                                            const encodedMessage = encodeURIComponent(message);
                                            // Open WhatsApp with pre-filled message
                                            window.open(`https://wa.me/${targetNumber}?text=${encodedMessage}`, '_blank');

                                            window.emitter.emit('add-flash', {
                                                type: 'success',
                                                message: 'Opening WhatsApp...'
                                            });
                                        } else {
                                            window.emitter.emit('add-flash', {
                                                type: 'error',
                                                message: 'Invalid contact number.'
                                            });
                                        }
                                    }
                                </script>
                            @endpush
                        @endif

                        <!-- File Activity Action -->
                        <x-admin::activities.actions.file :entity="$person" entity-control-name="person_id" />

                        <!-- Note Activity Action -->
                        <x-admin::activities.actions.note :entity="$person" entity-control-name="person_id" />

                        <!-- Activity Action -->
                        <x-admin::activities.actions.activity :entity="$person" entity-control-name="person_id" />

                        {!! view_render_event('admin.contact.persons.view.actions.after', ['person' => $person]) !!}
                    </div>
                </div>

                <!-- Person Attributes -->
                @include ('admin::contacts.persons.view.attributes')

                <!-- Contact Organization -->
                @include ('admin::contacts.persons.view.organization')

                <!-- AI Insights -->
                @include ('admin::contacts.persons.view.insights')
            </div>

            {!! view_render_event('admin.contact.persons.view.left.after', ['person' => $person]) !!}

            <!-- Right Panel -->
            <div class="flex w-full flex-col gap-4 rounded-lg">
                {!! view_render_event('admin.contact.persons.view.right.before', ['person' => $person]) !!}

                <!-- Stages Navigation -->
                <x-admin::activities :endpoint="route('admin.contacts.persons.activities.index', $person->id)" />

                {!! view_render_event('admin.contact.persons.view.right.after', ['person' => $person]) !!}
            </div>
        </div>
</x-admin::layouts>