@props([
    'extension' => null,
    'version' => null,
])

<div class="flex gap-2.5 max-xl:flex-wrap">
    {!! view_render_event('marketplace.developer.versions.form.left.before', ['extension' => $extension, 'version' => $version]) !!}

    <!-- Left Panel -->
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <!-- Version Information -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.versions.form.version-information')
            </p>

            <!-- Version Number -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.developer.versions.form.version')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="version"
                    name="version"
                    :value="old('version', $version?->version)"
                    rules="required"
                    :label="trans('marketplace::app.developer.versions.form.version')"
                    :placeholder="trans('marketplace::app.developer.versions.form.version-placeholder')"
                    @if($version?->status === 'approved') disabled @endif
                />

                <x-admin::form.control-group.error control-name="version" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.version-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Release Date -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.release-date')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="date"
                    id="release_date"
                    name="release_date"
                    :value="old('release_date', $version?->release_date?->format('Y-m-d'))"
                    :label="trans('marketplace::app.developer.versions.form.release-date')"
                />

                <x-admin::form.control-group.error control-name="release_date" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.release-date-hint')
                </p>
            </x-admin::form.control-group>
        </div>

        <!-- Changelog -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.versions.form.changelog')
            </p>

            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.changelog-label')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="changelog"
                    name="changelog"
                    :value="old('changelog', $version?->changelog)"
                    rows="10"
                    :label="trans('marketplace::app.developer.versions.form.changelog-label')"
                    :placeholder="trans('marketplace::app.developer.versions.form.changelog-placeholder')"
                />

                <x-admin::form.control-group.error control-name="changelog" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.changelog-hint')
                </p>
            </x-admin::form.control-group>
        </div>

        <!-- Compatibility Settings -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.versions.form.compatibility-settings')
            </p>

            <!-- Laravel Version -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.laravel-version')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="laravel_version"
                    name="laravel_version"
                    :value="old('laravel_version', $version?->laravel_version)"
                    :label="trans('marketplace::app.developer.versions.form.laravel-version')"
                    :placeholder="trans('marketplace::app.developer.versions.form.laravel-version-placeholder')"
                />

                <x-admin::form.control-group.error control-name="laravel_version" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.laravel-version-hint')
                </p>
            </x-admin::form.control-group>

            <!-- CRM Version -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.crm-version')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="crm_version"
                    name="crm_version"
                    :value="old('crm_version', $version?->crm_version)"
                    :label="trans('marketplace::app.developer.versions.form.crm-version')"
                    :placeholder="trans('marketplace::app.developer.versions.form.crm-version-placeholder')"
                />

                <x-admin::form.control-group.error control-name="crm_version" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.crm-version-hint')
                </p>
            </x-admin::form.control-group>

            <!-- PHP Version -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.php-version')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="php_version"
                    name="php_version"
                    :value="old('php_version', $version?->php_version)"
                    :label="trans('marketplace::app.developer.versions.form.php-version')"
                    :placeholder="trans('marketplace::app.developer.versions.form.php-version-placeholder')"
                />

                <x-admin::form.control-group.error control-name="php_version" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.php-version-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Dependencies -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.versions.form.dependencies')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="dependencies"
                    name="dependencies"
                    :value="old('dependencies', $version?->dependencies ? json_encode($version->dependencies, JSON_PRETTY_PRINT) : '')"
                    rows="5"
                    :label="trans('marketplace::app.developer.versions.form.dependencies')"
                    :placeholder="trans('marketplace::app.developer.versions.form.dependencies-placeholder')"
                />

                <x-admin::form.control-group.error control-name="dependencies" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.form.dependencies-hint')
                </p>
            </x-admin::form.control-group>
        </div>

        @if($version)
            <!-- Package Upload -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.developer.versions.form.package-file')
                </p>

                @if($version->file_path)
                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-950">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="icon-package text-3xl text-green-600 dark:text-green-400"></span>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        @lang('marketplace::app.developer.versions.form.package-uploaded')
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ number_format($version->file_size / 1024 / 1024, 2) }} MB
                                        @if($version->checksum)
                                            • MD5: {{ Str::limit($version->checksum, 16) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a
                                href="{{ route('developer.marketplace.versions.download_package', $version->id) }}"
                                class="secondary-button"
                            >
                                @lang('marketplace::app.developer.versions.form.download-package')
                            </a>
                        </div>
                    </div>
                @endif

                @if($version->status !== 'approved')
                    <div id="package-upload-section">
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('marketplace::app.developer.versions.form.upload-package')
                            </x-admin::form.control-group.label>

                            <input
                                type="file"
                                id="package"
                                name="package"
                                accept=".zip"
                                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.versions.form.upload-package-hint')
                            </p>
                        </x-admin::form.control-group>

                        <button
                            type="button"
                            id="upload-package-btn"
                            class="secondary-button mt-4"
                        >
                            @lang('marketplace::app.developer.versions.form.upload-btn')
                        </button>
                    </div>
                @else
                    <p class="text-sm text-orange-600 dark:text-orange-400">
                        @lang('marketplace::app.developer.versions.form.package-locked')
                    </p>
                @endif
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.versions.form.left.after', ['extension' => $extension, 'version' => $version]) !!}

    {!! view_render_event('marketplace.developer.versions.form.right.before', ['extension' => $extension, 'version' => $version]) !!}

    <!-- Right Panel -->
    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
        <!-- Extension Info -->
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.developer.versions.form.extension-info')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <div class="flex flex-col gap-3">
                    <!-- Extension Name -->
                    <div class="flex items-center gap-3">
                        @if($extension->logo)
                            <img
                                src="{{ Storage::url($extension->logo) }}"
                                alt="{{ $extension->name }}"
                                class="h-12 w-12 rounded-lg object-cover"
                            />
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                <span class="icon-package text-xl text-gray-400 dark:text-gray-600"></span>
                            </div>
                        @endif

                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $extension->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ ucfirst($extension->type) }}
                            </p>
                        </div>
                    </div>

                    <!-- Extension Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.versions.form.extension-status')
                        </span>
                        <span class="rounded-full px-2 py-1 text-xs font-medium
                            @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                            @elseif($extension->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                            @elseif($extension->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                            @endif">
                            {{ ucfirst($extension->status) }}
                        </span>
                    </div>

                    <!-- Total Versions -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.versions.form.total-versions')
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $extension->versions()->count() }}
                        </span>
                    </div>
                </div>
            </x-slot>
        </x-admin::accordion>

        @if($version)
            <!-- Version Status -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.developer.versions.form.version-status')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-3">
                        <!-- Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.versions.form.status')
                            </span>
                            <span class="rounded-full px-2 py-1 text-xs font-medium
                                @if($version->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($version->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @elseif($version->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                @endif">
                                {{ ucfirst($version->status) }}
                            </span>
                        </div>

                        <!-- Created At -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.versions.form.created-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $version->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Updated At -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.versions.form.updated-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $version->updated_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Quick Actions -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.developer.versions.form.quick-actions')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-2">
                        <a
                            href="{{ route('developer.marketplace.versions.index', $extension->id) }}"
                            class="secondary-button text-center"
                        >
                            @lang('marketplace::app.developer.versions.form.view-all-versions')
                        </a>

                        @if($version->file_path && !$version->submissions()->where('status', 'pending')->exists())
                            <form
                                action="{{ route('developer.marketplace.submissions.submit', $extension->id) }}"
                                method="POST"
                            >
                                @csrf
                                <input type="hidden" name="version_id" value="{{ $version->id }}" />
                                <button
                                    type="submit"
                                    class="secondary-button w-full text-center"
                                >
                                    @lang('marketplace::app.developer.versions.form.submit-for-review')
                                </button>
                            </form>
                        @elseif($version->submissions()->where('status', 'pending')->exists())
                            <p class="text-center text-xs text-orange-600 dark:text-orange-400">
                                @lang('marketplace::app.developer.versions.form.pending-review')
                            </p>
                        @endif
                    </div>
                </x-slot>
            </x-admin::accordion>
        @endif

        <!-- Guidelines -->
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.developer.versions.form.guidelines')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <div class="flex flex-col gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <p>@lang('marketplace::app.developer.versions.form.guideline-1')</p>
                    <p>@lang('marketplace::app.developer.versions.form.guideline-2')</p>
                    <p>@lang('marketplace::app.developer.versions.form.guideline-3')</p>
                    <p>@lang('marketplace::app.developer.versions.form.guideline-4')</p>
                </div>
            </x-slot>
        </x-admin::accordion>
    </div>

    {!! view_render_event('marketplace.developer.versions.form.right.after', ['extension' => $extension, 'version' => $version]) !!}
</div>

@if($version)
    @pushOnce('scripts')
        <script type="module">
            const uploadBtn = document.getElementById('upload-package-btn');
            const packageInput = document.getElementById('package');

            if (uploadBtn && packageInput) {
                uploadBtn.addEventListener('click', async function() {
                    const file = packageInput.files[0];

                    if (!file) {
                        alert('@lang('marketplace::app.developer.versions.form.select-package-first')');
                        return;
                    }

                    if (file.size > 51200 * 1024) {
                        alert('@lang('marketplace::app.developer.versions.form.package-too-large')');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('package', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    uploadBtn.disabled = true;
                    uploadBtn.textContent = '@lang('marketplace::app.developer.versions.form.uploading')';

                    try {
                        const response = await fetch('{{ route('developer.marketplace.versions.upload_package', $version->id) }}', {
                            method: 'POST',
                            body: formData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message);
                            uploadBtn.disabled = false;
                            uploadBtn.textContent = '@lang('marketplace::app.developer.versions.form.upload-btn')';
                        }
                    } catch (error) {
                        alert('@lang('marketplace::app.developer.versions.form.upload-error')');
                        uploadBtn.disabled = false;
                        uploadBtn.textContent = '@lang('marketplace::app.developer.versions.form.upload-btn')';
                    }
                });
            }
        </script>
    @endPushOnce
@endif
