<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Host Connection</title>
</head>
<body style="font-family: sans-serif; padding: 2rem;">
    <h2>Test Server & Login Info</h2>
    
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="margin-bottom: 20px;">
        <strong>Status Auth:</strong> 
        @auth
            <span style="color: green">LOGGED IN ({{ auth()->user()->email }})</span>
        @else
            <span style="color: red">GUEST OY</span>
        @endauth
    </div>

    <hr>
    <h3>1. Uji Login</h3>
    <form action="{{ url('/test-host') }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="login">
        Email: <input type="email" name="email" value="sayid.adam@ptjar.co.id" required><br><br>
        Pass: <input type="text" name="password" required><br><br>
        <button type="submit">Coba Login</button>
    </form>

    <hr>
    <h3>2. Uji CRUD Database (Create, Read, Update, Delete)</h3>
    <form action="{{ url('/test-host') }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="crud">
        <button type="submit">Jalankan Siklus CRUD</button>
    </form>

    <hr>
    <h3>3. Logout</h3>
    <form action="{{ url('/test-host') }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="logout">
        <button type="submit">Logout Auth</button>
    </form>
</body>
</html>
