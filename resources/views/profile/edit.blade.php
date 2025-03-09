@extends('adminlte::page')

@section('title', 'Profile Settings')

@section('content_header')
    <h1>Profile Settings</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="https://robohash.org/{{ Auth::id() }}?set=set3&size=100x100&bgset=bg1"
                         alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>

                <p class="text-muted text-center">
                    {{ Auth::user()->email }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#profile" data-toggle="tab">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#password" data-toggle="tab">
                            <i class="fas fa-lock mr-1"></i> Password
                        </a>
                    </li>

                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane active" id="profile">
                        <form method="post" action="{{ route('profile.update') }}" class="form-horizontal">
                            @csrf
                            @method('patch')

                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', Auth::user()->name) }}"
                                           required autofocus>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-sm-2 col-form-label">Email</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                                           required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    
                                    @if (Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! Auth::user()->hasVerifiedEmail())
                                        <div class="mt-2">
                                            <p class="text-sm text-muted">
                                                Your email address is unverified.
                                                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-muted">
                                                        Click here to re-send verification email
                                                    </button>
                                                </form>
                                            </p>
                                            @if (session('status') === 'verification-link-sent')
                                                <div class="alert alert-success mt-2">
                                                    A new verification link has been sent to your email address.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Save Changes
                                    </button>

                                    @if (session('status') === 'profile-updated')
                                        <span class="text-success ml-2">
                                            <i class="fas fa-check"></i> Saved successfully
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Password Tab -->
                    <div class="tab-pane" id="password">
                        <form method="post" action="{{ route('password.update') }}" class="form-horizontal">
                            @csrf
                            @method('put')

                            <div class="form-group row">
                                <label for="current_password" class="col-sm-3 col-form-label">Current Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-sm-3 col-form-label">New Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password_confirmation" class="col-sm-3 col-form-label">Confirm Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                           id="password_confirmation" name="password_confirmation" required>
                                    @error('password_confirmation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key mr-1"></i> Update Password
                                    </button>

                                    @if (session('status') === 'password-updated')
                                        <span class="text-success ml-2">
                                            <i class="fas fa-check"></i> Password updated successfully
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Delete Account Tab -->
                    @if(Auth::user()->can('delete users'))
                    <div class="tab-pane" id="delete">
                        <div class="alert alert-danger">
                            <h5><i class="icon fas fa-ban"></i> Warning!</h5>
                            Once your account is deleted, all of its resources and data will be permanently deleted.
                            Before deleting your account, please download any data or information that you wish to retain.
                        </div>

                        <form method="post" action="{{ route('profile.destroy') }}" class="form-horizontal">
                            @csrf
                            @method('delete')

                            <div class="form-group row">
                                <label for="password" class="col-sm-3 col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required
                                           placeholder="Enter your password to confirm">
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash mr-1"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .nav-pills .nav-link:not(.active):hover {
        color: #007bff;
    }
</style>
@stop

@section('js')
<script>
    // Show active tab based on hash in URL
    $(document).ready(function() {
        if (window.location.hash) {
            $('a[href="' + window.location.hash + '"]').tab('show');
        }
        
        // Update hash in URL when tab changes
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });

        // Auto-hide success messages
        $('.text-success').delay(3000).fadeOut();
    });
</script>
@stop
