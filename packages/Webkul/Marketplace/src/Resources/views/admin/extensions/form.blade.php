<div class="flex gap-4">
    <!-- Left Section -->
    <div class="flex-1 flex flex-col gap-8">

        <!-- General Information -->
        <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
            <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                @lang('marketplace::app.admin.extensions.form.general-info')
            </p>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.extensions.form.name')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="name"
                    rules="required"
                    :value="old('name') ?? $extension?->name"
                    :label="trans('marketplace::app.admin.extensions.form.name')"
                    :placeholder="trans('marketplace::app.admin.extensions.form.name')"
                />

                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.extensions.form.slug')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="slug"
                    rules="required"
                    :value="old('slug') ?? $extension?->slug"
                    :label="trans('marketplace::app.admin.extensions.form.slug')"
                    :placeholder="trans('marketplace::app.admin.extensions.form.slug')"
                />

                <x-admin::form.control-group.error control-name="slug" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.extensions.form.description')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    name="description"
                    :value="old('description') ?? $extension?->description"
                    :label="trans('marketplace::app.admin.extensions.form.description')"
                    :placeholder="trans('marketplace::app.admin.extensions.form.description')"
                />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.extensions.form.type')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="type"
                    rules="required"
                    :value="old('type') ?? $extension?->type"
                    :label="trans('marketplace::app.admin.extensions.form.type')"
                >
                    <option value="plugin">Plugin</option>
                    <option value="theme">Theme</option>
                    <option value="integration">Integration</option>
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="type" />
            </x-admin::form.control-group>

             <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.extensions.form.category')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="category_id"
                    :value="old('category_id') ?? $extension?->category_id"
                    :label="trans('marketplace::app.admin.extensions.form.category')"
                >
                    <option value="">@lang('marketplace::app.admin.extensions.index.datagrid.all-categories')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.extensions.form.price')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="price"
                    rules="required|numeric|min:0"
                    :value="old('price') ?? $extension?->price ?? 0"
                    :label="trans('marketplace::app.admin.extensions.form.price')"
                    :placeholder="trans('marketplace::app.admin.extensions.form.price')"
                />

                <x-admin::form.control-group.error control-name="price" />
            </x-admin::form.control-group>
        </div>

        <!-- Links -->
        <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
             <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                @lang('marketplace::app.admin.extensions.form.links')
            </p>

            <div class="flex gap-4">
                <x-admin::form.control-group class="flex-1 mb-2.5">
                    <x-admin::form.control-group.label>
                        @lang('marketplace::app.admin.extensions.form.documentation-url')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="documentation_url"
                        :value="old('documentation_url') ?? $extension?->documentation_url"
                        :label="trans('marketplace::app.admin.extensions.form.documentation-url')"
                        :placeholder="trans('marketplace::app.admin.extensions.form.documentation-url')"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group class="flex-1 mb-2.5">
                    <x-admin::form.control-group.label>
                        @lang('marketplace::app.admin.extensions.form.demo-url')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="demo_url"
                        :value="old('demo_url') ?? $extension?->demo_url"
                        :label="trans('marketplace::app.admin.extensions.form.demo-url')"
                        :placeholder="trans('marketplace::app.admin.extensions.form.demo-url')"
                    />
                </x-admin::form.control-group>
            </div>
            
             <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.extensions.form.support-email')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="email"
                    name="support_email"
                    :value="old('support_email') ?? $extension?->support_email"
                    :label="trans('marketplace::app.admin.extensions.form.support-email')"
                    :placeholder="trans('marketplace::app.admin.extensions.form.support-email')"
                />
            </x-admin::form.control-group>
        </div>
    </div>

    <!-- Right Section -->
    <div class="flex flex-col gap-2 w-[360px] max-w-full">
        <!-- Settings -->
         <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
            <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                @lang('marketplace::app.admin.acl.settings')
            </p>

             <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.extensions.form.status')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="status"
                    rules="required"
                    :value="old('status') ?? $extension?->status ?? 'draft'"
                    :label="trans('marketplace::app.admin.extensions.form.status')"
                >
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="disabled">Disabled</option>
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="status" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-2.5">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.extensions.form.featured')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="switch"
                    name="featured"
                    value="1"
                    :checked="(boolean) (old('featured') ?? $extension?->featured ?? 0)"
                    :label="trans('marketplace::app.admin.extensions.form.featured')"
                />
            </x-admin::form.control-group>
        </div>

        <!-- Logo -->
        <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
            <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                @lang('marketplace::app.admin.extensions.form.logo')
            </p>

            <x-admin::media.images
                name="logo"
                :uploaded-images="$extension?->logo ? [['id' => 'logo', 'url' => Storage::url($extension->logo)]] : []"
            />
        </div>

    </div>
</div>
