<?php

return [
    'acl' => [
        'leads' => 'Leads',
        'lead' => 'Lead',
        'quotes' => 'Quotes',
        'mail' => 'Mail',
        'inbox' => 'Inbox',
        'draft' => 'Draft',
        'outbox' => 'Outbox',
        'sent' => 'Sent',
        'trash' => 'Trash',
        'activities' => 'Activities',
        'webhook' => 'Webhook',
        'contacts' => 'Contacts',
        'persons' => 'Persons',
        'organizations' => 'Organizations',
        'products' => 'Products',
        'settings' => 'Settings',
        'groups' => 'Groups',
        'roles' => 'Roles',
        'users' => 'Users',
        'user' => 'User',
        'automation' => 'Automation',
        'attributes' => 'Attributes',
        'pipelines' => 'Pipelines',
        'sources' => 'Sources',
        'types' => 'Types',
        'email-templates' => 'Email Templates',
        'workflows' => 'Workflows',
        'other-settings' => 'Other Settings',
        'tags' => 'Tags',
        'onboarding' => 'Onboarding Wizard',
        'configuration' => 'Configuration',
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'print' => 'Print',
        'delete' => 'Delete',
        'export' => 'Export',
        'mass-delete' => 'Mass Delete',
        'data-transfer' => 'Data Transfer',
        'imports' => 'Imports',
        'import' => 'Import',
        'territories' => 'Territories',
        'territory' => 'Territory',
        'territory-rules' => 'Territory Rules',
        'territory-assignments' => 'Territory Assignments',
        'territory-analytics' => 'Territory Analytics',
        'event' => 'Event',
        'campaigns' => 'Campaigns',
        'marketing-campaigns' => 'Marketing Campaigns',
        'collaboration' => 'Collaboration',
        'channels' => 'Channels',
        'notifications' => 'Notifications',
        'support' => 'Support',
        'support-tickets' => 'Support Tickets',
        'sla-management' => 'SLA Management',
        'knowledge-base' => 'Knowledge Base',
        'integrations' => 'Integrations',
        'integrations-marketplace' => 'Integrations Marketplace',
        'integration-management' => 'Integration Management',
    ],

    'users' => [
        'activate-warning' => 'Your account is not activated yet. Please contact the administrator.',
        'login-error' => 'The credentials do not match our records.',
        'not-permission' => 'You do not have permission to access the admin panel.',

        'login' => [
            'email' => 'Email Address',
            'forget-password-link' => 'Forget Password ?',
            'password' => 'Password',
            'submit-btn' => 'Sign In',
            'title' => 'Sign In',
        ],

        'forget-password' => [
            'create' => [
                'email' => 'Registered Email',
                'email-not-exist' => 'Email Not Exists',
                'page-title' => 'Forget Password',
                'reset-link-sent' => 'Reset Password link sent',
                'sign-in-link' => 'Back to Sign In ?',
                'submit-btn' => 'Reset',
                'title' => 'Recover Password',
            ],
        ],

        'reset-password' => [
            'back-link-title' => 'Back to Sign In ?',
            'confirm-password' => 'Confirm Password',
            'email' => 'Registered Email',
            'password' => 'Password',
            'submit-btn' => 'Reset Password',
            'title' => 'Reset Password',
        ],
    ],

    'account' => [
        'edit' => [
            'back-btn' => 'Back',
            'change-password' => 'Change Password',
            'confirm-password' => 'Confirm Password',
            'current-password' => 'Current Password',
            'email' => 'Email',
            'general' => 'General',
            'invalid-password' => 'The current password you entered is incorrect.',
            'name' => 'Name',
            'password' => 'Password',
            'profile-image' => 'Profile Image',
            'save-btn' => 'Save Account',
            'title' => 'My Account',
            'update-success' => 'Account updated successfully',
            'upload-image-info' => 'Upload a Profile Image (110px X 110px) in PNG or JPG Format',
            'whatsapp-configuration' => 'WhatsApp Configuration',
            'whatsapp-phone-number-id' => 'Phone Number ID',
            'whatsapp-access-token' => 'Access Token',
            'whatsapp-phone-number-id-placeholder' => 'Meta API Phone Number ID',
            'whatsapp-access-token-placeholder' => 'Temporary or Permanent Access Token',
            'whatsapp-configuration-info' => 'Enter your Meta Cloud API credentials to enable WhatsApp messaging from your profile.',
        ],
    ],

    'components' => [
        'activities' => [
            'actions' => [
                'mail' => [
                    'btn' => 'Mail',
                    'title' => 'Compose Mail',
                    'to' => 'To',
                    'enter-emails' => 'Press enter to add emails',
                    'cc' => 'CC',
                    'bcc' => 'BCC',
                    'subject' => 'Subject',
                    'send-btn' => 'Send',
                    'message' => 'Message',
                ],

                'file' => [
                    'btn' => 'File',
                    'title' => 'Add File',
                    'title-control' => 'Title',
                    'name' => 'Name',
                    'description' => 'Description',
                    'file' => 'File',
                    'save-btn' => 'Save File',
                ],

                'note' => [
                    'btn' => 'Note',
                    'title' => 'Add Note',
                    'comment' => 'Comment',
                    'save-btn' => 'Save Note',
                ],

                'activity' => [
                    'btn' => 'Activity',
                    'title' => 'Add Activity',
                    'title-control' => 'Title',
                    'description' => 'Description',
                    'schedule-from' => 'Schedule From',
                    'schedule-to' => 'Schedule To',
                    'location' => 'Location',
                    'call' => 'Call',
                    'meeting' => 'Meeting',
                    'lunch' => 'Lunch',
                    'save-btn' => 'Save Activity',

                    'participants' => [
                        'title' => 'Participants',
                        'placeholder' => 'Type to search participants',
                        'users' => 'Users',
                        'persons' => 'Persons',
                        'no-results' => 'No result found...',
                    ],
                ],

                'whatsapp' => [
                    'btn' => 'WhatsApp',
                    'title' => 'Send WhatsApp Message to :name',
                    'templates' => 'WhatsApp Templates (Optional)',
                    'select-template' => '-- Select a template --',
                    'no-templates' => 'No templates available',
                    'message' => 'Message',
                    'message-placeholder' => 'Type your message here...',
                    'business-api-mode' => 'Business API Mode',
                    'business-api-description' => 'Message will be sent via your WhatsApp Business API',
                    'personal-mode' => 'Personal WhatsApp Mode',
                    'personal-description' => 'Your WhatsApp app will open with the message pre-filled',
                    'send-btn' => 'Send via API',
                    'open-btn' => 'Open in WhatsApp',
                    'cancel' => 'Cancel',
                    'no-contact-number' => 'No contact number found for this person.',
                    'invalid-number' => 'Invalid contact number.',
                    'opening-whatsapp' => 'Opening WhatsApp...',
                ],
            ],

            'index' => [
                'all' => 'All',
                'bcc' => 'Bcc',
                'by-user' => 'By :user',
                'calls' => 'Calls',
                'cc' => 'Cc',
                'change-log' => 'Changelogs',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'emails' => 'Emails',
                'empty' => 'Empty',
                'files' => 'Files',
                'from' => 'From',
                'location' => 'Location',
                'lunches' => 'Lunches',
                'mark-as-done' => 'Mark as Done',
                'meetings' => 'Meetings',
                'notes' => 'Notes',
                'participants' => 'Participants',
                'planned' => 'Planned',
                'quotes' => 'Quotes',
                'scheduled-on' => 'Scheduled on',
                'system' => 'System',
                'to' => 'To',
                'unlink' => 'Unlink',
                'view' => 'View',
                'whatsapps' => 'WhatsApp',
                'phone-number' => 'Phone Number',
                'direction' => 'Direction',
                'outbound' => 'Outbound',
                'inbound' => 'Inbound',
                'whatsapp-conversation' => 'WhatsApp Conversation',
                'message' => 'message',
                'messages' => 'messages',
                'click-to-expand' => 'Click to expand',
                'click-to-collapse' => 'Click to collapse',
                'latest' => 'Latest',

                'empty-placeholders' => [
                    'all' => [
                        'title' => 'No Activities Found',
                        'description' => 'No activities found for this. You can add activities by clicking on the Activity button on the left panel.',
                    ],

                    'planned' => [
                        'title' => 'No Planned Activities Found',
                        'description' => 'No planned activities found for this. You can add planned activities by clicking on the Activity button on the left panel.',
                    ],

                    'notes' => [
                        'title' => 'No Notes Found',
                        'description' => 'No notes found for this. You can add notes by clicking on the Note button on the left panel.',
                    ],

                    'calls' => [
                        'title' => 'No Calls Found',
                        'description' => 'No calls found for this. You can add calls by clicking on the Activity button on the left panel and selecting the Call type.',
                    ],

                    'meetings' => [
                        'title' => 'No Meetings Found',
                        'description' => 'No meetings found for this. You can add meetings by clicking on the Activity button on the left panel and selecting the Meeting type.',
                    ],

                    'lunches' => [
                        'title' => 'No Lunches Found',
                        'description' => 'No lunches found for this. You can add lunches by clicking on the Activity button on the left panel and selecting the Lunch type.',
                    ],

                    'files' => [
                        'title' => 'No Files Found',
                        'description' => 'No files found for this. You can add files by clicking on the File button on the left panel.',
                    ],

                    'emails' => [
                        'title' => 'No Emails Found',
                        'description' => 'No emails found for this. You can add emails by clicking on the Mail button on the left panel.',
                    ],

                    'system' => [
                        'title' => 'No Changelogs Found',
                        'description' => 'No changelogs found for this.',
                    ],

                    'whatsapp' => [
                        'title' => 'No WhatsApp Messages Found',
                        'description' => 'No WhatsApp messages found for this. WhatsApp messages will appear here when you send or receive them.',
                    ],
                ],
            ],
        ],

        'media' => [
            'images' => [
                'add-image-btn' => 'Add Image',
                'ai-add-image-btn' => 'Magic AI',
                'allowed-types' => 'png, jpeg, jpg',
                'not-allowed-error' => 'Only images files (.jpeg, .jpg, .png, ..) are allowed.',

                'placeholders' => [
                    'front' => 'Front',
                    'next' => 'Next',
                    'size' => 'Size',
                    'use-cases' => 'Use Cases',
                    'zoom' => 'Zoom',
                ],
            ],

            'videos' => [
                'add-video-btn' => 'Add Video',
                'allowed-types' => 'mp4, webm, mkv',
                'not-allowed-error' => 'Only videos files (.mp4, .mov, .ogg ..) are allowed.',
            ],
        ],

        'datagrid' => [
            'index' => [
                'no-records-selected' => 'No records have been selected.',
                'must-select-a-mass-action-option' => 'You must select a mass action\'s option.',
                'must-select-a-mass-action' => 'You must select a mass action.',
            ],

            'toolbar' => [
                'length-of' => ':length of',
                'of' => 'of',
                'per-page' => 'Per Page',
                'results' => ':total Results',
                'delete' => 'Delete',
                'selected' => ':total Items Selected',

                'mass-actions' => [
                    'submit' => 'Submit',
                    'select-option' => 'Select Option',
                    'select-action' => 'Select Action',
                ],

                'filter' => [
                    'apply-filters-btn' => 'Apply Filters',
                    'back-btn' => 'Back',
                    'create-new-filter' => 'Create New Filter',
                    'custom-filters' => 'Custom Filters',
                    'delete-error' => 'Something went wrong while deleting the filter, please try again.',
                    'delete-success' => 'Filter has been deleted successfully.',
                    'empty-description' => 'There is no selected filters available to save. Please select filters to save.',
                    'empty-title' => 'Add Filters to Save',
                    'name' => 'Name',
                    'quick-filters' => 'Quick Filters',
                    'save-btn' => 'Save',
                    'save-filter' => 'Save Filter',
                    'saved-success' => 'Filter has been saved successfully.',
                    'selected-filters' => 'Selected Filters',
                    'title' => 'Filter',
                    'update' => 'Update',
                    'update-filter' => 'Update Filter',
                    'updated-success' => 'Filter has been updated successfully.',
                ],

                'search' => [
                    'title' => 'Search',
                ],
            ],
        ],
    ],
];