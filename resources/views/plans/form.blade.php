{{-- Remove layout extension since this is a partial --}}
<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-group">
        <label for="name">Plan Name</label>
        <input type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name', isset($plan) ? $plan->name : '') }}"
               required>
        <small class="form-text text-muted">Only letters and numbers are allowed (e.g. 10MBPSVIP). Spaces and special characters will be removed.</small>
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="type">Plan Type</label>
        <select class="form-control @error('type') is-invalid @enderror" 
                id="type" 
                name="type" 
                required>
            <option value="limited" {{ old('type', isset($plan) ? $plan->type : '') == 'limited' ? 'selected' : '' }}>Limited</option>
            <option value="unlimited" {{ old('type', isset($plan) ? $plan->type : '') == 'unlimited' ? 'selected' : '' }}>Unlimited</option>
        </select>
        @error('type')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group" id="limitTypeGroup">
        <label for="limit_type">Limit Type</label>
        <select class="form-control @error('limit_type') is-invalid @enderror" 
                id="limit_type" 
                name="limit_type">
            <option value="time" {{ old('limit_type', isset($plan) ? $plan->limit_type : '') == 'time' ? 'selected' : '' }}>Time Based</option>
            <option value="data" {{ old('limit_type', isset($plan) ? $plan->limit_type : '') == 'data' ? 'selected' : '' }}>Data Based</option>
            <option value="both" {{ old('limit_type', isset($plan) ? $plan->limit_type : '') == 'both' ? 'selected' : '' }}>Both</option>
        </select>
        @error('limit_type')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group" id="timeLimitGroup">
        <label for="time_limit">Time Limit (Minutes)</label>
        <input type="number" 
               class="form-control @error('time_limit') is-invalid @enderror" 
               id="time_limit" 
               name="time_limit" 
               value="{{ old('time_limit', isset($plan) ? $plan->time_limit : '') }}"
               min="1">
        <small class="form-text text-muted">
            Examples: 60 (1 hour), 1440 (1 day), 10080 (1 week)
        </small>
        @error('time_limit')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group" id="dataLimitGroup">
        <label for="data_limit">Data Limit (MB)</label>
        <input type="number" 
               class="form-control @error('data_limit') is-invalid @enderror" 
               id="data_limit" 
               name="data_limit" 
               value="{{ old('data_limit', isset($plan) ? $plan->data_limit : '') }}"
               min="1">
        <small class="form-text text-muted">
            Examples: 1024 (1 GB), 5120 (5 GB), 10240 (10 GB)
        </small>
        @error('data_limit')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="price">Price (UGX)</label>
        <input type="number" 
               class="form-control @error('price') is-invalid @enderror" 
               id="price" 
               name="price" 
               value="{{ old('price', isset($plan) ? $plan->price : '') }}"
               step="100"
               required>
        @error('price')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="validity_days">Validity (Days)</label>
        <input type="number" 
               class="form-control @error('validity_days') is-invalid @enderror" 
               id="validity_days" 
               name="validity_days" 
               value="{{ old('validity_days', isset($plan) ? $plan->validity_days : '') }}"
               min="1"
               required>
        @error('validity_days')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="bandwidth_id">Bandwidth</label>
        <select class="form-control select2 @error('bandwidth_id') is-invalid @enderror" 
                id="bandwidth_id" 
                name="bandwidth_id"
                required>
            <option value="">Select a bandwidth</option>
            @foreach($bandwidths as $bandwidth)
                <option value="{{ $bandwidth->id }}" 
                        {{ old('bandwidth_id', isset($plan) ? $plan->bandwidth_id : '') == $bandwidth->id ? 'selected' : '' }}>
                    {{ $bandwidth->name }} 
                    ({{ $bandwidth->upload_speed }}Kbps/{{ $bandwidth->download_speed }}Kbps)
                </option>
            @endforeach
        </select>
        @error('bandwidth_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="router_id">Router</label>
        <select class="form-control select2 @error('router_id') is-invalid @enderror" 
                id="router_id" 
                name="router_id"
                required>
            <option value="">Select a router</option>
            @foreach($routers as $router)
                <option value="{{ $router->id }}" 
                        {{ old('router_id', isset($plan) ? $plan->router_id : '') == $router->id ? 'selected' : '' }}>
                    {{ $router->name }} ({{ $router->ip_address }})
                </option>
            @endforeach
        </select>
        @error('router_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="simultaneous_sessions">Simultaneous Sessions</label>
        <input type="number" 
               class="form-control @error('simultaneous_sessions') is-invalid @enderror" 
               id="simultaneous_sessions" 
               name="simultaneous_sessions" 
               value="{{ old('simultaneous_sessions', isset($plan) ? $plan->simultaneous_sessions : '1') }}"
               min="1"
               required>
        @error('simultaneous_sessions')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <div class="custom-control custom-switch">
            <input type="checkbox" 
                   class="custom-control-input" 
                   id="is_active" 
                   name="is_active"
                   value="1"
                   {{ old('is_active', isset($plan) ? $plan->is_active : true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Active</label>
        </div>
    </div>
</div>
