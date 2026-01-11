<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push(trans('admin::app.layouts.dashboard'), route('admin.dashboard.index'));
});

// Dashboard > Leads
Breadcrumbs::for('leads', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.leads'), route('admin.leads.index'));
});

// Dashboard > Leads > Create
Breadcrumbs::for('leads.create', function (BreadcrumbTrail $trail) {
    $trail->parent('leads');
    $trail->push(trans('admin::app.leads.create.title'), route('admin.leads.create'));
});

// Leads Edit
Breadcrumbs::for('leads.edit', function (BreadcrumbTrail $trail, $lead) {
    $trail->parent('leads');
    $trail->push(trans('admin::app.leads.edit.title'), route('admin.leads.edit', $lead->id));
});

// Dashboard > Leads > Title
Breadcrumbs::for('leads.view', function (BreadcrumbTrail $trail, $lead) {
    $trail->parent('leads');
    $trail->push('#' . $lead->id, route('admin.leads.view', $lead->id));
});

// Dashboard > Quotes
Breadcrumbs::for('quotes', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.quotes'), route('admin.quotes.index'));
});

// Dashboard > Quotes > Add Quote
Breadcrumbs::for('quotes.create', function (BreadcrumbTrail $trail) {
    $trail->parent('quotes');
    $trail->push(trans('admin::app.quotes.create.title'), route('admin.quotes.create'));
});

// Dashboard > Quotes > Edit Quote
Breadcrumbs::for('quotes.edit', function (BreadcrumbTrail $trail, $quote) {
    $trail->parent('quotes');
    $trail->push(trans('admin::app.quotes.edit.title'), route('admin.quotes.edit', $quote->id));
});

// Mail
Breadcrumbs::for('mail', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.mail.title'), route('admin.mail.index', ['route' => 'inbox']));
});

// Mail > [Compose | Inbox | Outbox | Draft | Sent | Trash]
Breadcrumbs::for('mail.route', function (BreadcrumbTrail $trail, $route) {
    $trail->parent('mail');
    $trail->push(trans('admin::app.mail.index.' . $route), route('admin.mail.index', ['route' => $route]));
});

// Mail > [Inbox | Outbox | Draft | Sent | Trash] > Title
Breadcrumbs::for('mail.route.view', function (BreadcrumbTrail $trail, $route, $email) {
    $trail->parent('mail.route', $route);
    $trail->push($email->subject ?? '', route('admin.mail.view', ['route' => $route, 'id' => $email->id]));
});

// Dashboard > Activities
Breadcrumbs::for('activities', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.activities'), route('admin.activities.index'));
});

// Dashboard > activities > Edit Activity
Breadcrumbs::for('activities.edit', function (BreadcrumbTrail $trail, $activity) {
    $trail->parent('activities');
    $trail->push(trans('admin::app.activities.edit.title'), route('admin.activities.edit', $activity->id));
});

// Dashboard > Contacts
Breadcrumbs::for('contacts', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.contacts'), route('admin.contacts.persons.index'));
});

// Dashboard > Contacts > Persons
Breadcrumbs::for('contacts.persons', function (BreadcrumbTrail $trail) {
    $trail->parent('contacts');
    $trail->push(trans('admin::app.layouts.persons'), route('admin.contacts.persons.index'));
});

// Dashboard > Contacts > Persons > Create
Breadcrumbs::for('contacts.persons.create', function (BreadcrumbTrail $trail) {
    $trail->parent('contacts.persons');
    $trail->push(trans('admin::app.contacts.persons.create.title'), route('admin.contacts.persons.create'));
});

// Dashboard > Contacts > Persons > Edit
Breadcrumbs::for('contacts.persons.edit', function (BreadcrumbTrail $trail, $person) {
    $trail->parent('contacts.persons');
    $trail->push(trans('admin::app.contacts.persons.edit.title'), route('admin.contacts.persons.edit', $person->id));
});

// Dashboard > Contacts > Persons > View
Breadcrumbs::for('contacts.persons.view', function (BreadcrumbTrail $trail, $person) {
    $trail->parent('contacts.persons');
    $trail->push('#' . $person->id, route('admin.contacts.persons.index'));
});

// Dashboard > Contacts > Organizations
Breadcrumbs::for('contacts.organizations', function (BreadcrumbTrail $trail) {
    $trail->parent('contacts');
    $trail->push(trans('admin::app.layouts.organizations'), route('admin.contacts.organizations.index'));
});

// Dashboard > Contacts > Organizations > Create
Breadcrumbs::for('contacts.organizations.create', function (BreadcrumbTrail $trail) {
    $trail->parent('contacts.organizations');
    $trail->push(trans('admin::app.contacts.organizations.create.title'), route('admin.contacts.organizations.create'));
});

// Dashboard > Contacts > Organizations > Edit
Breadcrumbs::for('contacts.organizations.edit', function (BreadcrumbTrail $trail, $organization) {
    $trail->parent('contacts.organizations');
    $trail->push(trans('admin::app.contacts.organizations.edit.title'), route('admin.contacts.organizations.edit', $organization->id));
});

// Products
Breadcrumbs::for('products', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.products'), route('admin.products.index'));
});

// Dashboard > Products > Create Product
Breadcrumbs::for('products.create', function (BreadcrumbTrail $trail) {
    $trail->parent('products');
    $trail->push(trans('admin::app.products.create.title'), route('admin.products.create'));
});

// Dashboard > Products > View Product
Breadcrumbs::for('products.view', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products');
    $trail->push('#' . $product->id, route('admin.products.view', $product->id));
});

// Dashboard > Products > Edit Product
Breadcrumbs::for('products.edit', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products');
    $trail->push(trans('admin::app.products.edit.title'), route('admin.products.edit', $product->id));
});

// Settings
Breadcrumbs::for('settings', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.settings'), route('admin.settings.index'));
});

// Settings > Groups
Breadcrumbs::for('settings.groups', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.groups'), route('admin.settings.groups.index'));
});

// Dashboard > Groups > Create Group
Breadcrumbs::for('settings.groups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.groups');
    $trail->push(trans('admin::app.settings.groups.create-title'), route('admin.settings.groups.create'));
});

// Dashboard > Groups > Edit Group
Breadcrumbs::for('settings.groups.edit', function (BreadcrumbTrail $trail, $role) {
    $trail->parent('settings.groups');
    $trail->push(trans('admin::app.settings.groups.edit-title'), route('admin.settings.groups.edit', $role->id));
});

// Settings > Roles
Breadcrumbs::for('settings.roles', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.roles'), route('admin.settings.roles.index'));
});

// Dashboard > Roles > Create Role
Breadcrumbs::for('settings.roles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.roles');
    $trail->push(trans('admin::app.settings.roles.create.title'), route('admin.settings.roles.create'));
});

// Dashboard > Roles > Edit Role
Breadcrumbs::for('settings.roles.edit', function (BreadcrumbTrail $trail, $role) {
    $trail->parent('settings.roles');
    $trail->push(trans('admin::app.settings.roles.edit.title'), route('admin.settings.roles.edit', $role->id));
});

// Settings > Users
Breadcrumbs::for('settings.users', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.users'), route('admin.settings.users.index'));
});

// Dashboard > Users > Create Role
Breadcrumbs::for('settings.users.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.users');
    $trail->push(trans('admin::app.settings.users.create-title'), route('admin.settings.users.create'));
});

// Dashboard > Users > Edit Role
Breadcrumbs::for('settings.users.edit', function (BreadcrumbTrail $trail, $user) {
    $trail->parent('settings.users');
    $trail->push(trans('admin::app.settings.users.edit-title'), route('admin.settings.users.edit', $user->id));
});

// Settings > Attributes
Breadcrumbs::for('settings.attributes', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.attributes'), route('admin.settings.attributes.index'));
});

// Dashboard > Attributes > Create Attribute
Breadcrumbs::for('settings.attributes.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.attributes');
    $trail->push(trans('admin::app.settings.attributes.create.title'), route('admin.settings.attributes.create'));
});

// Dashboard > Attributes > Edit Attribute
Breadcrumbs::for('settings.attributes.edit', function (BreadcrumbTrail $trail, $attribute) {
    $trail->parent('settings.attributes');
    $trail->push(trans('admin::app.settings.attributes.edit.title'), route('admin.settings.attributes.edit', $attribute->id));
});

// Settings > Pipelines
Breadcrumbs::for('settings.pipelines', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.pipelines'), route('admin.settings.pipelines.index'));
});

// Dashboard > Pipelines > Create Pipeline
Breadcrumbs::for('settings.pipelines.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.pipelines');
    $trail->push(trans('admin::app.settings.pipelines.create.title'), route('admin.settings.pipelines.create'));
});

// Dashboard > Pipelines > Edit Pipeline
Breadcrumbs::for('settings.pipelines.edit', function (BreadcrumbTrail $trail, $pipeline) {
    $trail->parent('settings.pipelines');
    $trail->push(trans('admin::app.settings.pipelines.edit.title'), route('admin.settings.pipelines.edit', $pipeline->id));
});

// Settings > Sources
Breadcrumbs::for('settings.sources', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.sources'), route('admin.settings.sources.index'));
});

// Dashboard > Sources > Edit Source
Breadcrumbs::for('settings.sources.edit', function (BreadcrumbTrail $trail, $source) {
    $trail->parent('settings.sources');
    $trail->push(trans('admin::app.settings.sources.edit-title'), route('admin.settings.sources.edit', $source->id));
});

// Settings > Types
Breadcrumbs::for('settings.types', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.types'), route('admin.settings.types.index'));
});

// Dashboard > Types > Edit Type
Breadcrumbs::for('settings.types.edit', function (BreadcrumbTrail $trail, $type) {
    $trail->parent('settings.types');
    $trail->push(trans('admin::app.settings.types.edit-title'), route('admin.settings.types.edit', $type->id));
});

// Settings > Email Templates
Breadcrumbs::for('settings.email_templates', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.email-template.index.title'), route('admin.settings.email_templates.index'));
});

// Dashboard > Email Templates > Create Email Template
Breadcrumbs::for('settings.email_templates.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.email_templates');
    $trail->push(trans('admin::app.settings.email-template.create.title'), route('admin.settings.email_templates.create'));
});

// Dashboard > Email Templates > Edit Email Template
Breadcrumbs::for('settings.email_templates.edit', function (BreadcrumbTrail $trail, $emailTemplate) {
    $trail->parent('settings.email_templates');
    $trail->push(trans('admin::app.settings.email-template.edit.title'), route('admin.settings.email_templates.edit', $emailTemplate->id));
});

// Settings > Mail Configuration
Breadcrumbs::for('settings.mail_configuration', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.mail-configuration.index.title'), route('admin.settings.mail_configuration.index'));
});

// Settings > Marketing Events
Breadcrumbs::for('settings.marketing.events', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.marketing.events.index.title'), route('admin.settings.marketing.events.index'));
});

Breadcrumbs::for('settings.marketing.campaigns', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.marketing.campaigns.index.title'), route('admin.settings.marketing.campaigns.index'));
});

// Dashboard > Settings > Territories
Breadcrumbs::for('settings.territories', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.territories.index.title'), route('admin.settings.territories.index'));
});

// Dashboard > Settings > Territories > Create
Breadcrumbs::for('settings.territories.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.territories');
    $trail->push(trans('admin::app.settings.territories.create.title'), route('admin.settings.territories.create'));
});

// Dashboard > Settings > Territories > Edit
Breadcrumbs::for('settings.territories.edit', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories');
    $trail->push(trans('admin::app.settings.territories.edit.title'), route('admin.settings.territories.edit', $territory->id));
});

// Dashboard > Settings > Territories > View
Breadcrumbs::for('settings.territories.view', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories');
    $trail->push($territory ? $territory->name : 'Territory', $territory ? route('admin.settings.territories.view', $territory->id) : '#');
});

// Dashboard > Settings > Territory Assignments (Global Index)
Breadcrumbs::for('settings.territories.assignments', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.territory-assignments.index.title'), route('admin.settings.territories.assignments.index'));
});

// Dashboard > Settings > Territory Assignments > Create
Breadcrumbs::for('settings.territories.assignments.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.territories.assignments');
    $trail->push(trans('admin::app.settings.territory-assignments.create.title'), route('admin.settings.territories.assignments.create'));
});

// Dashboard > Settings > Territories > Assignments (Specific Territory)
Breadcrumbs::for('settings.territories.assignments.territory', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories.view', $territory);
    $trail->push(trans('admin::app.settings.territories.view.assignments'), route('admin.settings.territories.assignments.index', $territory->id));
});

// Dashboard > Settings > Territories > Analytics (Global)
Breadcrumbs::for('settings.territories.analytics', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.territories');
    $trail->push(trans('admin::app.settings.territories.analytics.title'), route('admin.settings.territories.analytics.index'));
});

// Dashboard > Settings > Territories > Analytics (Specific Territory)
Breadcrumbs::for('settings.territories.analytics.territory', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories.view', $territory);
    $trail->push(trans('admin::app.settings.territories.view.analytics'), route('admin.settings.territories.analytics.index', $territory->id));
});

// Dashboard > Settings > Territories > Rules
Breadcrumbs::for('settings.territories.rules', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories.view', $territory);
    $trail->push(trans('admin::app.settings.territories.view.rules'), route('admin.settings.territories.rules.index', $territory->id));
});

// Dashboard > Settings > Territories > Rules > Create
Breadcrumbs::for('settings.territories.rules.create', function (BreadcrumbTrail $trail, $territory) {
    $trail->parent('settings.territories.rules', $territory);
    $trail->push(trans('admin::app.settings.territories.rules.create.title'), route('admin.settings.territories.rules.create', $territory->id));
});

// Dashboard > Settings > Territories > Rules > Edit
Breadcrumbs::for('settings.territories.rules.edit', function (BreadcrumbTrail $trail, $territory, $rule) {
    $trail->parent('settings.territories.rules', $territory);
    $trail->push(trans('admin::app.settings.territories.rules.edit.title'), route('admin.settings.territories.rules.edit', [$territory->id, $rule->id]));
});

// Settings > Workflows
Breadcrumbs::for('settings.workflows', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.workflows'), route('admin.settings.workflows.index'));
});

// Dashboard > Workflows > Create Workflow
Breadcrumbs::for('settings.workflows.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.workflows');
    $trail->push(trans('admin::app.settings.workflows.create.title'), route('admin.settings.workflows.create'));
});

// Dashboard > Workflows > Edit Workflow
Breadcrumbs::for('settings.workflows.edit', function (BreadcrumbTrail $trail, $workflow) {
    $trail->parent('settings.workflows');
    $trail->push(trans('admin::app.settings.workflows.edit.title'), route('admin.settings.workflows.edit', $workflow->id));
});

// Settings > Webhooks
Breadcrumbs::for('settings.webhooks', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.webhooks.index.title'), route('admin.settings.webhooks.index'));
});

// Dashboard > Webhooks > Create Workflow
Breadcrumbs::for('settings.webhooks.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.webhooks');
    $trail->push(trans('admin::app.settings.webhooks.create.title'), route('admin.settings.webhooks.create'));
});

// Dashboard > Webhooks > Edit Workflow
Breadcrumbs::for('settings.webhooks.edit', function (BreadcrumbTrail $trail, $workflow) {
    $trail->parent('settings.webhooks');
    $trail->push(trans('admin::app.settings.webhooks.edit.edit-btn'), route('admin.settings.workflows.edit', $workflow->id));
});

// Settings > Tags
Breadcrumbs::for('settings.tags', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.layouts.tags'), route('admin.settings.tags.index'));
});

// Dashboard > Tags > Edit Tag
Breadcrumbs::for('settings.tags.edit', function (BreadcrumbTrail $trail, $tag) {
    $trail->parent('settings.tags');
    $trail->push(trans('admin::app.settings.tags.edit-title'), route('admin.settings.tags.edit', $tag->id));
});

// Settings > Web Form
Breadcrumbs::for('settings.web_forms', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.webforms.index.title'), route('admin.settings.web_forms.index'));
});

// Dashboard > Web Form > Create Web Form
Breadcrumbs::for('settings.web_forms.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.web_forms');
    $trail->push(trans('admin::app.settings.webforms.create.title'), route('admin.settings.web_forms.create'));
});

// Dashboard > Web Form > Edit Web Form
Breadcrumbs::for('settings.web_forms.edit', function (BreadcrumbTrail $trail, $webForm) {
    $trail->parent('settings.web_forms');
    $trail->push(trans('admin::app.settings.webforms.edit.title'), route('admin.settings.web_forms.edit', $webForm->id));
});

// Settings > Warehouse
Breadcrumbs::for('settings.warehouses', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.warehouses.index.title'), route('admin.settings.warehouses.index'));
});

// Dashboard > Settings > Warehouse > Create Warehouse
Breadcrumbs::for('settings.warehouses.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.warehouses');
    $trail->push(trans('admin::app.settings.warehouses.create.title'), route('admin.settings.warehouses.create'));
});

// Dashboard > Settings > Warehouse > Edit Warehouse
Breadcrumbs::for('settings.warehouses.edit', function (BreadcrumbTrail $trail, $warehouse) {
    $trail->parent('settings.warehouses');
    $trail->push(trans('admin::app.settings.warehouses.edit.title'), route('admin.settings.warehouses.edit', $warehouse->id));
});

// Dashboard > Settings > Warehouse > View Warehouse
Breadcrumbs::for('settings.warehouses.view', function (BreadcrumbTrail $trail, $warehouse) {
    $trail->parent('settings.warehouses');
    $trail->push('#' . $warehouse->id, route('admin.settings.warehouses.view', $warehouse->id));
});

// Dashboard > Settings > Warehouse > View Warehouse > Products
Breadcrumbs::for('settings.warehouses.view.products', function (BreadcrumbTrail $trail, $warehouse) {
    $trail->parent('settings.warehouses.view', $warehouse);
    $trail->push(trans('admin::app.settings.warehouses.products'), route('admin.settings.warehouses.products.index', $warehouse->id));
});

// Dashboard > Settings > Locations
Breadcrumbs::for('settings.locations', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.locations.title'), route('admin.settings.locations.index'));
});

// Dashboard > Settings > Locations > Create Warehouse
Breadcrumbs::for('settings.locations.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.locations');
    $trail->push(trans('admin::app.settings.locations.create-title'), route('admin.settings.locations.create'));
});

// Dashboard > Settings > Locations > Edit Warehouse
Breadcrumbs::for('settings.locations.edit', function (BreadcrumbTrail $trail, $location) {
    $trail->parent('settings.locations');
    $trail->push(trans('admin::app.settings.locations.edit-title'), route('admin.settings.locations.edit', $location->id));
});

// Dashboard > Settings > Data Transfers
Breadcrumbs::for('settings.data_transfers', function (BreadcrumbTrail $trail) {
    $trail->parent('settings');
    $trail->push(trans('admin::app.settings.data-transfer.imports.index.title'), route('admin.settings.data_transfer.imports.index'));
});

// Dashboard > Settings > Data Transfers > Create Data Transfer
Breadcrumbs::for('settings.data_transfers.create', function (BreadcrumbTrail $trail) {
    $trail->parent('settings.data_transfers');
    $trail->push(trans('admin::app.settings.data-transfer.imports.create.title'), route('admin.settings.data_transfer.imports.create'));
});

// Dashboard > Settings > Data Transfers > Edit Data Transfer
Breadcrumbs::for('settings.data_transfers.edit', function (BreadcrumbTrail $trail, $import) {
    $trail->parent('settings.data_transfers');
    $trail->push(trans('admin::app.settings.data-transfer.imports.edit.title'), route('admin.settings.data_transfer.imports.edit', $import->id));
});

// Dashboard > Settings > Data Transfers > Import Data Transfer
Breadcrumbs::for('settings.data_transfers.import', function (BreadcrumbTrail $trail, $import) {
    $trail->parent('settings.data_transfers');
    $trail->push(trans('admin::app.settings.data-transfer.imports.import.title'), route('admin.settings.data_transfer.imports.import', $import->id));
});

// Configuration
Breadcrumbs::for('configuration', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.configuration'), route('admin.configuration.index'));
});

// Configuration > Config
Breadcrumbs::for('configuration.slug', function (BreadcrumbTrail $trail, $slug) {
    $trail->parent('configuration');
    $trail->push('', route('admin.configuration.index', ['slug' => $slug]));
});

// Dashboard > Account > Edit
Breadcrumbs::for('dashboard.account.edit', function (BreadcrumbTrail $trail, $user) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.account.edit.title'), route('admin.user.account.edit', $user->id));
});

// Support Tickets
Breadcrumbs::for('support.tickets', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.support-tickets'), route('admin.support.tickets.index'));
});

// SLA Policies
Breadcrumbs::for('support.sla.policies', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.sla-management'), route('admin.support.sla.policies.index'));
});

// Knowledge Base
Breadcrumbs::for('support.kb.articles', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.knowledge-base'), route('admin.support.kb.articles.index'));
});

// Support Tickets > Create
Breadcrumbs::for('support.tickets.create', function (BreadcrumbTrail $trail) {
    $trail->parent('support.tickets');
    $trail->push(trans('admin::app.support.tickets.create.title'), route('admin.support.tickets.create'));
});

// Support Tickets > Edit
Breadcrumbs::for('support.tickets.edit', function (BreadcrumbTrail $trail, $ticket) {
    $trail->parent('support.tickets');
    $trail->push(trans('admin::app.support.tickets.edit.title'), route('admin.support.tickets.edit', $ticket->id));
});

// Support Tickets > View
Breadcrumbs::for('support.tickets.view', function (BreadcrumbTrail $trail, $ticket) {
    $trail->parent('support.tickets');
    $trail->push('#' . $ticket->id, route('admin.support.tickets.show', $ticket->id));
});

// SLA Policies > Create
Breadcrumbs::for('support.sla.policies.create', function (BreadcrumbTrail $trail) {
    $trail->parent('support.sla.policies');
    $trail->push(trans('admin::app.support.sla.create.title'), route('admin.support.sla.policies.create'));
});

// SLA Policies > Edit
Breadcrumbs::for('support.sla.policies.edit', function (BreadcrumbTrail $trail, $policy) {
    $trail->parent('support.sla.policies');
    $trail->push(trans('admin::app.support.sla.edit.title'), route('admin.support.sla.policies.edit', $policy->id));
});

// Knowledge Base > Create
Breadcrumbs::for('support.kb.articles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('support.kb.articles');
    $trail->push(trans('admin::app.support.kb.create.title'), route('admin.support.kb.articles.create'));
});

// Knowledge Base > Edit
Breadcrumbs::for('support.kb.articles.edit', function (BreadcrumbTrail $trail, $article) {
    $trail->parent('support.kb.articles');
    $trail->push(trans('admin::app.support.kb.edit.title'), route('admin.support.kb.articles.edit', $article->id));
});

// KB Categories
Breadcrumbs::for('support.kb.categories.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.support.kb.categories.index.title'), route('admin.support.kb.categories.index'));
});

// KB Categories > Create
Breadcrumbs::for('support.kb.categories.create', function (BreadcrumbTrail $trail) {
    $trail->parent('support.kb.categories.index');
    $trail->push(trans('admin::app.support.kb.categories.create.title'), route('admin.support.kb.categories.create'));
});

// KB Categories > Edit
Breadcrumbs::for('support.kb.categories.edit', function (BreadcrumbTrail $trail, $category) {
    $trail->parent('support.kb.categories.index');
    $trail->push(trans('admin::app.support.kb.categories.edit.title'), route('admin.support.kb.categories.edit', $category->id));
});

// Ticket Categories
Breadcrumbs::for('support.categories.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.support.categories.index.title'), route('admin.support.categories.index'));
});

// Ticket Categories > Create
Breadcrumbs::for('support.categories.create', function (BreadcrumbTrail $trail) {
    $trail->parent('support.categories.index');
    $trail->push(trans('admin::app.support.categories.create.title'), route('admin.support.categories.create'));
});

// Ticket Categories > Edit
Breadcrumbs::for('support.categories.edit', function (BreadcrumbTrail $trail, $category) {
    $trail->parent('support.categories.index');
    $trail->push(trans('admin::app.support.categories.edit.title'), route('admin.support.categories.edit', $category->id));
});

// Marketing > Campaigns
Breadcrumbs::for('marketing.campaigns.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.marketing.campaigns.index.title'), route('admin.marketing.campaigns.index'));
});

// Marketing > Campaigns > Create
Breadcrumbs::for('marketing.campaigns.create', function (BreadcrumbTrail $trail) {
    $trail->parent('marketing.campaigns.index');
    $trail->push(trans('admin::app.marketing.campaigns.create.title'), route('admin.marketing.campaigns.create'));
});

// Marketing > Campaigns > Edit
Breadcrumbs::for('marketing.campaigns.edit', function (BreadcrumbTrail $trail, $campaign) {
    $trail->parent('marketing.campaigns.index');
    $trail->push(trans('admin::app.marketing.campaigns.edit.title'), route('admin.marketing.campaigns.edit', $campaign->id));
});

// Marketing > Campaigns > View
Breadcrumbs::for('marketing.campaigns.view', function (BreadcrumbTrail $trail, $campaign) {
    $trail->parent('marketing.campaigns.index');
    $trail->push($campaign->name, route('admin.marketing.campaigns.view', $campaign->id));
});

// Marketplace
Breadcrumbs::for('marketplace.extensions', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('marketplace::app.layouts.extensions'), route('admin.marketplace.extensions.index'));
});

Breadcrumbs::for('marketplace.extensions.create', function (BreadcrumbTrail $trail) {
    $trail->parent('marketplace.extensions');
    $trail->push(trans('marketplace::app.admin.extensions.create.title'), route('admin.marketplace.extensions.create'));
});

Breadcrumbs::for('marketplace.extensions.edit', function (BreadcrumbTrail $trail, $extension) {
    $trail->parent('marketplace.extensions');
    $trail->push(trans('marketplace::app.admin.extensions.edit.title'), route('admin.marketplace.extensions.edit', $extension->id));
});

Breadcrumbs::for('marketplace.extensions.show', function (BreadcrumbTrail $trail, $extension) {
    $trail->parent('marketplace.extensions');
    $trail->push($extension->name, route('admin.marketplace.extensions.show', $extension->id));
});

Breadcrumbs::for('marketplace.categories', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('marketplace::app.layouts.categories'), route('admin.marketplace.categories.index'));
});

Breadcrumbs::for('marketplace.categories.create', function (BreadcrumbTrail $trail) {
    $trail->parent('marketplace.categories');
    $trail->push(trans('marketplace::app.admin.categories.create.title'), route('admin.marketplace.categories.create'));
});

Breadcrumbs::for('marketplace.categories.edit', function (BreadcrumbTrail $trail, $category) {
    $trail->parent('marketplace.categories');
    $trail->push(trans('marketplace::app.admin.categories.edit.title'), route('admin.marketplace.categories.edit', $category->id));
});

Breadcrumbs::for('marketplace.submissions', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('marketplace::app.layouts.submissions'), route('admin.marketplace.submissions.index'));
});

Breadcrumbs::for('marketplace.submissions.review', function (BreadcrumbTrail $trail, $submission) {
    $trail->parent('marketplace.submissions');
    $trail->push(trans('marketplace::app.admin.submissions.review.title'), route('admin.marketplace.submissions.review', $submission->id));
});

Breadcrumbs::for('marketplace.developer-applications', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('marketplace::app.layouts.developer-applications'), route('admin.marketplace.developer-applications.index'));
});

Breadcrumbs::for('marketplace.developer-applications.show', function (BreadcrumbTrail $trail, $application) {
    $trail->parent('marketplace.developer-applications');
    $trail->push($application->name, route('admin.marketplace.developer-applications.show', $application->id));
});

Breadcrumbs::for('marketplace.revenue', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('marketplace::app.layouts.revenue'), route('admin.marketplace.revenue.index'));
});

Breadcrumbs::for('marketplace.revenue.transactions', function (BreadcrumbTrail $trail) {
    $trail->parent('marketplace.revenue');
    $trail->push(trans('marketplace::app.admin.revenue.transactions.title'), route('admin.marketplace.revenue.transactions'));
});

// Dashboard > Forecasts
Breadcrumbs::for('forecasts', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('admin::app.layouts.forecasts'), route('admin.forecasts.index'));
});

// Dashboard > Forecasts > Accuracy
Breadcrumbs::for('forecasts.accuracy', function (BreadcrumbTrail $trail) {
    $trail->parent('forecasts');
    $trail->push(trans('admin::app.layouts.forecast-accuracy'), route('admin.forecasts.accuracy'));
});

// Dashboard > Forecasts > Team
Breadcrumbs::for('forecasts.team', function (BreadcrumbTrail $trail, $teamId) {
    $trail->parent('forecasts');
    $trail->push(trans('admin::app.layouts.team-forecasts'), route('admin.forecasts.team', $teamId));
});

// Dashboard > Forecasts > Scenario Modeling
Breadcrumbs::for('forecasts.scenarios', function (BreadcrumbTrail $trail) {
    $trail->parent('forecasts');
    $trail->push(trans('admin::app.layouts.scenario-modeling'), route('admin.forecasts.analytics.scenarios'));
});
