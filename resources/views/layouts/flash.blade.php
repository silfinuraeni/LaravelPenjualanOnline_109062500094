@if(session('success'))
    <div class="alert" style="background: #e8f7ee; padding: 11px; margin-bottom: 15px; border-radius: 8px; color: #155724;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert error" style="background: #fdeaea; padding: 11px; margin-bottom: 15px; border-radius: 8px; color: #721c24;">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert error" style="background: #fdeaea; padding: 11px; margin-bottom: 15px; border-radius: 8px; color: #721c24;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif