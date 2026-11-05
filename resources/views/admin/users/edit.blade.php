@extends('admin.layouts.app')
@section('title', 'Edit User')

@section('content')
  <div class="pagetitle"><h1>Edit User</h1></div>
  <section class="section">
    <div class="card">
      <div class="card-body pt-3">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="row g-3">
          @csrf
          @method('PATCH')

          <div class="col-md-6">
            <label for="name" class="form-label">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required maxlength="255">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                   class="form-control @error('email') is-invalid @enderror" required maxlength="255">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-12 text-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection
