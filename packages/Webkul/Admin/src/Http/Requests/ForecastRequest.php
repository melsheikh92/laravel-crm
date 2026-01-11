<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForecastRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'      => 'required|integer|exists:users,id',
            'period_type'  => 'required|in:week,month,quarter',
            'period_start' => 'nullable|date',
            'team_id'      => 'nullable|integer|exists:groups,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required'       => __('admin::app.leads.forecasts.user-id-required'),
            'user_id.exists'         => __('admin::app.leads.forecasts.user-not-found'),
            'period_type.required'   => __('admin::app.leads.forecasts.period-type-required'),
            'period_type.in'         => __('admin::app.leads.forecasts.period-type-invalid'),
            'period_start.date'      => __('admin::app.leads.forecasts.period-start-invalid'),
            'team_id.exists'         => __('admin::app.leads.forecasts.team-not-found'),
        ];
    }
}
