@if(isset($installedExtensions) && $installedExtensions->count() > 0)
    <div class="card">
        <h2 class="font-bold text-sm"
            style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem;">
            @lang('marketplace::app.portal.dashboard.installed-extensions.title')
        </h2>

        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach($installedExtensions as $installation)
                <li class="mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <a href="{{ route('marketplace.extensions.show', $installation->extension->slug) }}"
                           class="btn btn-link"
                           style="padding: 0; font-weight: 600; font-size: 0.875rem;">
                            {{ $installation->extension->name }}
                        </a>

                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;
                            {{ $installation->status === 'active' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #f3f4f6; color: #6b7280;' }}">
                            {{ ucfirst($installation->status) }}
                        </span>
                    </div>

                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                        @lang('marketplace::app.portal.dashboard.installed-extensions.version'): {{ $installation->version->version ?? 'N/A' }}
                        •
                        @lang('marketplace::app.portal.dashboard.installed-extensions.installed'): {{ $installation->installed_at->format('M d, Y') }}
                    </div>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('marketplace.extensions.index') }}" class="btn btn-link mt-4" style="display: inline-block;">
            @lang('marketplace::app.portal.dashboard.installed-extensions.browse-marketplace') &rarr;
        </a>
    </div>
@endif
