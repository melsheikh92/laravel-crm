{{-- IMAP Configuration Form Partial --}}
<div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
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
        </x-admin::form.control-group>

        <!-- IMAP Validate Certificate -->
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('admin::app.configuration.index.email.imap.account.validate-cert')
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                name="email.imap.account.validate_cert"
                id="imap_validate_cert"
                v-model="imap.validate_cert"
                :value="old('email.imap.account.validate_cert') ?? $imapConfig['validate_cert']"
                :label="trans('admin::app.configuration.index.email.imap.account.validate-cert')"
            >
                <option value="1">@lang('admin::app.settings.mail-configuration.index.yes')</option>
                <option value="0">@lang('admin::app.settings.mail-configuration.index.no')</option>
            </x-admin::form.control-group.control>

            <x-admin::form.control-group.error control-name="email.imap.account.validate_cert" />
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
        </x-admin::form.control-group>
    </div>
</div>
