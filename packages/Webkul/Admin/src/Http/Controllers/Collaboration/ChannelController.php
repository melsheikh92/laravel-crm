<?php

namespace Webkul\Admin\Http\Controllers\Collaboration;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Collaboration\Repositories\ChatChannelRepository;
use Webkul\Collaboration\Services\ChatService;

class ChannelController extends Controller
{
    public function __construct(
        protected ChatChannelRepository $channelRepository,
        protected ChatService $chatService
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\Collaboration\ChannelDataGrid::class)->process();
        }

        return view('admin::collaboration.channels.index');
    }

    public function create(): View
    {
        return view('admin::collaboration.channels.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:direct,group',
            'description' => 'nullable|string',
        ]);

        $channel = $this->chatService->createChannel(request()->all());

        if (request()->ajax()) {
            return response()->json([
                'data' => $channel->load(['members', 'creator']),
            ]);
        }

        session()->flash('success', trans('admin::app.collaboration.channels.create.success') ?: 'Channel created successfully.');

        return redirect()->route('admin.collaboration.channels.index');
    }

    public function show(int $id)
    {
        $channel = $this->channelRepository
            ->with([
                'members.user',
                'creator',
                'messages' => function ($query) {
                    $query->where('is_deleted', false)
                        ->with('user')
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'data' => $channel,
            ]);
        }

        return view('admin::collaboration.channels.show', [
            'channel' => $channel,
        ]);
    }

    public function edit(int $id): View
    {
        $channel = $this->channelRepository->findOrFail($id);

        return view('admin::collaboration.channels.edit', [
            'channel' => $channel,
        ]);
    }

    public function update(int $id)
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:direct,group',
            'description' => 'nullable|string',
        ]);

        $channel = $this->chatService->updateChannel($id, request()->all());

        if (request()->ajax()) {
            return response()->json([
                'data' => $channel->load(['members', 'creator']),
            ]);
        }

        session()->flash('success', trans('admin::app.collaboration.channels.edit.success') ?: 'Channel updated successfully.');

        return redirect()->route('admin.collaboration.channels.index');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->chatService->deleteChannel($id);

            return response()->json([
                'message' => trans('admin::app.collaboration.channels.delete.success') ?: 'Channel deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.collaboration.channels.delete.error') ?: 'Error deleting channel.',
            ], 500);
        }
    }
}

