@if(isset($pendingUpdates) && $pendingUpdates->count() > 0)
    <div class="card">
        <h2 class="font-bold text-sm"
            style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem;">
            @lang('marketplace::app.portal.dashboard.pending-updates.title')
        </h2>

        <div style="background-color: #fef3c7; color: #92400e; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
            <strong>{{ $pendingUpdates->count() }}</strong> {{ $pendingUpdates->count() === 1 ? 'extension has' : 'extensions have' }} updates available
        </div>

        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach($pendingUpdates as $update)
                <li class="mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                    <a href="{{ route('marketplace.extensions.show', $update['installation']->extension->slug) }}"
                       class="btn btn-link"
                       style="padding: 0; font-weight: 600; font-size: 0.875rem;">
                        {{ $update['installation']->extension->name }}
                    </a>

                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        @lang('marketplace::app.portal.dashboard.pending-updates.current'): {{ $update['current_version'] }}
                        →
                        @lang('marketplace::app.portal.dashboard.pending-updates.latest'): <strong>{{ $update['latest_version']->version }}</strong>
                    </div>

                    @if($update['latest_version']->release_notes)
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">
                            {{ Str::limit($update['latest_version']->release_notes, 100) }}
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <a href="{{ route('marketplace.extensions.index') }}" class="btn btn-link mt-4" style="display: inline-block;">
            @lang('marketplace::app.portal.dashboard.pending-updates.manage-extensions') &rarr;
        </a>
    </div>
@elseif(isset($pendingUpdates))
    <div class="card">
        <h2 class="font-bold text-sm"
            style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem;">
            @lang('marketplace::app.portal.dashboard.pending-updates.title')
        </h2>

        <div style="background-color: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 0.375rem; font-size: 0.875rem;">
            @lang('marketplace::app.portal.dashboard.pending-updates.all-up-to-date')
        </div>
    </div>
@endif
