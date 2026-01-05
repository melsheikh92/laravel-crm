<?php

namespace App\Http\Controllers;

use App\Repositories\WhatsAppTemplateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\WhatsAppTemplateDataGrid;

class WhatsAppTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected WhatsAppTemplateRepository $whatsAppTemplateRepository
    ) {}

    /**
     * Display a listing of WhatsApp templates.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return app(WhatsAppTemplateDataGrid::class)->toJson();
        }

        return view('whatsapp.templates.index');
    }

    /**
     * Show the form for creating a new WhatsApp template.
     */
    public function create(): View
    {
        return view('whatsapp.templates.create');
    }

    /**
     * Store a newly created WhatsApp template in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:whatsapp_templates,name',
            'language'         => 'required|string|max:10',
            'status'           => 'required|in:APPROVED,PENDING,REJECTED',
            'category'         => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'body'             => 'required|string',
            'header'           => 'nullable|string',
            'footer'           => 'nullable|string',
            'buttons'          => 'nullable|array',
            'meta_template_id' => 'nullable|string|max:255',
        ]);

        Event::dispatch('whatsapp.template.create.before');

        $validated['user_id'] = auth()->guard('user')->user()->id;

        $template = $this->whatsAppTemplateRepository->create($validated);

        Event::dispatch('whatsapp.template.create.after', $template);

        if ($request->ajax()) {
            return response()->json([
                'data'    => $template,
                'message' => 'WhatsApp template created successfully.',
            ], 201);
        }

        session()->flash('success', 'WhatsApp template created successfully.');

        return redirect()->route('whatsapp.templates.index');
    }

    /**
     * Display the specified WhatsApp template.
     */
    public function show(int $id): View|JsonResponse
    {
        $template = $this->whatsAppTemplateRepository->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'data' => $template,
            ]);
        }

        return view('whatsapp.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified WhatsApp template.
     */
    public function edit(int $id): View
    {
        $template = $this->whatsAppTemplateRepository->findOrFail($id);

        return view('whatsapp.templates.edit', compact('template'));
    }

    /**
     * Update the specified WhatsApp template in storage.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:whatsapp_templates,name,'.$id,
            'language'         => 'required|string|max:10',
            'status'           => 'required|in:APPROVED,PENDING,REJECTED',
            'category'         => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'body'             => 'required|string',
            'header'           => 'nullable|string',
            'footer'           => 'nullable|string',
            'buttons'          => 'nullable|array',
            'meta_template_id' => 'nullable|string|max:255',
        ]);

        Event::dispatch('whatsapp.template.update.before', $id);

        $template = $this->whatsAppTemplateRepository->update($validated, $id);

        Event::dispatch('whatsapp.template.update.after', $template);

        if ($request->ajax()) {
            return response()->json([
                'data'    => $template,
                'message' => 'WhatsApp template updated successfully.',
            ]);
        }

        session()->flash('success', 'WhatsApp template updated successfully.');

        return redirect()->route('whatsapp.templates.index');
    }

    /**
     * Remove the specified WhatsApp template from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = $this->whatsAppTemplateRepository->findOrFail($id);

        try {
            Event::dispatch('whatsapp.template.delete.before', $id);

            $template->delete();

            Event::dispatch('whatsapp.template.delete.after', $id);

            return response()->json([
                'message' => 'WhatsApp template deleted successfully.',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Failed to delete WhatsApp template.',
            ], 400);
        }
    }
}
