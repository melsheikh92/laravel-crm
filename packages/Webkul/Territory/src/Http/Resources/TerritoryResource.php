<?php

namespace Webkul\Territory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Admin\Http\Resources\UserResource;

class TerritoryResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
            'type'        => $this->type,
            'status'      => $this->status,
            'boundaries'  => $this->boundaries,
            'parent_id'   => $this->parent_id,
            'parent'      => new TerritoryResource($this->whenLoaded('parent')),
            'children'    => TerritoryResource::collection($this->whenLoaded('children')),
            'owner'       => new UserResource($this->whenLoaded('owner')),
            'users'       => UserResource::collection($this->whenLoaded('users')),
            'rules'       => TerritoryRuleResource::collection($this->whenLoaded('rules')),
            'assignments' => TerritoryAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}
