<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.categories.index.title')
        </x-slot>

        <div class="flex flex-col gap-4">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.marketplace.categories.index.breadcrumbs.before') !!}

                    <x-admin::breadcrumbs name="marketplace.categories" />

                    {!! view_render_event('admin.marketplace.categories.index.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.categories.index.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.marketplace.categories.index.create_button.before') !!}

                        @if (bouncer()->hasPermission('marketplace.categories.create'))
                            <a href="{{ route('admin.marketplace.categories.create') }}" class="primary-button">
                                @lang('marketplace::app.admin.categories.index.create-btn')
                            </a>
                        @endif

                        {!! view_render_event('admin.marketplace.categories.index.create_button.after') !!}
                    </div>
                </div>
            </div>

            {!! view_render_event('admin.marketplace.categories.index.view_switcher.before') !!}

            <!-- View Switcher -->
            <div class="flex items-center gap-2">
                <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                    :class="viewMode === 'tree' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-800'"
                    @click="viewMode = 'tree'">
                    @lang('marketplace::app.admin.categories.index.tree-view')
                </button>
                <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                    :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-800'"
                    @click="viewMode = 'table'">
                    @lang('marketplace::app.admin.categories.index.table-view')
                </button>
            </div>

            {!! view_render_event('admin.marketplace.categories.index.view_switcher.after') !!}

            {!! view_render_event('admin.marketplace.categories.index.tree.before') !!}

            <!-- Tree View with Drag & Drop -->
            <div v-show="viewMode === 'tree'"
                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <v-category-tree></v-category-tree>
            </div>

            {!! view_render_event('admin.marketplace.categories.index.tree.after') !!}

            {!! view_render_event('admin.marketplace.categories.index.datagrid.before') !!}

            <!-- Table View -->
            <div v-show="viewMode === 'table'">
                <x-admin::datagrid :src="route('admin.marketplace.categories.index')">
                    <!-- DataGrid Shimmer -->
                    <x-admin::shimmer.datagrid />
                </x-admin::datagrid>
            </div>

            {!! view_render_event('admin.marketplace.categories.index.datagrid.after') !!}
        </div>

        @pushOnce('scripts')
            <script type="text/x-template" id="v-category-tree-template">
                <div>
                    <div v-if="isLoading" class="flex items-center justify-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">
                            @lang('marketplace::app.admin.categories.index.loading')
                        </div>
                    </div>

                    <div v-else-if="treeData.length === 0" class="flex flex-col items-center justify-center py-12">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            @lang('marketplace::app.admin.categories.index.no-categories')
                        </div>
                        @if (bouncer()->hasPermission('marketplace.categories.create'))
                            <a href="{{ route('admin.marketplace.categories.create') }}" class="primary-button">
                                @lang('marketplace::app.admin.categories.index.create-first-category')
                            </a>
                        @endif
                    </div>

                    <div v-else class="category-tree">
                        <v-category-node
                            v-for="category in treeData"
                            :key="category.id"
                            :category="category"
                            :level="0"
                            @reorder="handleReorder"
                        ></v-category-node>
                    </div>

                    <div v-if="hasChanges" class="mt-4 flex items-center gap-2 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <span class="text-sm text-blue-700 dark:text-blue-400">
                            @lang('marketplace::app.admin.categories.index.reorder-changes-pending')
                        </span>
                        <button
                            @click="saveReorder"
                            :disabled="isSaving"
                            class="primary-button ml-auto"
                        >
                            <span v-if="isSaving">@lang('marketplace::app.admin.categories.index.saving')</span>
                            <span v-else>@lang('marketplace::app.admin.categories.index.save-order')</span>
                        </button>
                        <button
                            @click="cancelReorder"
                            :disabled="isSaving"
                            class="secondary-button"
                        >
                            @lang('marketplace::app.admin.categories.index.cancel')
                        </button>
                    </div>
                </div>
            </script>

            <script type="text/x-template" id="v-category-node-template">
                <div class="category-node" :style="{ paddingLeft: (level * 24) + 'px' }">
                    <div
                        class="category-item flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900 mb-2 hover:bg-gray-50 dark:hover:bg-gray-950 transition-colors"
                        :class="{ 'opacity-50': isDragging }"
                        draggable="true"
                        @dragstart="handleDragStart"
                        @dragend="handleDragEnd"
                        @dragover.prevent="handleDragOver"
                        @drop="handleDrop"
                    >
                        <!-- Drag Handle -->
                        <span class="icon-drag cursor-move text-xl text-gray-400 dark:text-gray-600"></span>

                        <!-- Expand/Collapse -->
                        <button
                            v-if="category.children && category.children.length > 0"
                            @click="toggleExpanded"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <span v-if="isExpanded" class="icon-arrow-down text-lg"></span>
                            <span v-else class="icon-arrow-right text-lg"></span>
                        </button>
                        <span v-else class="w-5"></span>

                        <!-- Icon -->
                        <span v-if="category.icon" class="text-2xl" v-html="category.icon"></span>
                        <span v-else class="icon-folder text-2xl text-gray-400"></span>

                        <!-- Category Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-800 dark:text-white">@{{ category.name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">(@{{ category.slug }})</span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>@{{ category.extensions_count || 0 }} @lang('marketplace::app.admin.categories.index.extensions')</span>
                                <span v-if="category.children">@{{ category.children.length }} @lang('marketplace::app.admin.categories.index.subcategories')</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1">
                            @if (bouncer()->hasPermission('marketplace.categories.edit'))
                                <a
                                    :href="`{{ route('admin.marketplace.categories.edit', '') }}/${category.id}`"
                                    class="icon-edit cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    title="@lang('marketplace::app.admin.categories.index.datagrid.edit')"
                                ></a>
                            @endif

                            @if (bouncer()->hasPermission('marketplace.categories.delete'))
                                <span
                                    @click="confirmDelete"
                                    class="icon-delete cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    title="@lang('marketplace::app.admin.categories.index.datagrid.delete')"
                                ></span>
                            @endif
                        </div>
                    </div>

                    <!-- Children -->
                    <div v-if="isExpanded && category.children && category.children.length > 0">
                        <v-category-node
                            v-for="child in category.children"
                            :key="child.id"
                            :category="child"
                            :level="level + 1"
                            @reorder="$emit('reorder', $event)"
                        ></v-category-node>
                    </div>
                </div>
            </script>

            <script type="module">
                app.component('v-category-tree', {
                    template: '#v-category-tree-template',

                    data() {
                        return {
                            treeData: [],
                            isLoading: true,
                            hasChanges: false,
                            isSaving: false,
                            originalData: null,
                        };
                    },

                    mounted() {
                        this.loadTreeData();
                    },

                    methods: {
                        async loadTreeData() {
                            this.isLoading = true;

                            try {
                                const response = await this.$axios.get('{{ route('admin.marketplace.categories.tree_data') }}');
                                this.treeData = response.data.data;
                                this.originalData = JSON.parse(JSON.stringify(this.treeData));
                            } catch (error) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || '@lang('marketplace::app.admin.categories.index.tree-data-failed')'
                                });
                            } finally {
                                this.isLoading = false;
                            }
                        },

                        handleReorder(data) {
                            this.hasChanges = true;
                        },

                        async saveReorder() {
                            this.isSaving = true;

                            try {
                                const categories = this.flattenTree(this.treeData);

                                const response = await this.$axios.post('{{ route('admin.marketplace.categories.reorder') }}', {
                                    categories: categories
                                });

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });

                                this.hasChanges = false;
                                this.originalData = JSON.parse(JSON.stringify(this.treeData));
                            } catch (error) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || '@lang('marketplace::app.admin.categories.index.reorder-failed')'
                                });
                            } finally {
                                this.isSaving = false;
                            }
                        },

                        cancelReorder() {
                            this.treeData = JSON.parse(JSON.stringify(this.originalData));
                            this.hasChanges = false;
                        },

                        flattenTree(categories, parentId = null, result = []) {
                            categories.forEach((category, index) => {
                                result.push({
                                    id: category.id,
                                    sort_order: index,
                                    parent_id: parentId
                                });

                                if (category.children && category.children.length > 0) {
                                    this.flattenTree(category.children, category.id, result);
                                }
                            });

                            return result;
                        },
                    },
                });

                app.component('v-category-node', {
                    template: '#v-category-node-template',

                    props: ['category', 'level'],

                    data() {
                        return {
                            isExpanded: true,
                            isDragging: false,
                        };
                    },

                    methods: {
                        toggleExpanded() {
                            this.isExpanded = !this.isExpanded;
                        },

                        handleDragStart(event) {
                            this.isDragging = true;
                            event.dataTransfer.effectAllowed = 'move';
                            event.dataTransfer.setData('categoryId', this.category.id);
                        },

                        handleDragEnd(event) {
                            this.isDragging = false;
                        },

                        handleDragOver(event) {
                            event.dataTransfer.dropEffect = 'move';
                        },

                        handleDrop(event) {
                            event.preventDefault();
                            const draggedId = parseInt(event.dataTransfer.getData('categoryId'));

                            if (draggedId !== this.category.id) {
                                this.$emit('reorder', {
                                    draggedId: draggedId,
                                    targetId: this.category.id
                                });
                            }
                        },

                        async confirmDelete() {
                            if (this.category.children && this.category.children.length > 0) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: '@lang('marketplace::app.admin.categories.index.has-children-error')'
                                });
                                return;
                            }

                            if (this.category.extensions_count > 0) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: '@lang('marketplace::app.admin.categories.index.has-extensions-error')'
                                });
                                return;
                            }

                            this.$emitter.emit('open-confirm-modal', {
                                agree: () => {
                                    this.deleteCategory();
                                }
                            });
                        },

                        async deleteCategory() {
                            try {
                                const response = await this.$axios.delete(`{{ route('admin.marketplace.categories.destroy', '') }}/${this.category.id}`);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });

                                window.location.reload();
                            } catch (error) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || '@lang('marketplace::app.admin.categories.index.delete-failed')'
                                });
                            }
                        },
                    },
                });
            </script>
        @endPushOnce
</x-admin::layouts>

<script>
    let viewMode = 'tree';
</script>