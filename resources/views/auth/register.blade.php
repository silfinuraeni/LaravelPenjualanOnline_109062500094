@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="card" style="max-width:480px;margin:2rem auto;">
    <h2 style="margin-bottom:1.5rem;text-align:center;">Registrasi Pelanggan</h2>
    
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label>Nama
            <input class="input" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </label>
        
        <label style="margin-top:1rem;display:block;">Email
            <input class="input" type="email" name="email" value="{{ old('email') }}" required>
        </label>
        
        <label style="margin-top:1rem;display:block;">Password
            <input class="input" type="password" name="password" required>
        </label>
        
        <label style="margin-top:1rem;display:block;">Konfirmasi Password
            <input class="input" type="password" name="password_confirmation" required>
        </label>
        
        <button class="btn" style="width:100%;margin-top:1.5rem;">Daftar</button>
    </form>
</div>
@endsection