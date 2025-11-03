<section>
    <h4 class="mb-3">Profile Information</h4>
    <p class="text-muted mb-4">Update your account's profile information, branding, and email address.</p>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name*</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-warning small">
                            Your email address is unverified.
                            <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Click here to re-send the verification email.</button>
                            </form>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                            <p class="text-success small mt-1">A new verification link has been sent to your email address.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $user->company_name) }}">
                @error('company_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">This will appear on your PDF charts</small>
            </div>

            <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
                @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">This will appear on your PDF charts</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="logo" class="form-label">Logo Image</label>
            @if($user->logo_path)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $user->logo_path) }}" alt="Current Logo" style="max-height: 50px;" class="d-block mb-1">
                    <small class="text-muted">Current logo</small>
                </div>
            @endif
            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
            @error('logo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Max size: 2MB. This will appear on your PDF charts.</small>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</section>
