@props([
    'entity'            => null,
    'entityControlName' => null,
])

<!-- WhatsApp Button -->
<div>
    {!! view_render_event('admin.components.activities.actions.whatsapp.create_btn.before') !!}

    <button
        class="flex h-[74px] w-[84px] flex-col items-center justify-center gap-1 rounded-lg border border-transparent bg-green-200 font-medium text-green-900 transition-all hover:border-green-400"
        @click="$refs.whatsappActionComponent.openModal()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="currentColor" class="text-2xl dark:!text-green-900">
            <path
                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
        </svg>

        @lang('admin::app.components.activities.actions.whatsapp.btn')
    </button>

    {!! view_render_event('admin.components.activities.actions.whatsapp.create_btn.after') !!}

    {!! view_render_event('admin.components.activities.actions.whatsapp.before') !!}

    <!-- WhatsApp Activity Action Vue Component -->
    <v-whatsapp-activity
        ref="whatsappActionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-whatsapp-activity>

    {!! view_render_event('admin.components.activities.actions.whatsapp.after') !!}
</div>

@pushOnce('scripts')
    <script type="text/x-template" id="v-whatsapp-activity-template">
        <Teleport to="body">
            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, save)">
                    {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.before') !!}

                    <x-admin::modal
                        ref="whatsappActivityModal"
                        position="bottom-right"
                    >
                        <x-slot:header>
                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.header.title.before') !!}

                            <h3 class="text-base font-semibold dark:text-white">
                                @{{ modalTitle }}
                            </h3>

                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.header.title.after') !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.content.controls.before') !!}

                            <!-- Template Selection (Optional) -->
                            <x-admin::form.control-group v-if="templates.length > 0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.components.activities.actions.whatsapp.templates')
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedTemplate"
                                    @change="applyTemplate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">@lang('admin::app.components.activities.actions.whatsapp.select-template')</option>
                                    <option
                                        v-for="template in templates"
                                        :key="template.id"
                                        :value="template.body"
                                    >
                                        @{{ template.name }}
                                    </option>
                                </select>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" v-if="templates.length === 0">
                                    @lang('admin::app.components.activities.actions.whatsapp.no-templates')
                                </p>
                            </x-admin::form.control-group>

                            <!-- Message Text Area -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.components.activities.actions.whatsapp.message')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="message"
                                    id="whatsapp_message"
                                    v-model="message"
                                    rules="required"
                                    rows="5"
                                    :label="trans('admin::app.components.activities.actions.whatsapp.message')"
                                    :placeholder="trans('admin::app.components.activities.actions.whatsapp.message-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="message" />
                            </x-admin::form.control-group>

                            <!-- Mode Information -->
                            <div
                                v-if="hasBusinessAPI"
                                class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800"
                            >
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>@lang('admin::app.components.activities.actions.whatsapp.business-api-mode'):</strong>
                                    @lang('admin::app.components.activities.actions.whatsapp.business-api-description')
                                </p>
                            </div>

                            <div
                                v-else
                                class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800"
                            >
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <strong>@lang('admin::app.components.activities.actions.whatsapp.personal-mode'):</strong>
                                    @lang('admin::app.components.activities.actions.whatsapp.personal-description')
                                </p>
                            </div>

                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.footer.buttons.before') !!}

                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="secondary-button"
                                    @click="closeModal"
                                >
                                    @lang('admin::app.components.activities.actions.whatsapp.cancel')
                                </button>

                                <x-admin::button
                                    class="primary-button"
                                    :title="hasBusinessAPI ? trans('admin::app.components.activities.actions.whatsapp.send-btn') : trans('admin::app.components.activities.actions.whatsapp.open-btn')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring"
                                />
                            </div>

                            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.footer.buttons.after') !!}
                        </x-slot>
                    </x-admin::modal>

                    {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.components.activities.actions.whatsapp.form_controls.after') !!}
        </Teleport>
    </script>

    <script type="module">
        app.component('v-whatsapp-activity', {
            template: '#v-whatsapp-activity-template',

            props: {
                entity: {
                    type: Object,
                    required: true,
                    default: () => {}
                },

                entityControlName: {
                    type: String,
                    required: true,
                    default: ''
                }
            },

            data() {
                return {
                    isStoring: false,
                    message: '',
                    selectedTemplate: '',
                    templates: [],
                    hasBusinessAPI: false,
                    contactNumbers: [],
                }
            },

            computed: {
                modalTitle() {
                    const entityName = this.entity.name || this.entity.title || 'Contact';
                    return "{{ trans('admin::app.components.activities.actions.whatsapp.title') }}".replace(':name', entityName);
                },

                sendEndpoint() {
                    if (this.entityControlName === 'person_id') {
                        return "{{ route('admin.contacts.persons.whatsapp.send', 'replaceId') }}".replace('replaceId', this.entity.id);
                    } else if (this.entityControlName === 'lead_id') {
                        return "{{ route('admin.leads.whatsapp.send', 'replaceId') }}".replace('replaceId', this.entity.id);
                    }
                    return '';
                }
            },

            mounted() {
                this.loadWhatsAppData();
            },

            methods: {
                openModal() {
                    this.$refs.whatsappActivityModal.open();
                },

                closeModal() {
                    this.$refs.whatsappActivityModal.close();
                    this.message = '';
                    this.selectedTemplate = '';
                },

                applyTemplate() {
                    if (this.selectedTemplate) {
                        this.message = this.selectedTemplate;
                    }
                },

                loadWhatsAppData() {
                    this.$axios.get("{{ route('admin.whatsapp.data') }}")
                        .then(response => {
                            this.templates = response.data.templates || [];
                            this.hasBusinessAPI = response.data.hasBusinessAPI || false;
                        })
                        .catch(error => {
                            this.templates = [];
                            this.hasBusinessAPI = false;
                        });

                    if (this.entityControlName === 'person_id' && this.entity.contact_numbers) {
                        this.contactNumbers = this.entity.contact_numbers;
                    } else if (this.entityControlName === 'lead_id' && this.entity.person && this.entity.person.contact_numbers) {
                        this.contactNumbers = this.entity.person.contact_numbers;
                    }
                },

                save(params) {
                    if (!this.hasBusinessAPI) {
                        this.openWhatsAppPersonal();
                        return;
                    }

                    this.isStoring = true;

                    this.$axios.post(this.sendEndpoint, { message: params.message })
                        .then(response => {
                            this.isStoring = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emitter.emit('on-activity-added', response.data.data);

                            this.closeModal();
                        })
                        .catch(error => {
                            this.isStoring = false;

                            if (error.response && error.response.status == 422) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response.data.message || 'Validation error'
                                });
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'Failed to send WhatsApp message'
                                });
                            }
                        });
                },

                openWhatsAppPersonal() {
                    if (!Array.isArray(this.contactNumbers) || this.contactNumbers.length === 0) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "{{ trans('admin::app.components.activities.actions.whatsapp.no-contact-number') }}"
                        });
                        return;
                    }

                    const clean = (num) => num.replace(/\D/g, '');
                    let targetNumber = null;

                    for (let contact of this.contactNumbers) {
                        if (contact.label && (contact.label.toLowerCase() === 'mobile' || contact.label.toLowerCase() === 'whatsapp')) {
                            targetNumber = clean(contact.value);
                            break;
                        }
                    }

                    if (!targetNumber && this.contactNumbers[0] && this.contactNumbers[0].value) {
                        targetNumber = clean(this.contactNumbers[0].value);
                    }

                    if (targetNumber) {
                        const encodedMessage = encodeURIComponent(this.message);
                        window.open(`https://wa.me/${targetNumber}?text=${encodedMessage}`, '_blank');

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "{{ trans('admin::app.components.activities.actions.whatsapp.opening-whatsapp') }}"
                        });

                        this.closeModal();
                    } else {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "{{ trans('admin::app.components.activities.actions.whatsapp.invalid-number') }}"
                        });
                    }
                }
            },
        });
    </script>
@endPushOnce
