<?php

namespace Webkul\Admin\Http\Controllers\Marketing;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketing\Repositories\EmailTemplateRepository;
use Webkul\Marketing\Services\TemplateService;

class TemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateRepository $templateRepository,
        protected TemplateService $templateService
    ) {}

    /**
     * Display a listing of templates.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $templates = $this->templateRepository->all();

            return response()->json([
                'data' => $templates,
            ]);
        }

        return view('admin::marketing.templates.index');
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): View
    {
        return view('admin::marketing.templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|in:system,custom',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $template = $this->templateService->create(request()->all());

            return response()->json([
                'data' => $template,
                'message' => trans('admin::app.marketing.templates.create-success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified template.
     */
    public function show(int $id): View|JsonResponse
    {
        $template = $this->templateRepository->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'data' => $template,
            ]);
        }

        return view('admin::marketing.templates.view', compact('template'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(int $id): View|JsonResponse
    {
        $template = $this->templateRepository->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'data' => $template,
            ]);
        }

        return view('admin::marketing.templates.edit', compact('template'));
    }

    /**
     * Update the specified template.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|in:system,custom',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $template = $this->templateService->update(request()->all(), $id);

            return response()->json([
                'data' => $template,
                'message' => trans('admin::app.marketing.templates.update-success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified template.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = $this->templateRepository->findOrFail($id);

        // Check if template is used in any campaigns
        if ($template->campaigns && $template->campaigns->count() > 0) {
            return response()->json([
                'message' => trans('admin::app.marketing.templates.cannot-delete'),
            ], 400);
        }

        $this->templateRepository->delete($id);

        return response()->json([
            'message' => trans('admin::app.marketing.templates.delete-success'),
        ]);
    }

    /**
     * Preview template with sample data.
     */
    public function preview(int $id): JsonResponse
    {
        $this->validate(request(), [
            'person_id' => 'nullable|exists:persons,id',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        try {
            if (request('person_id')) {
                $rendered = $this->templateService->renderForPerson($id, request('person_id'));
            } elseif (request('lead_id')) {
                $rendered = $this->templateService->renderForLead($id, request('lead_id'));
            } else {
                $rendered = $this->templateService->render($id, [
                    'name' => 'John Doe',
                    'company' => 'Example Company',
                    'email' => 'john@example.com',
                    'phone' => '+1234567890',
                ]);
            }

            return response()->json([
                'data' => $rendered,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

