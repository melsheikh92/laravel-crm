<?php

namespace Webkul\Territory\Listeners;

use Webkul\Contact\Contracts\Organization;
use Webkul\Territory\Services\TerritoryAssignmentService;

class AssignTerritoryToOrganization
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected TerritoryAssignmentService $assignmentService
    ) {}

    /**
     * Handle the contacts.organization.create.after event.
     *
     * @param  \Webkul\Contact\Contracts\Organization  $organization
     * @return void
     */
    public function handle($organization)
    {
        if (! $organization instanceof Organization) {
            return;
        }

        $this->assignmentService->autoAssign($organization);
    }
}
