{{-- SMTP Configuration Form Partial --}}
<div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
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
        </x-admin::form.control-group>
    </div>
</div>
