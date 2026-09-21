<?php
session_start();
require_once '../db.php';

$error = '';

// Si ya está logueado, redirigir al panel
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Configuración de credenciales de acceso
    $usuario_correcto = 'admin';
    $password_correcta = 'Nannan2026!'; // Cambia esta contraseña si lo deseas

    if ($usuario === $usuario_correcto && $password === $password_correcta) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $usuario;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administración - Premios Ñam Ñam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
        <div class="text-center mb-6">
            <span class="text-xs font-black tracking-widest text-amber-700 uppercase">VII EDICIÓN</span>
            <h1 class="text-2xl font-serif text-[#1E5A32] font-bold">Panel de Control</h1>
            <p class="text-xs text-slate-500 mt-1">Premios Culinarios Ñam Ñam</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Usuario</label>
                <input type="text" name="usuario" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#1E5A32] focus:outline-none text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#1E5A32] focus:outline-none text-sm">
            </div>

            <button type="submit" class="w-full bg-[#1E5A32] hover:bg-[#164325] text-white font-bold py-2.5 rounded-lg text-sm transition duration-150 shadow-md">
                Entrar al Panel
            </button>
        </form>
    </div>
</body>
</html>