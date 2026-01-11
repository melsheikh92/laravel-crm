<?php

namespace Webkul\Territory\Listeners;

use Webkul\Contact\Contracts\Person;
use Webkul\Territory\Services\TerritoryAssignmentService;

class AssignTerritoryToPerson
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected TerritoryAssignmentService $assignmentService
    ) {}

    /**
     * Handle the contacts.person.create.after event.
     *
     * @param  \Webkul\Contact\Contracts\Person  $person
     * @return void
     */
    public function handle($person)
    {
        if (! $person instanceof Person) {
            return;
        }

        $this->assignmentService->autoAssign($person);
    }
}
