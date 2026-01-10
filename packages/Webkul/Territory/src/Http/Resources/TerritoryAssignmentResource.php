<?php

namespace Webkul\Territory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Admin\Http\Resources\UserResource;

class TerritoryAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'territory_id'    => $this->territory_id,
            'assignable_type' => $this->assignable_type,
            'assignable_id'   => $this->assignable_id,
            'assignment_type' => $this->assignment_type,
            'assigned_at'     => $this->assigned_at,
            'territory'       => new TerritoryResource($this->whenLoaded('territory')),
            'assignable'      => $this->whenLoaded('assignable'),
            'assigned_by'     => new UserResource($this->whenLoaded('assignedBy')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
