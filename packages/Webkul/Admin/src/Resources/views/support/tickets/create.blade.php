<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.tickets.create.title')
        </x-slot>

        <x-admin::form :action="route('admin.support.tickets.store')" method="POST" enctype="multipart/form-data">
            <!-- Header -->
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="support.tickets.create" />

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.support.tickets.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <!-- Button removed -->
                </div>
            </div>

            <!-- Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Section -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div
                        class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.support.tickets.create.general')
                        </p>

                        <!-- Subject -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.support.tickets.create.subject')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" name="subject" rules="required"
                                :value="old('subject')"
                                :placeholder="trans('admin::app.support.tickets.create.subject')" />

                            <x-admin::form.control-group.error control-name="subject" />
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.support.tickets.create.description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="textarea" name="description" rules="required"
                                :value="old('description')"
                                :placeholder="trans('admin::app.support.tickets.create.description')" rows="5" />

                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>

                        <!-- Priority -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.support.tickets.create.priority')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="select" name="priority" rules="required"
                                :value="old('priority', 'normal')">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="priority" />
                        </x-admin::form.control-group>

                        <!-- Category -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.support.tickets.create.category')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="select" name="category_id"
                                :value="old('category_id')">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="category_id" />
                        </x-admin::form.control-group>

                        <!-- Customer -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.support.tickets.create.customer')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="select" name="customer_id" rules="required"
                                :value="old('customer_id')">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="customer_id" />
                        </x-admin::form.control-group>

                        <!-- Assigned To -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.support.tickets.create.assigned-to')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="select" name="assigned_to"
                                :value="old('assigned_to')">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="assigned_to" />
                        </x-admin::form.control-group>

                        <div class="flex items-center justify-end gap-x-2.5 mt-4">
                            <button type="submit" class="primary-button">
                                @lang('admin::app.support.tickets.create.save-btn')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin::form>
</x-admin::layouts>