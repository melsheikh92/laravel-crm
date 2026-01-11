<?php

namespace Webkul\Territory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryRuleResource extends JsonResource
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
            'id'           => $this->id,
            'territory_id' => $this->territory_id,
            'rule_type'    => $this->rule_type,
            'field_name'   => $this->field_name,
            'operator'     => $this->operator,
            'value'        => $this->value,
            'priority'     => $this->priority,
            'is_active'    => $this->is_active,
            'territory'    => new TerritoryResource($this->whenLoaded('territory')),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
