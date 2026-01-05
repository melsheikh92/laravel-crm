@php
    // Helper variables for translations with fallback
    $_trans_title = trans('admin::app.marketing.campaigns.edit.title');
    $_title = $_trans_title !== 'admin::app.marketing.campaigns.edit.title' ? $_trans_title : 'Edit Campaign';
    
    $_trans_name = trans('admin::app.marketing.campaigns.create.name');
    $_name = $_trans_name !== 'admin::app.marketing.campaigns.create.name' ? $_trans_name : 'Campaign Name';
    
    $_trans_subject = trans('admin::app.marketing.campaigns.create.subject');
    $_subject = $_trans_subject !== 'admin::app.marketing.campaigns.create.subject' ? $_trans_subject : 'Email Subject';
    
    $_trans_content = trans('admin::app.marketing.campaigns.create.content');
    $_content = $_trans_content !== 'admin::app.marketing.campaigns.create.content' ? $_trans_content : 'Email Content';
    
    $_trans_template = trans('admin::app.marketing.campaigns.create.template');
    $_template = $_trans_template !== 'admin::app.marketing.campaigns.create.template' ? $_trans_template : 'Email Template';
    
    $_trans_select_template = trans('admin::app.marketing.campaigns.create.select-template');
    $_select_template = $_trans_select_template !== 'admin::app.marketing.campaigns.create.select-template' ? $_select_template : 'Select Template';
    
    $_trans_recipients = trans('admin::app.marketing.campaigns.create.recipients');
    $_recipients = $_trans_recipients !== 'admin::app.marketing.campaigns.create.recipients' ? $_recipients : 'Recipients';
    
    $_trans_select_recipients = trans('admin::app.marketing.campaigns.create.select-recipients');
    $_select_recipients = $_trans_select_recipients !== 'admin::app.marketing.campaigns.create.select-recipients' ? $_select_recipients : 'Select Recipients';
    
    $_trans_settings = trans('admin::app.marketing.campaigns.create.settings');
    $_settings = $_trans_settings !== 'admin::app.marketing.campaigns.create.settings' ? $_settings : 'Settings';
    
    $_trans_status = trans('admin::app.marketing.campaigns.create.status');
    $_status = $_trans_status !== 'admin::app.marketing.campaigns.create.status' ? $_status : 'Status';
    
    $_trans_status_draft = trans('admin::app.marketing.campaigns.create.status-draft');
    $_status_draft = $_trans_status_draft !== 'admin::app.marketing.campaigns.create.status-draft' ? $_status_draft : 'Draft';
    
    $_trans_status_scheduled = trans('admin::app.marketing.campaigns.create.status-scheduled');
    $_status_scheduled = $_trans_status_scheduled !== 'admin::app.marketing.campaigns.create.status-scheduled' ? $_status_scheduled : 'Scheduled';
    
    $_trans_scheduled_at = trans('admin::app.marketing.campaigns.create.scheduled-at');
    $_scheduled_at = $_trans_scheduled_at !== 'admin::app.marketing.campaigns.create.scheduled-at' ? $_scheduled_at : 'Scheduled At';
    
    $_trans_sender_name = trans('admin::app.marketing.campaigns.create.sender-name');
    $_sender_name = $_trans_sender_name !== 'admin::app.marketing.campaigns.create.sender-name' ? $_sender_name : 'Sender Name';
    
    $_trans_sender_email = trans('admin::app.marketing.campaigns.create.sender-email');
    $_sender_email = $_trans_sender_email !== 'admin::app.marketing.campaigns.create.sender-email' ? $_sender_email : 'Sender Email';
    
    $_trans_reply_to = trans('admin::app.marketing.campaigns.create.reply-to');
    $_reply_to = $_trans_reply_to !== 'admin::app.marketing.campaigns.create.reply-to' ? $_reply_to : 'Reply To';
    
    $_trans_cancel = trans('admin::app.marketing.campaigns.create.cancel');
    $_cancel = $_trans_cancel !== 'admin::app.marketing.campaigns.create.cancel' ? $_cancel : 'Cancel';
    
    $_trans_save = trans('admin::app.marketing.campaigns.edit.update-btn');
    $_save = $_trans_save !== 'admin::app.marketing.campaigns.edit.update-btn' ? $_save : 'Update Campaign';
    
    // Format scheduled_at for datetime input
    $scheduled_at_formatted = $campaign->scheduled_at ? date('Y-m-d\TH:i', strtotime($campaign->scheduled_at)) : '';
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $_title }}
    </x-slot:title>

    {!! view_render_event('admin.marketing.campaigns.edit.before') !!}

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap mb-6">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">{{ $_title }}</p>
        </div>
    </div>

    <div x-data="{ status: '{{ $campaign->status ?? 'draft' }}' }">
        <x-admin::form :action="route('admin.marketing.campaigns.update', $campaign->id)" v-slot="{ meta, errors, handleSubmit }" as="div">
            <form method="POST" action="{{ route('admin.marketing.campaigns.update', $campaign->id) }}" @submit.prevent="console.log('Form submit event triggered', errors); handleSubmit($event, window.submitForm)">
                @csrf
                @method('PUT')
            <div class="flex gap-6 max-xl:flex-wrap">
                <div class="flex flex-col gap-6 flex-1 max-xl:flex-auto">
                    <div class="px-6 py-10 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow">
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-gray-800 dark:text-white mb-2">
                                {{ $_name }}
                                <span class="text-red-600">*</span>
                            </label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="name"
                                :label="$_name"
                                :value="old('name', $campaign->name)"
                                rules="required"
                            />
                            <x-admin::form.control-group.error name="name"/>
                        </div>

                        <div class="mb-6">
                            <label class="block text-base font-semibold text-gray-800 dark:text-white mb-2">
                                {{ $_subject }}
                                <span class="text-red-600">*</span>
                            </label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="subject"
                                :label="$_subject"
                                :value="old('subject', $campaign->subject)"
                                rules="required"
                            />
                            <x-admin::form.control-group.error name="subject"/>
                        </div>

                        <div class="mb-6">
                            <label class="block text-base font-semibold text-gray-800 dark:text-white mb-2">
                                {{ $_content }}
                                <span class="text-red-600">*</span>
                            </label>
                            <x-admin::form.control-group.control
                                type="textarea"
                                name="content"
                                :label="$_content"
                                :value="old('content', $campaign->content)"
                                rules="required"
                                :rows="10"
                            />
                            <x-admin::form.control-group.error name="content"/>
                        </div>

                        <div>
                            <label class="block text-base font-semibold text-gray-800 dark:text-white mb-2">
                                {{ $_template }}
                            </label>
                            <x-admin::lookup
                                name="template_id"
                                :src="route('admin.marketing.templates.index')"
                                :placeholder="$_select_template"
                                :value="old('template_id', $campaign->template_id)"
                            />
                        </div>
                    </div>

                    <div class="px-6 py-10 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                            {{ $_recipients }}
                        </h3>

                        <div>
                            <label class="block text-base font-semibold text-gray-800 dark:text-white mb-2">
                                {{ $_select_recipients }}
                            </label>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Recipients can be managed after updating the campaign.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6 w-[360px] max-w-full max-xl:w-full">
                    <div class="px-6 py-10 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6">
                            {{ $_settings }}
                        </h3>

                        <div class="mb-6">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ $_status }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="status"
                                    :label="$_status"
                                    x-model="status"
                                    :value="old('status', $campaign->status)"
                                >
                                    <option value="draft">{{ $_status_draft }}</option>
                                    <option value="scheduled">{{ $_status_scheduled }}</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </div>

                        <div class="mb-6" x-show="status === 'scheduled'">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ $_scheduled_at }}
                                    <span class="text-red-600">*</span>
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="datetime"
                                    name="scheduled_at"
                                    :label="$_scheduled_at"
                                    :value="old('scheduled_at', $scheduled_at_formatted)"
                                    rules=""
                                />
                                <x-admin::form.control-group.error name="scheduled_at"/>
                            </x-admin::form.control-group>
                        </div>

                        <div class="mb-6">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ $_sender_name }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="sender_name"
                                    :label="$_sender_name"
                                    :value="old('sender_name', $campaign->sender_name)"
                                />
                            </x-admin::form.control-group>
                        </div>

                        <div class="mb-6">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ $_sender_email }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="email"
                                    name="sender_email"
                                    :label="$_sender_email"
                                    :value="old('sender_email', $campaign->sender_email)"
                                />
                            </x-admin::form.control-group>
                        </div>

                        <div>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ $_reply_to }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="email"
                                    name="reply_to"
                                    :label="$_reply_to"
                                    :value="old('reply_to', $campaign->reply_to)"
                                />
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-x-2.5 justify-end items-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('admin.marketing.campaigns.index') }}">
                    <button type="button" class="secondary-button">
                        {{ $_cancel }}
                    </button>
                </a>

                <button type="submit" class="primary-button">
                    {{ $_save }}
                </button>
            </div>
            </form>
        </x-admin::form>
    </div>

    {!! view_render_event('admin.marketing.campaigns.edit.after') !!}

    @push('scripts')
    <script>
        window.submitForm = function(params, { resetForm, setErrors }) {
            console.log('submitForm called - validation passed!', params);
            
            const form = document.querySelector('form');
            if (!form) {
                console.error('Form not found');
                return;
            }
            
            const formData = new FormData(form);
            
            fetch("{{ route('admin.marketing.campaigns.update', $campaign->id) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            return Promise.reject({ response: { status: response.status, data: data } });
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success:', data);
                    const message = data.message && !data.message.includes('admin::app.marketing.campaigns.update-success') 
                        ? data.message 
                        : 'Campaign updated successfully.';
                    
                    if (window.emitter) {
                        window.emitter.emit('add-flash', { 
                            type: 'success', 
                            message: message
                        });
                    }
                    
                    // Redirect immediately
                    console.log('Redirecting to campaigns list...');
                    window.location.replace("{{ route('admin.marketing.campaigns.index') }}");
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (error.response && error.response.status === 422) {
                        setErrors(error.response.data.errors || {});
                    } else {
                        const errorMessage = error.response?.data?.message || 'An error occurred while updating the campaign.';
                        if (window.emitter) {
                            window.emitter.emit('add-flash', { 
                                type: 'error', 
                                message: errorMessage 
                            });
                        }
                    }
                });
        };
    </script>
    @endpush
</x-admin::layouts>

