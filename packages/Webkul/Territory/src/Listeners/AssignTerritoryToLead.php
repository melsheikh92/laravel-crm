<?php

namespace Webkul\Territory\Listeners;

use Webkul\Lead\Contracts\Lead;
use Webkul\Territory\Services\TerritoryAssignmentService;

class AssignTerritoryToLead
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected TerritoryAssignmentService $assignmentService
    ) {}

    /**
     * Handle the lead.create.after event.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    public function handle($lead)
    {
        if (! $lead instanceof Lead) {
            return;
        }

        $this->assignmentService->autoAssign($lead);
    }
}
