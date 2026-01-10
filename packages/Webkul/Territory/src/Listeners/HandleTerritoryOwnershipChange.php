<?php

namespace Webkul\Territory\Listeners;

use Webkul\Territory\Models\Territory;
use Webkul\Territory\Services\TerritoryReassignmentHandler;

class HandleTerritoryOwnershipChange
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected TerritoryReassignmentHandler $reassignmentHandler
    ) {}

    /**
     * Handle the territory update event.
     *
     * @param  \Webkul\Territory\Models\Territory  $territory
     * @return void
     */
    public function handle($territory): void
    {
        if (! $territory instanceof Territory) {
            return;
        }

        // Check if the territory owner has changed
        if (! $territory->wasChanged('user_id')) {
            return;
        }

        $oldOwnerId = $territory->getOriginal('user_id');
        $newOwnerId = $territory->user_id;

        // Handle ownership transfer for all assigned entities
        $this->reassignmentHandler->handleTerritoryOwnerChange(
            $territory->id,
            $oldOwnerId,
            $newOwnerId
        );
    }
}
