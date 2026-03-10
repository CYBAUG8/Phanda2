@extends('Providers.layout')

@section('content')
<div x-data="{
        addModalOpen: @js($errors->any()),
        editModalOpen: false,
        editingService: {}
    }" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @php
        $addDisabled = $categories->isEmpty() || empty($providerProfile->service_area);
        $addDisabledReason = $categories->isEmpty()
            ? 'Create categories first before adding services.'
            : (empty($providerProfile->service_area) ? 'Complete your provider profile first.' : '');
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Services</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your offerings and pricing.</p>
        </div>
        <button @click="addModalOpen = true"
                class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-50"
                title="{{ $addDisabled ? $addDisabledReason : 'Add a new service offering' }}"
                @if($addDisabled) disabled @endif>
            <i class="fa-solid fa-plus"></i>
            Add Service
        </button>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($services->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-base font-medium text-gray-800">No services yet</p>
            <p class="mt-1 text-sm text-gray-500">Add your first service to get started.</p>
            <button @click="addModalOpen = true"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-50"
                    title="{{ $addDisabled ? $addDisabledReason : 'Add a new service offering' }}"
                    @if($addDisabled) disabled @endif>
                <i class="fa-solid fa-plus"></i>
                Add Service
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($services as $ps)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500">{{ $ps->category?->name ?? 'Uncategorised' }}</p>
                            <h3 class="mt-1 text-base font-semibold text-gray-900">{{ $ps->title ?: ($ps->category?->name ?? 'Service') }}</h3>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $ps->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $ps->is_active ? 'Active' : 'Paused' }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($ps->description, 90) }}</p>

                    <div class="mt-3 flex items-center justify-between text-sm">
                        <p class="font-semibold text-gray-900">R{{ number_format((float)$ps->base_price, 2) }}</p>
                        <p class="text-gray-500">{{ (int)$ps->min_duration }} min</p>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <button @click="editingService = {
                                    id: '{{ $ps->service_id }}',
                                    category_id: '{{ $ps->category_id }}',
                                    title: @js($ps->title),
                                    description: @js($ps->description),
                                    base_price: '{{ (float)$ps->base_price }}',
                                    min_duration: '{{ (int)$ps->min_duration }}'
                                }; editModalOpen = true"
                                class="col-span-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Edit
                        </button>

                        <form action="{{ route('provider.services.destroy', $ps->service_id) }}" method="POST" onsubmit="return confirm('Remove this service offering?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('provider.services.toggle', $ps->service_id) }}" method="POST" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full rounded-lg px-3 py-2 text-sm font-medium transition {{ $ps->is_active ? 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                            {{ $ps->is_active ? 'Pause Service' : 'Resume Service' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="addModalOpen = false"></div>
            <div class="relative z-10 w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Add Service</h3>
                    <button @click="addModalOpen = false" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form action="{{ route('provider.services.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Service Title</label>
                        <input type="text" name="title" required maxlength="255" value="{{ old('title') }}"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900 @error('title') border-red-400 ring-1 ring-red-200 @enderror">
                        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" required
                                    class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900 @error('category_id') border-red-400 ring-1 ring-red-200 @enderror">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Min Duration (minutes)</label>
                            <input type="number" min="15" max="1440" name="min_duration" required value="{{ old('min_duration', 60) }}"
                                   class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900 @error('min_duration') border-red-400 ring-1 ring-red-200 @enderror">
                            @error('min_duration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Price (R)</label>
                            <input type="number" step="0.01" min="0" max="99999999.99" name="base_price" required value="{{ old('base_price') }}" placeholder="0.00"
                                   class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900 @error('base_price') border-red-400 ring-1 ring-red-200 @enderror">
                            @error('base_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>


                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="4" required maxlength="65535" class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900 @error('description') border-red-400 ring-1 ring-red-200 @enderror">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="addModalOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="editModalOpen = false"></div>
            <div class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Service</h3>
                    <button @click="editModalOpen = false" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="'{{ url('/providers/services') }}/' + editingService.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Service Title</label>
                        <input type="text" name="title" x-model="editingService.title" required maxlength="255"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" x-model="editingService.category_id" required
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" x-model="editingService.description" maxlength="65535" required
                                  class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Price (R)</label>
                            <input type="number" step="0.01" min="0" max="99999999.99" name="base_price" x-model="editingService.base_price" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Duration (min)</label>
                            <input type="number" min="15" max="1440" name="min_duration" x-model="editingService.min_duration" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                    </div>


                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editModalOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection