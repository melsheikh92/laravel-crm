<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.mail-configuration.index.title')
    </x-slot>

    {!! view_render_event('admin.settings.mail_configuration.index.form.before') !!}

    <x-admin::form
        :action="route('admin.settings.mail_configuration.store')"
        method="POST"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.mail_configuration.index.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.mail_configuration" />

                    {!! view_render_event('admin.settings.mail_configuration.index.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.mail-configuration.index.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.mail_configuration.index.save_button.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.mail-configuration.index.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.mail_configuration.index.save_button.after') !!}
                    </div>
                </div>
            </div>

            <v-mail-configuration
                :smtp-config="{{ json_encode($smtpConfig) }}"
                :imap-config="{{ json_encode($imapConfig) }}"
            ></v-mail-configuration>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.mail_configuration.index.form.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-mail-configuration-template"
        >
            <div class="flex flex-col gap-4">
                {!! view_render_event('admin.settings.mail_configuration.index.tabs.before') !!}

                <!-- Tab Navigation -->
                <div class="flex border-b border-gray-200 dark:border-gray-800">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium transition-all"
                        :class="activeTab === 'smtp' ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white'"
                        @click="activeTab = 'smtp'"
                    >
                        @lang('admin::app.settings.mail-configuration.index.smtp-tab')
                    </button>

                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium transition-all"
                        :class="activeTab === 'imap' ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white'"
                        @click="activeTab = 'imap'"
                    >
                        @lang('admin::app.settings.mail-configuration.index.imap-tab')
                    </button>

                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium transition-all"
                        :class="activeTab === 'templates' ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white'"
                        @click="activeTab = 'templates'"
                    >
                        @lang('admin::app.settings.mail-configuration.index.templates-tab')
                    </button>
                </div>

                {!! view_render_event('admin.settings.mail_configuration.index.tabs.after') !!}

                <!-- SMTP Tab Content -->
                <div v-show="activeTab === 'smtp'" class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.mail-configuration.index.smtp.title')
                            </p>

                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.info')
                            </p>
                        </div>

                        <button
                            type="button"
                            class="secondary-button"
                            @click="testSmtpConnection"
                            :disabled="testingSmtp"
                        >
                            <span v-if="testingSmtp">@lang('admin::app.settings.mail-configuration.index.testing')</span>
                            <span v-else>@lang('admin::app.settings.mail-configuration.index.test-connection')</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- SMTP Host -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.host')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.smtp.account.host"
                                id="smtp_host"
                                v-model="smtp.host"
                                :value="old('email.smtp.account.host') ?? $smtpConfig['host']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.host')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.host')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.host" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.host-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- SMTP Port -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.port')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.smtp.account.port"
                                id="smtp_port"
                                v-model="smtp.port"
                                :value="old('email.smtp.account.port') ?? $smtpConfig['port']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.port')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.port')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.port" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.port-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- SMTP Encryption -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.encryption')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="email.smtp.account.encryption"
                                id="smtp_encryption"
                                v-model="smtp.encryption"
                                :value="old('email.smtp.account.encryption') ?? $smtpConfig['encryption']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.encryption')"
                            >
                                <option value="">@lang('admin::app.settings.mail-configuration.index.none')</option>
                                <option value="tls">@lang('admin::app.settings.mail-configuration.index.tls')</option>
                                <option value="ssl">@lang('admin::app.settings.mail-configuration.index.ssl')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="email.smtp.account.encryption" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.encryption-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- SMTP Username -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.username')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.smtp.account.username"
                                id="smtp_username"
                                v-model="smtp.username"
                                :value="old('email.smtp.account.username') ?? $smtpConfig['username']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.username')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.username')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.username" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.username-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- SMTP Password -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.password')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="password"
                                name="email.smtp.account.password"
                                id="smtp_password"
                                v-model="smtp.password"
                                :value="old('email.smtp.account.password') ?? $smtpConfig['password']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.password')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.password')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.password" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.password-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- From Address -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.from-address')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                name="email.smtp.account.from_address"
                                id="smtp_from_address"
                                v-model="smtp.from_address"
                                :value="old('email.smtp.account.from_address') ?? $smtpConfig['from_address']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.from-address')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.from-address')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.from_address" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.from-address-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- From Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.smtp.account.from-name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.smtp.account.from_name"
                                id="smtp_from_name"
                                v-model="smtp.from_name"
                                :value="old('email.smtp.account.from_name') ?? $smtpConfig['from_name']"
                                :label="trans('admin::app.configuration.index.email.smtp.account.from-name')"
                                :placeholder="trans('admin::app.configuration.index.email.smtp.account.from-name')"
                            />

                            <x-admin::form.control-group.error control-name="email.smtp.account.from_name" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.smtp.from-name-hint')
                            </p>
                        </x-admin::form.control-group>
                    </div>

                    <!-- Common Provider Settings Reference -->
                    <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <h3 class="mb-3 text-sm font-semibold text-blue-900 dark:text-blue-100">
                            @lang('admin::app.settings.mail-configuration.index.providers.title')
                        </h3>
                        <p class="mb-4 text-xs text-blue-800 dark:text-blue-200">
                            @lang('admin::app.settings.mail-configuration.index.providers.info')
                        </p>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <!-- Gmail Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.gmail.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.smtp-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.smtp-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.smtp-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.gmail.note')
                                </p>
                            </div>

                            <!-- Outlook/Office 365 Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.outlook.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.smtp-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.smtp-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.smtp-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.outlook.note')
                                </p>
                            </div>

                            <!-- Yahoo Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.yahoo.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.smtp-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.smtp-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.smtp.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.smtp-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.yahoo.note')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- IMAP Tab Content -->
                <div v-show="activeTab === 'imap'" class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.mail-configuration.index.imap.title')
                            </p>

                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.info')
                            </p>
                        </div>

                        <button
                            type="button"
                            class="secondary-button"
                            @click="testImapConnection"
                            :disabled="testingImap"
                        >
                            <span v-if="testingImap">@lang('admin::app.settings.mail-configuration.index.testing')</span>
                            <span v-else>@lang('admin::app.settings.mail-configuration.index.test-connection')</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- IMAP Host -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.imap.account.host')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.imap.account.host"
                                id="imap_host"
                                v-model="imap.host"
                                :value="old('email.imap.account.host') ?? $imapConfig['host']"
                                :label="trans('admin::app.configuration.index.email.imap.account.host')"
                                :placeholder="trans('admin::app.configuration.index.email.imap.account.host')"
                            />

                            <x-admin::form.control-group.error control-name="email.imap.account.host" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.host-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- IMAP Port -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.imap.account.port')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.imap.account.port"
                                id="imap_port"
                                v-model="imap.port"
                                :value="old('email.imap.account.port') ?? $imapConfig['port']"
                                :label="trans('admin::app.configuration.index.email.imap.account.port')"
                                :placeholder="trans('admin::app.configuration.index.email.imap.account.port')"
                            />

                            <x-admin::form.control-group.error control-name="email.imap.account.port" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.port-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- IMAP Encryption -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.imap.account.encryption')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="email.imap.account.encryption"
                                id="imap_encryption"
                                v-model="imap.encryption"
                                :value="old('email.imap.account.encryption') ?? $imapConfig['encryption']"
                                :label="trans('admin::app.configuration.index.email.imap.account.encryption')"
                            >
                                <option value="tls">@lang('admin::app.settings.mail-configuration.index.tls')</option>
                                <option value="ssl">@lang('admin::app.settings.mail-configuration.index.ssl')</option>
                                <option value="notls">@lang('admin::app.settings.mail-configuration.index.no-encryption')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="email.imap.account.encryption" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.encryption-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- IMAP Username -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.imap.account.username')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="email.imap.account.username"
                                id="imap_username"
                                v-model="imap.username"
                                :value="old('email.imap.account.username') ?? $imapConfig['username']"
                                :label="trans('admin::app.configuration.index.email.imap.account.username')"
                                :placeholder="trans('admin::app.configuration.index.email.imap.account.username')"
                            />

                            <x-admin::form.control-group.error control-name="email.imap.account.username" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.username-hint')
                            </p>
                        </x-admin::form.control-group>

                        <!-- IMAP Password -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.configuration.index.email.imap.account.password')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="password"
                                name="email.imap.account.password"
                                id="imap_password"
                                v-model="imap.password"
                                :value="old('email.imap.account.password') ?? $imapConfig['password']"
                                :label="trans('admin::app.configuration.index.email.imap.account.password')"
                                :placeholder="trans('admin::app.configuration.index.email.imap.account.password')"
                            />

                            <x-admin::form.control-group.error control-name="email.imap.account.password" />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.imap.password-hint')
                            </p>
                        </x-admin::form.control-group>
                    </div>

                    <!-- Common Provider Settings Reference -->
                    <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <h3 class="mb-3 text-sm font-semibold text-blue-900 dark:text-blue-100">
                            @lang('admin::app.settings.mail-configuration.index.providers.title')
                        </h3>
                        <p class="mb-4 text-xs text-blue-800 dark:text-blue-200">
                            @lang('admin::app.settings.mail-configuration.index.providers.info')
                        </p>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <!-- Gmail IMAP Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.gmail.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.imap-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.imap-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.gmail.imap-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.gmail.note')
                                </p>
                            </div>

                            <!-- Outlook/Office 365 IMAP Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.outlook.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.imap-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.imap-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.outlook.imap-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.outlook.note')
                                </p>
                            </div>

                            <!-- Yahoo IMAP Settings -->
                            <div class="rounded-md bg-white p-3 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.settings.mail-configuration.index.providers.yahoo.name')
                                </h4>
                                <dl class="space-y-1 text-xs">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.host'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.imap-host')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.port'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.imap-port')</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">@lang('admin::app.settings.mail-configuration.index.imap.encryption'):</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">@lang('admin::app.settings.mail-configuration.index.providers.yahoo.imap-encryption')</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.settings.mail-configuration.index.providers.yahoo.note')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Templates Tab Content -->
                <div v-show="activeTab === 'templates'" class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.mail-configuration.index.templates.title')
                            </p>

                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.mail-configuration.index.templates.info')
                            </p>
                        </div>

                        <a
                            href="{{ route('admin.settings.email_templates.index') }}"
                            class="secondary-button"
                        >
                            @lang('admin::app.settings.mail-configuration.index.manage-templates')
                        </a>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <p>@lang('admin::app.settings.mail-configuration.index.templates.description')</p>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-mail-configuration', {
                template: '#v-mail-configuration-template',

                props: ['smtpConfig', 'imapConfig'],

                data() {
                    return {
                        activeTab: 'smtp',

                        testingSmtp: false,

                        testingImap: false,

                        smtp: {
                            host: this.smtpConfig?.host || '',
                            port: this.smtpConfig?.port || '',
                            encryption: this.smtpConfig?.encryption || '',
                            username: this.smtpConfig?.username || '',
                            password: this.smtpConfig?.password || '',
                            from_address: this.smtpConfig?.from_address || '',
                            from_name: this.smtpConfig?.from_name || '',
                        },

                        imap: {
                            host: this.imapConfig?.host || '',
                            port: this.imapConfig?.port || '',
                            encryption: this.imapConfig?.encryption || 'tls',
                            username: this.imapConfig?.username || '',
                            password: this.imapConfig?.password || '',
                        },
                    };
                },

                methods: {
                    /**
                     * Test SMTP connection
                     *
                     * @returns {void}
                     */
                    testSmtpConnection() {
                        if (!this.smtp.host || !this.smtp.port || !this.smtp.username || !this.smtp.password || !this.smtp.from_address || !this.smtp.from_name) {
                            this.$emitter.emit('add-flash', { type: 'warning', message: "@lang('admin::app.settings.mail-configuration.index.fill-required-fields')" });
                            return;
                        }

                        this.testingSmtp = true;

                        this.$axios.post("{{ route('admin.settings.mail_configuration.test_smtp') }}", this.smtp)
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || "@lang('admin::app.settings.mail-configuration.index.smtp-test-failed')" });
                            })
                            .finally(() => {
                                this.testingSmtp = false;
                            });
                    },

                    /**
                     * Test IMAP connection
                     *
                     * @returns {void}
                     */
                    testImapConnection() {
                        if (!this.imap.host || !this.imap.port || !this.imap.username || !this.imap.password) {
                            this.$emitter.emit('add-flash', { type: 'warning', message: "@lang('admin::app.settings.mail-configuration.index.fill-required-fields')" });
                            return;
                        }

                        this.testingImap = true;

                        this.$axios.post("{{ route('admin.settings.mail_configuration.test_imap') }}", this.imap)
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || "@lang('admin::app.settings.mail-configuration.index.imap-test-failed')" });
                            })
                            .finally(() => {
                                this.testingImap = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
