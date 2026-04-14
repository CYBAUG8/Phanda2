@extends('Providers.layout')

@section('content')
@php
    $showArchived = $showArchived ?? false;
    $serviceMetrics = $serviceMetrics ?? ['total' => 0, 'active' => 0, 'paused' => 0, 'archived' => 0];
    $serviceFilters = $serviceFilters ?? ['q' => '', 'category' => '', 'status' => 'all', 'sort' => 'newest'];
    $openAddModalOnLoad = $errors->any() && old('_form_mode', 'add') === 'add';
    $addDisabled = $categories->isEmpty() || empty($providerProfile->service_area);
    $addDisabledReason = $categories->isEmpty()
        ? 'Create categories first before adding services.'
        : (empty($providerProfile->service_area) ? 'Complete your provider profile first.' : '');

    $sortOptions = [
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'price_low' => 'Price: Low to High',
        'price_high' => 'Price: High to Low',
        'name_asc' => 'Name: A to Z',
        'name_desc' => 'Name: Z to A',
    ];

    $hasFilters = trim((string) ($serviceFilters['q'] ?? '')) !== ''
        || trim((string) ($serviceFilters['category'] ?? '')) !== ''
        || (!$showArchived && (($serviceFilters['status'] ?? 'all') !== 'all'))
        || (($serviceFilters['sort'] ?? 'newest') !== 'newest');
@endphp

<div
    x-data="providerServicesPage({
        initialAddOpen: @js($openAddModalOnLoad),
        addDisabled: @js($addDisabled),
    })"
    class="mx-auto max-w-6xl space-y-6"
>
    <section class="provider-page-header">
        <div class="space-y-3">
            <div>
                <h1>Services</h1>
                <p class="provider-page-subtitle">
                    {{ $showArchived ? 'Archived services are kept for your records.' : 'Manage pricing, availability, and service quality details.' }}
                </p>
            </div>

            <div class="provider-segmented" role="tablist" aria-label="Service views">
                <a
                    href="{{ route('provider.services.index') }}"
                    class="provider-segmented-link {{ !$showArchived ? 'is-active' : '' }}"
                    role="tab"
                    aria-selected="{{ !$showArchived ? 'true' : 'false' }}"
                >
                    <span>Active &amp; Paused</span>
                    <span class="provider-status-badge provider-status-paused">{{ number_format((int) $serviceMetrics['total']) }}</span>
                </a>
                <a
                    href="{{ route('provider.services.index', ['archived' => 1]) }}"
                    class="provider-segmented-link {{ $showArchived ? 'is-active' : '' }}"
                    role="tab"
                    aria-selected="{{ $showArchived ? 'true' : 'false' }}"
                >
                    <span>Archived</span>
                    <span class="provider-status-badge provider-status-archived">{{ number_format((int) $serviceMetrics['archived']) }}</span>
                </a>
            </div>
        </div>

        @if(!$showArchived)
            <button
                type="button"
                @click="openAddModal()"
                class="ui-btn-primary min-h-11 justify-center px-4 py-2.5 disabled:cursor-not-allowed disabled:opacity-60"
                title="{{ $addDisabled ? $addDisabledReason : 'Add a new service offering' }}"
                @if($addDisabled) disabled @endif
            >
                <i class="fa-solid fa-plus"></i>
                <span>Add Service</span>
            </button>
        @endif
    </section>

    <section class="provider-metrics-grid" aria-label="Service summary metrics">
        <article class="provider-metric-card">
            <p class="provider-metric-label">Total Services</p>
            <p class="provider-metric-value">{{ number_format((int) $serviceMetrics['total']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Active</p>
            <p class="provider-metric-value text-emerald-700">{{ number_format((int) $serviceMetrics['active']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Paused</p>
            <p class="provider-metric-value text-slate-700">{{ number_format((int) $serviceMetrics['paused']) }}</p>
        </article>
        <article class="provider-metric-card">
            <p class="provider-metric-label">Archived</p>
            <p class="provider-metric-value text-slate-600">{{ number_format((int) $serviceMetrics['archived']) }}</p>
        </article>
    </section>

    @include('partials.ui.flash')

    @if(!$showArchived && empty($providerProfile->service_area))
        <div class="ui-alert ui-alert-error flex flex-wrap items-center justify-between gap-3">
            <span>Add your service area in Provider Profile before creating or editing services.</span>
            <a href="/providers/profile" class="ui-btn-secondary px-3 py-1.5 text-xs">Go to Profile</a>
        </div>
    @endif

    @if(!$showArchived && $categories->isEmpty())
        <div class="ui-alert ui-alert-error">
            Categories are currently unavailable. Create categories before adding services.
        </div>
    @endif

    <form action="{{ route('provider.services.index') }}" method="GET" class="provider-filter-bar">
        @if($showArchived)
            <input type="hidden" name="archived" value="1">
        @endif

        <div class="provider-filter-grid">
            <div>
                <label for="serviceSearch" class="provider-label">Search</label>
                <input
                    id="serviceSearch"
                    type="text"
                    name="q"
                    value="{{ $serviceFilters['q'] }}"
                    class="provider-input"
                    placeholder="Search by title, description, or category"
                >
            </div>

            <div>
                <label for="serviceCategory" class="provider-label">Category</label>
                <select id="serviceCategory" name="category" class="provider-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($serviceFilters['category'] === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="serviceStatus" class="provider-label">Status</label>
                <select
                    id="serviceStatus"
                    name="status"
                    class="provider-select"
                    @if($showArchived) disabled @endif
                >
                    <option value="all" @selected(($serviceFilters['status'] ?? 'all') === 'all')>All</option>
                    <option value="active" @selected(($serviceFilters['status'] ?? 'all') === 'active')>Active</option>
                    <option value="paused" @selected(($serviceFilters['status'] ?? 'all') === 'paused')>Paused</option>
                </select>
            </div>

            <div>
                <label for="serviceSort" class="provider-label">Sort</label>
                <select id="serviceSort" name="sort" class="provider-select">
                    @foreach($sortOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($serviceFilters['sort'] ?? 'newest') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="provider-filter-actions">
                <button type="submit" class="ui-btn-primary w-full justify-center">Apply</button>
                <a
                    href="{{ route('provider.services.index', $showArchived ? ['archived' => 1] : []) }}"
                    class="ui-btn-secondary w-full justify-center"
                >
                    Reset
                </a>
            </div>
        </div>
    </form>

    @if($services->total() === 0)
        <section class="provider-empty-state">
            @if($showArchived)
                <h3>No archived services found</h3>
                <p>Archived services will appear here once you archive active listings.</p>
            @elseif($hasFilters)
                <h3>No services match these filters</h3>
                <p>Adjust search terms or filters to find matching services.</p>
            @else
                <h3>No services yet</h3>
                <p>Create your first service to start receiving bookings from customers.</p>
            @endif

            @if(!$showArchived)
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <button
                        type="button"
                        @click="openAddModal()"
                        class="ui-btn-primary"
                        @if($addDisabled) disabled @endif
                        title="{{ $addDisabled ? $addDisabledReason : 'Add a new service offering' }}"
                    >
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Service</span>
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('provider.services.index') }}" class="ui-btn-secondary">Clear Filters</a>
                    @endif
                </div>
            @endif
        </section>
    @else
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($services as $service)
                @php
                    $statusClass = $showArchived
                        ? 'provider-status-archived'
                        : ($service->is_active ? 'provider-status-active' : 'provider-status-paused');
                @endphp
                <article class="ui-card flex h-full flex-col p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">{{ $service->category?->name ?? 'Uncategorised' }}</p>
                            <h2 class="mt-1 truncate text-base font-semibold text-slate-900">
                                {{ $service->title ?: ($service->category?->name ?? 'Service') }}
                            </h2>
                        </div>
                        <span class="provider-status-badge {{ $statusClass }}">
                            {{ $showArchived ? 'Archived' : ($service->is_active ? 'Active' : 'Paused') }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($service->description, 120) }}</p>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <p class="text-lg font-bold text-slate-900">R{{ number_format((float) $service->base_price, 2) }}</p>
                        <p class="text-sm text-slate-500">{{ (int) $service->min_duration }} min</p>
                    </div>

                    @if(!$showArchived)
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="startEdit({
                                    id: '{{ $service->service_id }}',
                                    category_id: '{{ $service->category_id }}',
                                    title: @js($service->title),
                                    description: @js($service->description),
                                    base_price: '{{ (float) $service->base_price }}',
                                    min_duration: '{{ (int) $service->min_duration }}'
                                })"
                                class="ui-btn-secondary min-h-11 justify-center px-3 py-2 text-sm"
                            >
                                Edit
                            </button>

                            <form
                                action="{{ route('provider.services.destroy', $service->service_id) }}"
                                method="POST"
                                data-confirm="Archive this service offering?"
                                data-confirm-title="Archive service"
                                data-confirm-text="Archive"
                                data-confirm-variant="danger"
                                data-submit-lock="true"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ui-btn-danger min-h-11 w-full justify-center px-3 py-2 text-sm" data-loading-text="Archiving...">
                                    Archive
                                </button>
                            </form>
                        </div>

                        <form action="{{ route('provider.services.toggle', $service->service_id) }}" method="POST" class="mt-2" data-submit-lock="true">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="ui-btn-secondary min-h-11 w-full justify-center px-3 py-2 text-sm {{ $service->is_active ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                data-loading-text="{{ $service->is_active ? 'Pausing...' : 'Resuming...' }}"
                            >
                                {{ $service->is_active ? 'Pause Service' : 'Resume Service' }}
                            </button>
                        </form>
                    @else
                        <p class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                            Archived services are read-only.
                        </p>
                    @endif
                </article>
            @endforeach
        </section>

        @if($services->hasPages())
            <section class="pt-2">
                {{ $services->onEachSide(1)->links() }}
            </section>
        @endif
    @endif

    @if(!$showArchived)
        <div
            x-show="addModalOpen"
            x-cloak
            x-on:keydown.escape.window="addModalOpen = false"
            class="fixed inset-0 z-50 overflow-y-auto p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="addServiceTitle"
        >
            <div class="flex min-h-full items-center justify-center">
                <div class="fixed inset-0 bg-slate-950/50" @click="addModalOpen = false"></div>
                <div class="provider-modal-panel relative z-10 w-full max-w-2xl">
                    <div class="provider-modal-header">
                        <h3 id="addServiceTitle" class="text-lg font-semibold text-slate-900">Add Service</h3>
                        <button type="button" class="text-slate-500 hover:text-slate-700" @click="addModalOpen = false" aria-label="Close add service modal">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form action="{{ route('provider.services.store') }}" method="POST" class="provider-modal-body space-y-4" data-submit-lock="true">
                        @csrf
                        <input type="hidden" name="_form_mode" value="add">

                        <div>
                            <label class="provider-label normal-case tracking-normal text-slate-700">Service Title</label>
                            <input
                                type="text"
                                name="title"
                                required
                                maxlength="255"
                                value="{{ old('title') }}"
                                class="provider-input @error('title') border-rose-400 ring-2 ring-rose-100 @enderror"
                                placeholder="e.g. Deep Home Cleaning"
                            >
                            @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Category</label>
                                <select
                                    name="category_id"
                                    required
                                    class="provider-select @error('category_id') border-rose-400 ring-2 ring-rose-100 @enderror"
                                >
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Min Duration (minutes)</label>
                                <input
                                    type="number"
                                    min="15"
                                    max="1440"
                                    name="min_duration"
                                    required
                                    value="{{ old('min_duration', 60) }}"
                                    class="provider-input @error('min_duration') border-rose-400 ring-2 ring-rose-100 @enderror"
                                >
                                @error('min_duration')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Price (R)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="99999999.99"
                                    name="base_price"
                                    required
                                    value="{{ old('base_price') }}"
                                    placeholder="0.00"
                                    class="provider-input @error('base_price') border-rose-400 ring-2 ring-rose-100 @enderror"
                                >
                                @error('base_price')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Location</label>
                                <input
                                    type="text"
                                    class="provider-input bg-slate-50 text-slate-500"
                                    value="{{ $providerProfile->service_area }}"
                                    readonly
                                >
                            </div>
                        </div>

                        <div>
                            <label class="provider-label normal-case tracking-normal text-slate-700">Description</label>
                            <textarea
                                name="description"
                                rows="4"
                                required
                                maxlength="65535"
                                class="provider-textarea @error('description') border-rose-400 ring-2 ring-rose-100 @enderror"
                                placeholder="Describe what's included, equipment used, and what customers should expect."
                            >{{ old('description') }}</textarea>
                            @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="provider-modal-footer -mx-5 -mb-5 mt-2">
                            <button type="button" @click="addModalOpen = false" class="ui-btn-secondary px-4 py-2">Cancel</button>
                            <button type="submit" class="ui-btn-primary px-4 py-2" data-loading-text="Saving...">Save Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            x-show="editModalOpen"
            x-cloak
            x-on:keydown.escape.window="editModalOpen = false"
            class="fixed inset-0 z-50 overflow-y-auto p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="editServiceTitle"
        >
            <div class="flex min-h-full items-center justify-center">
                <div class="fixed inset-0 bg-slate-950/50" @click="editModalOpen = false"></div>
                <div class="provider-modal-panel relative z-10 w-full max-w-2xl">
                    <div class="provider-modal-header">
                        <h3 id="editServiceTitle" class="text-lg font-semibold text-slate-900">Edit Service</h3>
                        <button type="button" class="text-slate-500 hover:text-slate-700" @click="editModalOpen = false" aria-label="Close edit service modal">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('/providers/services') }}/' + editingService.id" method="POST" class="provider-modal-body space-y-4" data-submit-lock="true">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form_mode" value="edit">

                        <div>
                            <label class="provider-label normal-case tracking-normal text-slate-700">Service Title</label>
                            <input type="text" name="title" x-model="editingService.title" required maxlength="255" class="provider-input">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Category</label>
                                <select name="category_id" x-model="editingService.category_id" required class="provider-select">
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Min Duration (minutes)</label>
                                <input type="number" min="15" max="1440" name="min_duration" x-model="editingService.min_duration" required class="provider-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Price (R)</label>
                                <input type="number" step="0.01" min="0" max="99999999.99" name="base_price" x-model="editingService.base_price" required class="provider-input">
                            </div>
                            <div>
                                <label class="provider-label normal-case tracking-normal text-slate-700">Location</label>
                                <input type="text" value="{{ $providerProfile->service_area }}" class="provider-input bg-slate-50 text-slate-500" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="provider-label normal-case tracking-normal text-slate-700">Description</label>
                            <textarea name="description" rows="4" x-model="editingService.description" maxlength="65535" required class="provider-textarea"></textarea>
                        </div>

                        <div class="provider-modal-footer -mx-5 -mb-5 mt-2">
                            <button type="button" @click="editModalOpen = false" class="ui-btn-secondary px-4 py-2">Cancel</button>
                            <button type="submit" class="ui-btn-primary px-4 py-2" data-loading-text="Saving...">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function providerServicesPage(config) {
    return {
        addModalOpen: Boolean(config.initialAddOpen),
        editModalOpen: false,
        addDisabled: Boolean(config.addDisabled),
        editingService: {
            id: '',
            category_id: '',
            title: '',
            description: '',
            base_price: '',
            min_duration: '',
        },
        openAddModal() {
            if (this.addDisabled) {
                return;
            }
            this.addModalOpen = true;
        },
        startEdit(service) {
            this.editingService = { ...service };
            this.editModalOpen = true;
        },
    };
}

(() => {
    if (window.__providerSubmitLockInit) {
        return;
    }
    window.__providerSubmitLockInit = true;

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.submitLock !== 'true') {
            return;
        }

        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        if (submitter.disabled) {
            event.preventDefault();
            return;
        }

        submitter.disabled = true;
        const loadingText = submitter.dataset.loadingText;
        if (loadingText) {
            submitter.dataset.originalText = submitter.innerHTML;
            submitter.innerHTML = loadingText;
        }
    });
})();
</script>
@endpush
@endsection