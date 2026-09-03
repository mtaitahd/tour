@extends('admin.layouts.app')
@section('title', 'Editor Dashboard')

@section('content')
  <div class="pagetitle">
    <h1>Editor Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Package Editor Panel</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mt-3">Welcome, {{ $editor->name }}</h5>
            <p class="mb-0">
              Your Package Editor workspace is ready. Package management tools arrive in the
              next phase — for now you can sign out safely.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection