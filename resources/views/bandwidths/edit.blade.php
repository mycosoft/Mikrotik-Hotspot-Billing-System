@extends('adminlte::page')

@section('title', 'Edit Bandwidth')

@section('content_header')
    <h1>Edit Bandwidth: {{ $bandwidth->name }}</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('bandwidths.update', $bandwidth) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Bandwidth Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $bandwidth->name) }}" 
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
                                       value="{{ old('rate_up', $bandwidth->rate_up) }}" 
                                       required>
                                <div class="input-group-append">
                                    <select class="form-control @error('rate_up_unit') is-invalid @enderror" 
                                            name="rate_up_unit">
                                        <option value="Kbps" {{ old('rate_up_unit', $bandwidth->rate_up_unit) == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                        <option value="Mbps" {{ old('rate_up_unit', $bandwidth->rate_up_unit) == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                    </select>
                                </div>
                                @error('rate_up')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                                       value="{{ old('rate_down', $bandwidth->rate_down) }}" 
                                       required>
                                <div class="input-group-append">
                                    <select class="form-control @error('rate_down_unit') is-invalid @enderror" 
                                            name="rate_down_unit">
                                        <option value="Kbps" {{ old('rate_down_unit', $bandwidth->rate_down_unit) == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                        <option value="Mbps" {{ old('rate_down_unit', $bandwidth->rate_down_unit) == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                    </select>
                                </div>
                                @error('rate_down')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                               {{ old('is_active', $bandwidth->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Bandwidth
                </button>
                <a href="{{ route('bandwidths.index') }}" class="btn btn-default float-right">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@push('js')
<script>
$(function() {
    // Handle status toggle
    $('#is_active').on('change', function() {
        const badge = $('#status-badge');
        if (this.checked) {
            badge.removeClass('badge-danger').addClass('badge-success');
            badge.text('Active');
        } else {
            badge.removeClass('badge-success').addClass('badge-danger');
            badge.text('Inactive');
        }
    });
});
</script>
@endpush

@push('css')
<style>
.status-badge {
    padding: 8px 12px;
    transition: all 0.3s ease;
}
.status-badge:hover {
    opacity: 0.8;
}
</style>
@endpush
