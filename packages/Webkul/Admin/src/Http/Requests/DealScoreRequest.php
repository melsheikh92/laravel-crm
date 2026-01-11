<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealScoreRequest extends FormRequest
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
            'limit'                => 'nullable|integer|min:1|max:100',
            'user_id'              => 'nullable|integer|exists:users,id',
            'priority'             => 'nullable|in:high,medium,low',
            'min_score'            => 'nullable|numeric|between:0,100',
            'min_win_probability'  => 'nullable|numeric|between:0,100',
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
            'limit.integer'                   => __('admin::app.leads.scoring.limit-invalid'),
            'limit.min'                       => __('admin::app.leads.scoring.limit-min'),
            'limit.max'                       => __('admin::app.leads.scoring.limit-max'),
            'user_id.exists'                  => __('admin::app.leads.scoring.user-not-found'),
            'priority.in'                     => __('admin::app.leads.scoring.priority-invalid'),
            'min_score.numeric'               => __('admin::app.leads.scoring.min-score-invalid'),
            'min_score.between'               => __('admin::app.leads.scoring.min-score-range'),
            'min_win_probability.numeric'     => __('admin::app.leads.scoring.min-win-probability-invalid'),
            'min_win_probability.between'     => __('admin::app.leads.scoring.min-win-probability-range'),
        ];
    }
}
