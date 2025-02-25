@extends('adminlte::page')

@section('title', 'Create Bandwidth Plan')

@section('content_header')
    <h1>Create New Bandwidth Plan</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('bandwidths.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Bandwidth Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rate_up">Upload Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       step="0.01"
                                       class="form-control @error('rate_up') is-invalid @enderror" 
                                       id="rate_up" 
                                       name="rate_up" 
                                       value="{{ old('rate_up') }}" 
                                       required>
                                <div class="input-group-append">
                                    <select class="form-control @error('rate_up_unit') is-invalid @enderror" 
                                            name="rate_up_unit">
                                        <option value="Kbps" {{ old('rate_up_unit') == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                        <option value="Mbps" {{ old('rate_up_unit', 'Mbps') == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                    </select>
                                </div>
                            </div>
                            @error('rate_up')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('rate_up_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Will show as M or k in Mikrotik
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="rate_down">Download Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       step="0.01"
                                       class="form-control @error('rate_down') is-invalid @enderror" 
                                       id="rate_down" 
                                       name="rate_down" 
                                       value="{{ old('rate_down') }}" 
                                       required>
                                <div class="input-group-append">
                                    <select class="form-control @error('rate_down_unit') is-invalid @enderror" 
                                            name="rate_down_unit">
                                        <option value="Kbps" {{ old('rate_down_unit') == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                        <option value="Mbps" {{ old('rate_down_unit', 'Mbps') == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                    </select>
                                </div>
                            </div>
                            @error('rate_down')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('rate_down_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Will show as M or k in Mikrotik
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Burst Settings (Optional)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="burst_limit">Burst Limit</label>
                            <input type="text" 
                                   class="form-control @error('burst_limit') is-invalid @enderror" 
                                   id="burst_limit" 
                                   name="burst_limit" 
                                   value="{{ old('burst_limit') }}"
                                   placeholder="Format: 2M/2M">
                            @error('burst_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="burst_threshold">Burst Threshold</label>
                            <input type="text" 
                                   class="form-control @error('burst_threshold') is-invalid @enderror" 
                                   id="burst_threshold" 
                                   name="burst_threshold" 
                                   value="{{ old('burst_threshold') }}"
                                   placeholder="Format: 1M/1M">
                            @error('burst_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="burst_time">Burst Time</label>
                            <input type="text" 
                                   class="form-control @error('burst_time') is-invalid @enderror" 
                                   id="burst_time" 
                                   name="burst_time" 
                                   value="{{ old('burst_time') }}"
                                   placeholder="Format: 10/10">
                            @error('burst_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Bandwidth Plan
                </button>
                <a href="{{ route('bandwidths.index') }}" class="btn btn-default float-right">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Burst Limit Presets</h3>
        </div>
        <div class="card-body p-0">
            <div class="list-group">
                <div class="list-group-item bg-primary text-white">2x Speed Burst</div>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('2M/2M', '1M/1M', '10/10')">2M to 4M</a>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('3M/3M', '1.5M/1.5M', '10/10')">3M to 6M</a>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('4M/4M', '2M/2M', '10/10')">4M to 8M</a>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('5M/5M', '2.5M/2.5M', '10/10')">5M to 10M</a>
                <div class="list-group-item bg-primary text-white">Up to 1MB</div>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('1M/1M', '512K/512K', '10/10')">1M up to 2M</a>
                <a href="#" class="list-group-item list-group-item-action" onclick="setBurst('2M/2M', '1M/1M', '10/10')">2M up to 3M</a>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
function setBurst(limit, threshold, time) {
    document.getElementById('burst_limit').value = limit;
    document.getElementById('burst_threshold').value = threshold;
    document.getElementById('burst_time').value = time;
}
</script>
@endpush
