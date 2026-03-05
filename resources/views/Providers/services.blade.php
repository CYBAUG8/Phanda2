@extends('providers.layout')

@section('content')
<div class="container py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Services</h2>
            <p class="text-muted mb-0">Manage the services you offer to customers</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#addServiceCard">
            + Add New Service
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <h6 class="text-muted">Total Services</h6>
                <h3 class="fw-bold">{{ $services->count() }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <h6 class="text-muted">Active Services</h6>
                <h3 class="fw-bold text-success">
                    {{ $services->where('active', true)->count() }}
                </h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <h6 class="text-muted">Inactive Services</h6>
                <h3 class="fw-bold text-danger">
                    {{ $services->where('active', false)->count() }}
                </h3>
            </div>
        </div>
    </div>

    {{-- Services Grid --}}
    <div class="row g-4">
        @forelse($services as $service)
        <div class="col-md-6 col-lg-4" id="service-{{ $service->service_id }}">
            <div class="card shadow-sm border-0 h-100 service-card">

                <div class="card-body d-flex flex-column">
                    
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold service-name">{{ $service->name }}</h5>

                        <span class="badge 
                            {{ $service->active ? 'bg-success' : 'bg-secondary' }}
                            service-status">
                            {{ $service->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="text-muted small flex-grow-1 service-description">
                        {{ $service->description ?? 'No description provided.' }}
                    </p>

                    <h4 class="fw-bold mb-3 service-price">
                        R{{ number_format($service->price, 2) }}
                    </h4>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary edit-service"
                            data-id="{{ $service->service_id }}"
                            data-name="{{ $service->name }}"
                            data-description="{{ $service->description }}"
                            data-price="{{ $service->price }}">
                            Edit
                        </button>

                        <button class="btn btn-sm btn-outline-warning toggle-active"
                            data-id="{{ $service->service_id }}">
                            {{ $service->active ? 'Deactivate' : 'Activate' }}
                        </button>

                        <button class="btn btn-sm btn-outline-danger delete-service"
                            data-id="{{ $service->service_id }}">
                            Delete
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center text-muted">
                <p>You have not added any services yet.</p>
            </div>
        @endforelse
    </div>

    {{-- Add Service Card --}}
    <div class="collapse mt-5" id="addServiceCard">
        <div class="card shadow-sm border-0 p-4">
            <h4 class="fw-bold mb-3">Add New Service</h4>

            <form method="POST" action="{{ route('provider.services.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Service Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price (R)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>

                <button class="btn btn-success">Save Service</button>
            </form>
        </div>
    </div>

</div>

<style>
.service-card {
    transition: 0.2s ease-in-out;
}
.service-card:hover {
    transform: translateY(-4px);
}
</style>

@endsection