<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Contable</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Sistema Contable</h1>
            
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p class="font-bold">Error de autenticación:</p>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.simple') }}">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Correo Electrónic
                        
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required 
                        autofocus>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Contraseña
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" 
                        id="password" 
                        type="password" 
                        name="password" 
                        required>
                </div>

                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                        type="submit">
                        Entrar
                    </button>
                </div>
            </form>

            <p class="text-center text-gray-600 text-sm mt-4">
                <a href="/admin/login" class="text-blue-500 hover:text-blue-700">Volver al login de Filament</a>
            </p>
        </div>
    </div>
</body>
</html>
