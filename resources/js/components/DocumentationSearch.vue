<template>
    <div class="documentation-search">
        <div class="search-input-wrapper">
            <input
                ref="searchInput"
                type="text"
                v-model="query"
                @input="handleInput"
                @focus="showResults = true"
                @keydown.down="highlightNext"
                @keydown.up="highlightPrevious"
                @keydown.enter="navigateToHighlighted"
                @keydown.escape="closeResults"
                placeholder="Search documentation..."
                autocomplete="off"
                class="search-input"
            >
            <div v-if="loading" class="search-spinner"></div>
        </div>

        <transition name="fade">
            <div v-if="showResults && (query.length >= 2 || popularArticles.length)" class="search-results">
                <!-- Loading State -->
                <div v-if="loading" class="search-loading">
                    <div class="loading-spinner"></div>
                    <span>Searching...</span>
                </div>

                <!-- Search Results -->
                <div v-else-if="query.length >= 2">
                    <div v-if="searchResults.length > 0" class="results-section">
                        <div class="results-header">
                            <span class="results-count">{{ searchResults.length }} results</span>
                            <span class="results-query">for "{{ query }}"</span>
                        </div>
                        <div class="results-list">
                            <a
                                v-for="(result, index) in searchResults"
                                :key="result.id"
                                :href="result.url"
                                class="result-item"
                                :class="{ 'highlighted': highlightedIndex === index }"
                                @mouseenter="highlightedIndex = index"
                                @click="closeResults"
                            >
                                <div class="result-icon">
                                    <svg v-if="result.type === 'getting-started'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                    </svg>
                                    <svg v-else-if="result.type === 'api-doc'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                    <svg v-else-if="result.type === 'feature-guide'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                    </svg>
                                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                </div>
                                <div class="result-content">
                                    <div class="result-title">{{ result.title }}</div>
                                    <div class="result-excerpt">{{ result.excerpt }}</div>
                                    <div class="result-meta">
                                        <span v-if="result.category" class="result-category">{{ result.category.name }}</span>
                                        <span v-if="result.reading_time_minutes" class="result-time">{{ result.reading_time_minutes }} min read</span>
                                        <span v-if="result.has_video" class="result-video">Video</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div v-else class="no-results">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <p>No results found for "{{ query }}"</p>
                        <p class="no-results-suggestion">Try different keywords or check popular articles below</p>
                    </div>
                </div>

                <!-- Popular Articles (shown when query is empty) -->
                <div v-else-if="popularArticles.length > 0" class="popular-section">
                    <div class="results-header">
                        <span class="results-title">Popular Articles</span>
                    </div>
                    <div class="results-list">
                        <a
                            v-for="(article, index) in popularArticles"
                            :key="article.id"
                            :href="article.url"
                            class="result-item popular-item"
                            :class="{ 'highlighted': highlightedIndex === index }"
                            @mouseenter="highlightedIndex = index"
                            @click="closeResults"
                        >
                            <div class="result-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                </svg>
                            </div>
                            <div class="result-content">
                                <div class="result-title">{{ article.title }}</div>
                                <div class="result-meta">
                                    <span class="result-views">{{ article.view_count }} views</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Search Tips -->
                <div v-if="query.length < 2 && query.length > 0" class="search-tips">
                    <div class="tip-header">Search Tips</div>
                    <ul class="tip-list">
                        <li>Type at least 2 characters to search</li>
                        <li>Use specific keywords for better results</li>
                        <li>Check popular articles below</li>
                    </ul>
                </div>

                <!-- Footer -->
                <div v-if="query.length >= 2" class="search-footer">
                    <span>Use <kbd>↑</kbd> <kbd>↓</kbd> to navigate</span>
                    <span><kbd>Enter</kbd> to select</span>
                    <span><kbd>Esc</kbd> to close</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'DocumentationSearch',
    data() {
        return {
            query: '',
            searchResults: [],
            popularArticles: [],
            loading: false,
            showResults: false,
            highlightedIndex: -1,
            searchTimeout: null,
        };
    },
    mounted() {
        this.fetchPopularArticles();

        // Close results when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
    },
    methods: {
        handleInput() {
            // Debounce search
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            if (this.query.length >= 2) {
                this.loading = true;
                this.highlightedIndex = -1;

                this.searchTimeout = setTimeout(() => {
                    this.performSearch();
                }, 300);
            } else {
                this.searchResults = [];
                this.loading = false;
            }
        },
        async performSearch() {
            try {
                const response = await axios.post('/api/docs/search', {
                    query: this.query,
                    limit: 10,
                });

                if (response.data.success) {
                    this.searchResults = response.data.data.results;
                } else {
                    this.searchResults = [];
                }
            } catch (error) {
                if (error.response && error.response.status === 500) {
                    // Database not available, show demo results
                    this.searchResults = this.getDemoResults();
                } else {
                    console.error('Search error:', error);
                    this.searchResults = [];
                }
            } finally {
                this.loading = false;
            }
        },
        async fetchPopularArticles() {
            try {
                const response = await axios.get('/api/docs/popular', {
                    params: {
                        limit: 5,
                    },
                });

                if (response.data.success) {
                    this.popularArticles = response.data.data.results;
                }
            } catch (error) {
                if (error.response && error.response.status === 500) {
                    // Database not available, show demo articles
                    this.popularArticles = this.getDemoPopularArticles();
                } else {
                    console.error('Failed to fetch popular articles:', error);
                    this.popularArticles = [];
                }
            }
        },
        highlightNext() {
            const maxIndex = this.query.length >= 2
                ? this.searchResults.length - 1
                : this.popularArticles.length - 1;

            if (this.highlightedIndex < maxIndex) {
                this.highlightedIndex++;
            } else {
                this.highlightedIndex = 0;
            }
        },
        highlightPrevious() {
            const maxIndex = this.query.length >= 2
                ? this.searchResults.length - 1
                : this.popularArticles.length - 1;

            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else {
                this.highlightedIndex = maxIndex;
            }
        },
        navigateToHighlighted() {
            if (this.highlightedIndex >= 0) {
                const results = this.query.length >= 2 ? this.searchResults : this.popularArticles;
                const result = results[this.highlightedIndex];

                if (result && result.url) {
                    window.location.href = result.url;
                }
            }
        },
        closeResults() {
            this.showResults = false;
        },
        handleClickOutside(event) {
            if (this.$el && !this.$el.contains(event.target)) {
                this.closeResults();
            }
        },
        getDemoResults() {
            return [
                {
                    id: 1,
                    title: 'Getting Started Guide',
                    slug: 'getting-started',
                    excerpt: 'Learn how to set up and configure the CRM in minutes.',
                    type: 'getting-started',
                    category: { name: 'Getting Started' },
                    reading_time_minutes: 5,
                    has_video: true,
                    view_count: 150,
                    helpful_count: 45,
                    url: '/docs/1',
                },
                {
                    id: 2,
                    title: 'Managing Leads',
                    slug: 'managing-leads',
                    excerpt: 'Complete guide to lead management and conversion.',
                    type: 'feature-guide',
                    category: { name: 'Features' },
                    reading_time_minutes: 8,
                    has_video: false,
                    view_count: 89,
                    helpful_count: 32,
                    url: '/docs/2',
                },
            ];
        },
        getDemoPopularArticles() {
            return [
                {
                    id: 1,
                    title: 'Getting Started Guide',
                    slug: 'getting-started',
                    excerpt: 'Learn how to set up and configure the CRM.',
                    view_count: 150,
                    url: '/docs/1',
                },
                {
                    id: 2,
                    title: 'Managing Contacts',
                    slug: 'managing-contacts',
                    excerpt: 'Organize and manage your contacts efficiently.',
                    view_count: 120,
                    url: '/docs/2',
                },
                {
                    id: 3,
                    title: 'Creating Quotes',
                    slug: 'creating-quotes',
                    excerpt: 'Create professional quotes for your prospects.',
                    view_count: 95,
                    url: '/docs/3',
                },
            ];
        },
    },
};
</script>

<style scoped>
.documentation-search {
    position: relative;
    width: 100%;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem;
    padding-right: 2.5rem;
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 0.5rem;
    font-family: inherit;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: var(--surface-color, #FFFFFF);
    color: var(--text-main, #1E293B);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color, #4F46E5);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.search-spinner {
    position: absolute;
    right: 0.75rem;
    width: 16px;
    height: 16px;
    border: 2px solid var(--border-color, #E2E8F0);
    border-top-color: var(--primary-color, #4F46E5);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.search-results {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: var(--surface-color, #FFFFFF);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-height: 500px;
    overflow-y: auto;
    z-index: 1000;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s, transform 0.2s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.search-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 2rem;
    color: var(--text-secondary, #64748B);
}

.loading-spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--border-color, #E2E8F0);
    border-top-color: var(--primary-color, #4F46E5);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

.results-section, .popular-section {
    padding: 0.5rem 0;
}

.results-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color, #E2E8F0);
    background: var(--bg-color, #F8FAFC);
}

.results-count, .results-query, .results-title {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-secondary, #64748B);
}

.results-query {
    color: var(--text-main, #1E293B);
}

.results-list {
    max-height: 350px;
    overflow-y: auto;
}

.result-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.15s;
    border-left: 3px solid transparent;
}

.result-item:hover,
.result-item.highlighted {
    background-color: var(--bg-color, #F8FAFC);
    border-left-color: var(--primary-color, #4F46E5);
}

.result-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary, #64748B);
    margin-top: 0.125rem;
}

.result-content {
    flex: 1;
    min-width: 0;
}

.result-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-main, #1E293B);
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.result-excerpt {
    font-size: 0.8125rem;
    color: var(--text-secondary, #64748B);
    margin-bottom: 0.5rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.5;
}

.result-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.result-category,
.result-time,
.result-video,
.result-views {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    background: var(--bg-color, #F8FAFC);
    color: var(--text-secondary, #64748B);
}

.result-video {
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary-color, #4F46E5);
}

.popular-item .result-icon {
    color: var(--primary-color, #4F46E5);
}

.no-results {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
    color: var(--text-secondary, #64748B);
}

.no-results svg {
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-results p {
    margin: 0.5rem 0;
    font-size: 0.875rem;
}

.no-results-suggestion {
    font-size: 0.8125rem !important;
    color: var(--text-secondary, #64748B);
}

.search-tips {
    padding: 1.5rem;
}

.tip-header {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-main, #1E293B);
    margin-bottom: 0.75rem;
}

.tip-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tip-list li {
    font-size: 0.8125rem;
    color: var(--text-secondary, #64748B);
    padding: 0.375rem 0;
    padding-left: 1.25rem;
    position: relative;
}

.tip-list li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--primary-color, #4F46E5);
}

.search-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--border-color, #E2E8F0);
    background: var(--bg-color, #F8FAFC);
    font-size: 0.75rem;
    color: var(--text-secondary, #64748B);
}

.search-footer kbd {
    display: inline-block;
    padding: 0.125rem 0.375rem;
    font-size: 0.6875rem;
    font-family: inherit;
    background: var(--surface-color, #FFFFFF);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 0.25rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Scrollbar */
.search-results::-webkit-scrollbar,
.results-list::-webkit-scrollbar {
    width: 6px;
}

.search-results::-webkit-scrollbar-track,
.results-list::-webkit-scrollbar-track {
    background: transparent;
}

.search-results::-webkit-scrollbar-thumb,
.results-list::-webkit-scrollbar-thumb {
    background: var(--border-color, #E2E8F0);
    border-radius: 3px;
}

.search-results::-webkit-scrollbar-thumb:hover,
.results-list::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary, #64748B);
}
</style>
