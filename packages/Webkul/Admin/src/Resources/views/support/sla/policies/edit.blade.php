<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.sla.edit.title')
        </x-slot>

        <x-admin::form :action="route('admin.support.sla.policies.update', $policy->id)">
            @method('PUT')

            <div class="flex flex-col gap-4">
                <!-- Header -->
                <div
                    class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div class="flex flex-col gap-2">
                        <x-admin::breadcrumbs name="support.sla.policies.edit" :entity="$policy" />

                        <div class="text-xl font-bold dark:text-white">
                            @lang('admin::app.support.sla.edit.title')
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2.5">
                        <a href="{{ route('admin.support.sla.policies.index') }}"
                            class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white">
                            @lang('admin::app.common.cancel')
                        </a>

                        <button type="submit" class="primary-button">
                            @lang('admin::app.support.sla.edit.update-btn')
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex gap-4">
                    <!-- Left Section: General Info -->
                    <div class="flex flex-1 flex-col gap-4">
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <p class="mb-4 text-base font-semibold dark:text-white">
                                @lang('admin::app.support.sla.create.general')
                            </p>

                            <!-- Name -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.support.sla.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control type="text" name="name"
                                    value="{{ old('name', $policy->name) }}" rules="required"
                                    placeholder="Type policy name" />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.support.sla.create.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control type="textarea" name="description"
                                    value="{{ old('description', $policy->description) }}" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- Rules Section -->
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <p class="mb-4 text-base font-semibold dark:text-white">
                                @lang('admin::app.support.sla.create.rules')
                            </p>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">
                                                @lang('admin::app.support.sla.create.priority')
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                @lang('admin::app.support.sla.create.first-response-time')
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                @lang('admin::app.support.sla.create.resolution-time')
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $rules = $policy->rules->keyBy('priority');
                                        @endphp
                                        @foreach(['urgent', 'high', 'normal', 'low'] as $index => $priority)
                                            @php
                                                $rule = $rules[$priority] ?? null;
                                            @endphp
                                            <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                                                <td class="px-6 py-4">
                                                    <input type="hidden" name="rules[{{ $index }}][priority]"
                                                        value="{{ $priority }}">
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/10">
                                                        {{ ucfirst($priority) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" name="rules[{{ $index }}][first_response_time]"
                                                        value="{{ old("rules.$index.first_response_time", $rule ? $rule->first_response_time : 60) }}"
                                                        class="w-full rounded border border-gray-200 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                        min="1" required>
                                                    <x-admin::form.control-group.error
                                                        control-name="rules[{{ $index }}][first_response_time]" />
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="number" name="rules[{{ $index }}][resolution_time]"
                                                        value="{{ old("rules.$index.resolution_time", $rule ? $rule->resolution_time : 240) }}"
                                                        class="w-full rounded border border-gray-200 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                        min="1" required>
                                                    <x-admin::form.control-group.error
                                                        control-name="rules[{{ $index }}][resolution_time]" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Switches -->
                    <div class="flex w-[360px] max-w-full flex-col gap-4">
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <p class="mb-4 text-base font-semibold dark:text-white">
                                Settings
                            </p>

                            <!-- Is Active -->
                            <x-admin::form.control-group class="mb-4 flex items-center justify-between">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.support.sla.create.is-active')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control type="switch" name="is_active" value="1"
                                    :checked="old('is_active', $policy->is_active)" />
                            </x-admin::form.control-group>

                            <!-- Is Default -->
                            <x-admin::form.control-group class="mb-4 flex items-center justify-between">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.support.sla.create.is-default')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control type="switch" name="is_default" value="1"
                                    :checked="old('is_default', $policy->is_default)" />
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin::form>
</x-admin::layouts>