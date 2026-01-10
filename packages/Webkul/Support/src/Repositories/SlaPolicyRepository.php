<?php

namespace Webkul\Support\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use App\Models\SlaPolicy;

class SlaPolicyRepository extends Repository
{
    /**
     * Specify model class name
     */
    public function model()
    {
        return SlaPolicy::class;
    }

    /**
     * Create SLA policy with rules
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            // If this is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->model->where('is_default', true)->update(['is_default' => false]);
            }

            $policy = parent::create($data);

            // Create rules if provided
            if (!empty($data['rules'])) {
                foreach ($data['rules'] as $rule) {
                    $policy->rules()->create($rule);
                }
            }

            // Create conditions if provided
            if (!empty($data['conditions'])) {
                foreach ($data['conditions'] as $condition) {
                    $policy->conditions()->create($condition);
                }
            }

            DB::commit();

            return $policy->fresh(['rules', 'conditions']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update SLA policy with rules
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $policy = $this->findOrFail($id);

            // If this is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->model->where('id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $policy = parent::update($data, $id);

            // Update rules if provided
            if (isset($data['rules'])) {
                $policy->rules()->delete();
                foreach ($data['rules'] as $rule) {
                    $policy->rules()->create($rule);
                }
            }

            // Update conditions if provided
            if (isset($data['conditions'])) {
                $policy->conditions()->delete();
                foreach ($data['conditions'] as $condition) {
                    $policy->conditions()->create($condition);
                }
            }

            DB::commit();

            return $policy->fresh(['rules', 'conditions']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get active policies
     */
    public function getActivePolicies()
    {
        return $this->model->active()->with(['rules', 'conditions'])->get();
    }

    /**
     * Get default policy
     */
    public function getDefaultPolicy()
    {
        return $this->model->default()->with(['rules', 'conditions'])->first();
    }

    /**
     * Get policy for ticket
     */
    public function getPolicyForTicket($ticket)
    {
        // Check if ticket has specific conditions that match a policy
        $policies = $this->getActivePolicies();

        foreach ($policies as $policy) {
            if ($this->policyMatchesTicket($policy, $ticket)) {
                return $policy;
            }
        }

        // Return default policy if no match
        return $this->getDefaultPolicy();
    }

    /**
     * Check if policy matches ticket
     */
    protected function policyMatchesTicket($policy, $ticket): bool
    {
        if ($policy->conditions->isEmpty()) {
            return $policy->is_default;
        }

        foreach ($policy->conditions as $condition) {
            $matches = match ($condition->condition_type) {
                'category' => $ticket->category_id == $condition->condition_value,
                'priority' => $ticket->priority == $condition->condition_value,
                'customer_type' => $ticket->customer->type ?? null == $condition->condition_value,
                default => false,
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
