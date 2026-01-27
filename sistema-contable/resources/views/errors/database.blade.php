<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en el Sistema Contable</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { font-size: 18px; }
    </style>
</head>
<body>
    <h1>Error Interno del Servidor</h1>
    <p>{{ $message ?? 'Ha ocurrido un error en la base de datos. Por favor, contacte al administrador del sistema.' }}</p>
    <p><a href="{{ url('/admin') }}">Volver al Panel</a></p>
</body>
</html>