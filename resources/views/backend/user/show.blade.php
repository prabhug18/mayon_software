
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>User Details</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Mobile:</strong> {{ $user->mobile }}</p>
            <p><strong>Role:</strong> {{ $user->roles->pluck('name')->join(', ') }}</p>
            <p><strong>Status:</strong> {{ $user->status }}</p>
            <p><strong>Created At:</strong> {{ $user->created_at }}</p>
            <p><strong>Updated At:</strong> {{ $user->updated_at }}</p>
        </div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
