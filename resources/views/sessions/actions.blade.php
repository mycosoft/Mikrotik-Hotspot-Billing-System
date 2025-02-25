<div class="btn-group">
    @can('disconnect sessions')
        <button type="button" class="btn btn-sm btn-danger" onclick="disconnectSession({{ $session->id }})">
            <i class="fas fa-power-off"></i> Disconnect
        </button>
    @endcan
</div>
