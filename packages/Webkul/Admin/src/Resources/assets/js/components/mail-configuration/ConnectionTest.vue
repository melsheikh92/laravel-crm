<template>
    <div class="flex items-center gap-4">
        <!-- Test Connection Button -->
        <button
            type="button"
            class="secondary-button"
            @click="testConnection"
            :disabled="testing"
        >
            <span v-if="testing">
                {{ testingLabel }}
            </span>
            <span v-else>
                {{ buttonLabel }}
            </span>
        </button>

        <!-- Connection Status Indicator -->
        <div v-if="showStatus && connectionStatus" class="flex items-center gap-2">
            <span
                class="inline-block h-3 w-3 rounded-full"
                :class="connectionStatus === 'success' ? 'bg-green-500' : 'bg-red-500'"
            ></span>
            <span
                class="text-sm"
                :class="connectionStatus === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'"
            >
                {{ statusMessage }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ConnectionTest',

    props: {
        /**
         * The type of connection to test (smtp or imap)
         */
        type: {
            type: String,
            required: true,
            validator: (value) => ['smtp', 'imap'].includes(value),
        },

        /**
         * Configuration object to test
         */
        config: {
            type: Object,
            required: true,
        },

        /**
         * Test endpoint URL
         */
        endpoint: {
            type: String,
            required: true,
        },

        /**
         * Button label text
         */
        buttonLabel: {
            type: String,
            default: 'Test Connection',
        },

        /**
         * Testing label text (shown during test)
         */
        testingLabel: {
            type: String,
            default: 'Testing...',
        },

        /**
         * Required fields for validation
         */
        requiredFields: {
            type: Array,
            default: () => [],
        },

        /**
         * Show inline status indicator
         */
        showStatus: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['test-start', 'test-success', 'test-error', 'test-complete'],

    data() {
        return {
            testing: false,
            connectionStatus: null,
            statusMessage: '',
        };
    },

    methods: {
        /**
         * Validate required fields
         *
         * @returns {boolean}
         */
        validateFields() {
            if (this.requiredFields.length === 0) {
                return true;
            }

            const missingFields = this.requiredFields.filter(
                field => !this.config[field] || this.config[field] === ''
            );

            if (missingFields.length > 0) {
                return false;
            }

            return true;
        },

        /**
         * Test the connection
         *
         * @returns {void}
         */
        testConnection() {
            // Validate required fields
            if (!this.validateFields()) {
                this.$emitter.emit('add-flash', {
                    type: 'warning',
                    message: 'Please fill in all required fields before testing the connection.',
                });
                return;
            }

            // Reset status
            this.connectionStatus = null;
            this.statusMessage = '';

            // Set testing state
            this.testing = true;

            // Emit test start event
            this.$emit('test-start', this.type);

            // Make the API request
            this.$axios.post(this.endpoint, this.config)
                .then(response => {
                    // Connection successful
                    this.connectionStatus = 'success';
                    this.statusMessage = response.data.message || 'Connection successful';

                    // Emit flash message
                    this.$emitter.emit('add-flash', {
                        type: 'success',
                        message: response.data.message,
                    });

                    // Emit success event
                    this.$emit('test-success', {
                        type: this.type,
                        response: response.data,
                    });
                })
                .catch(error => {
                    // Connection failed
                    this.connectionStatus = 'error';
                    this.statusMessage = error.response?.data?.message || 'Connection failed';

                    // Emit flash message
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message: error.response?.data?.message || `${this.type.toUpperCase()} connection test failed. Please check your settings.`,
                    });

                    // Emit error event
                    this.$emit('test-error', {
                        type: this.type,
                        error: error.response?.data,
                    });
                })
                .finally(() => {
                    // Reset testing state
                    this.testing = false;

                    // Emit complete event
                    this.$emit('test-complete', this.type);

                    // Auto-clear status after 5 seconds
                    if (this.showStatus) {
                        setTimeout(() => {
                            this.connectionStatus = null;
                            this.statusMessage = '';
                        }, 5000);
                    }
                });
        },

        /**
         * Manually trigger the test (exposed for parent component)
         *
         * @returns {void}
         */
        test() {
            this.testConnection();
        },
    },
};
</script>
