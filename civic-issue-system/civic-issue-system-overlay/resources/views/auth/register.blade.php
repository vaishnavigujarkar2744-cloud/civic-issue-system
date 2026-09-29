@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card"><div class="card-body p-4">
            <h4 class="mb-3">Citizen registration</h4>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                @foreach([['name','Full name','text'],['email','Email','email'],['phone','Phone (optional)','text'],['password','Password','password'],['password_confirmation','Confirm password','password']] as [$f,$label,$type])
                    <div class="mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $f }}" value="{{ $type === 'password' ? '' : old($f) }}" class="form-control @error($f) is-invalid @enderror" {{ $f === 'phone' ? '' : 'required' }}>
                        @error($f)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <button class="btn btn-primary w-100">Register</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
