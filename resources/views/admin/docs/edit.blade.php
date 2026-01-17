<x-admin::layouts>
    <x-slot:title>
        Edit Documentation Article
    </x-slot>

    <x-admin::form :action="route('admin.docs.update', $article->id)" method="PUT">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="admin.docs.edit" :entity="$article" />
                <div class="text-xl font-bold dark:text-white">
                    Edit Documentation Article
                </div>
            </div>

            <button type="submit" class="primary-button">
                Update Article
            </button>
        </div>

        <div class="mt-3.5">
            <!-- General Information -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    General Information
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        Title
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="title"
                        placeholder="Enter article title"
                        :value="old('title', $article->title)"
                        rules="required"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Slug
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="slug"
                        placeholder="URL-friendly version of title (auto-generated if empty)"
                        :value="old('slug', $article->slug)"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Excerpt
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="excerpt"
                        placeholder="Brief summary of the article (shown in listings)"
                        :value="old('excerpt', $article->excerpt)"
                        rows="3"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        Content
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="content"
                        id="content"
                        placeholder="Main article content with rich text formatting"
                        :value="old('content', $article->content)"
                        rules="required"
                        :tinymce="true"
                        rows="15"
                    />
                </x-admin::form.control-group>
            </div>

            <!-- Organization -->
            <div class="mt-3.5 box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Organization
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Category
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="category_id"
                        :value="old('category_id', $article->category_id)"
                    >
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Article Type
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="type"
                        :value="old('type', $article->type)"
                    >
                        <option value="">Select Type</option>
                        <option value="getting_started">Getting Started</option>
                        <option value="api_doc">API Documentation</option>
                        <option value="feature_guide">Feature Guide</option>
                        <option value="troubleshooting">Troubleshooting</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Difficulty Level
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="difficulty_level"
                        :value="old('difficulty_level', $article->difficulty_level)"
                    >
                        <option value="">Select Difficulty</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Sort Order
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        name="sort_order"
                        placeholder="0"
                        :value="old('sort_order', $article->sort_order)"
                        min="0"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Lower numbers appear first
                    </p>
                </x-admin::form.control-group>
            </div>

            <!-- Video Embed -->
            <div class="mt-3.5 box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Video Embed
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Video URL
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="video_url"
                        placeholder="https://www.youtube.com/watch?v=..."
                        :value="old('video_url', $article->video_url)"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Supports YouTube and Vimeo URLs
                    </p>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Video Type
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="video_type"
                        :value="old('video_type', $article->video_type)"
                    >
                        <option value="">Auto-detect</option>
                        <option value="youtube">YouTube</option>
                        <option value="vimeo">Vimeo</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>
            </div>

            <!-- Publication Settings -->
            <div class="mt-3.5 box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Publication Settings
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Status
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="status"
                        :value="old('status', $article->status)"
                    >
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Visibility
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="visibility"
                        :value="old('visibility', $article->visibility)"
                    >
                        <option value="public">Public</option>
                        <option value="internal">Internal</option>
                        <option value="private">Private</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Featured
                    </x-admin::form.control-group.label>

                    <div class="flex items-center gap-2">
                        <x-admin::form.control-group.control
                            type="checkbox"
                            name="featured"
                            value="1"
                            :checked="old('featured', $article->featured)"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Mark as featured article
                        </span>
                    </div>
                </x-admin::form.control-group>
            </div>

            <!-- SEO Settings -->
            <div class="mt-3.5 box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    SEO Settings (Optional)
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Meta Title
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="meta_title"
                        placeholder="Custom title for search engines"
                        :value="old('meta_title', $article->meta_title)"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Meta Description
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="meta_description"
                        placeholder="Description for search engine results"
                        :value="old('meta_description', $article->meta_description)"
                        rows="2"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        Meta Keywords
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="meta_keywords"
                        placeholder="Comma-separated keywords"
                        :value="old('meta_keywords', $article->meta_keywords)"
                    />
                </x-admin::form.control-group>
            </div>

            <!-- Article Stats -->
            @if($article->id)
            <div class="mt-3.5 box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Article Statistics
                </p>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Views</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $article->view_count ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Helpful</p>
                        <p class="text-2xl font-bold text-green-600">{{ $article->helpful_count ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Not Helpful</p>
                        <p class="text-2xl font-bold text-red-600">{{ $article->not_helpful_count ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Reading Time</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $article->reading_time_minutes ?? 0 }} min</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-admin::form>
</x-admin::layouts>
