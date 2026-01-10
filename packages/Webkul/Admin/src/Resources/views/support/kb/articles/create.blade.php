<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.kb.create.title')
        </x-slot>

        <x-admin::form :action="route('admin.support.kb.articles.store')" method="POST">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="support.kb.articles.create" />
                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.support.kb.create.title')
                    </div>
                </div>

                <button type="submit" class="primary-button">
                    @lang('admin::app.support.kb.create.save-btn')
                </button>
            </div>

            <div class="mt-3.5">
                <div
                    class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.support.kb.create.general')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.support.kb.create.article-title')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="text" name="title" rules="required" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.support.kb.create.content')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="textarea" name="content" id="content"
                            rules="required" :tinymce="true" rows="10" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.support.kb.create.category')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="select" name="category_id" rules="required">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.support.kb.create.status')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="select" name="status" rules="required">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.support.kb.create.visibility')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="select" name="visibility" rules="required">
                            <option value="public">Public</option>
                            <option value="internal">Internal</option>
                            <option value="customer_portal">Customer Portal</option>
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>
                </div>
            </div>
        </x-admin::form>
</x-admin::layouts>