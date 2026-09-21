<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Premios Ñam Ñam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen font-sans pb-12">

    <!-- Navegación superior -->
    <nav class="bg-[#1E5A32] text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="font-serif font-bold text-lg">Ñam Ñam Admin</span>
                <span class="bg-emerald-800 text-emerald-200 text-xs px-2 py-0.5 rounded-full font-mono">VII Edición</span>
            </div>
            <div class="flex items-center space-x-4 text-xs font-semibold">
                <a href="tapas.php" class="hover:underline">Gestor de Tapas & Chefs</a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 mt-8">
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Control de la Gala</h1>
                <p class="text-xs text-slate-500">Administra la votación en tiempo real y la pantalla gigante.</p>
            </div>
            <a href="api_admin.php?action=exportar_csv" class="inline-flex items-center bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition">
                📥 Descargar Acta de Resultados (CSV)
            </a>
        </div>

        <!-- Métrica de Votos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Votos Registrados</span>
                    <div id="metric-votos" class="text-4xl font-extrabold text-[#1E5A32] mt-1">--</div>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-[#1E5A32] rounded-xl flex items-center justify-center text-xl">🗳️</div>
            </div>

            <!-- Interruptor Votaciones -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado Votación</span>
                    <div id="status-votacion-text" class="text-lg font-bold text-slate-700 mt-1">Cargando...</div>
                </div>
                <button id="btn-toggle-votacion" onclick="toggleVotacion()" class="px-4 py-2 text-xs font-bold rounded-xl text-white transition shadow-sm">
                    --
                </button>
            </div>

            <!-- Interruptor Pantalla Gigante -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pantalla Gigante</span>
                    <div id="status-desvelar-text" class="text-lg font-bold text-slate-700 mt-1">Cargando...</div>
                </div>
                <button id="btn-toggle-desvelar" onclick="toggleDesvelar()" class="px-4 py-2 text-xs font-bold rounded-xl text-white transition shadow-sm">
                    --
                </button>
            </div>
        </div>
    </main>

    <script>
        let estadoVotacion = false;
        let estadoDesvelar = false;

        document.addEventListener('DOMContentLoaded', cargarEstado);
        // Actualizar métricas cada 5 segundos
        setInterval(cargarEstado, 5000);

        function cargarEstado() {
            fetch('api_admin.php?action=get_status')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('metric-votos').innerText = res.total_votos;
                        
                        // Estado Votación
                        estadoVotacion = res.votacion_abierta;
                        const btnVot = document.getElementById('btn-toggle-votacion');
                        const txtVot = document.getElementById('status-votacion-text');
                        if (estadoVotacion) {
                            txtVot.innerText = 'Abierta';
                            txtVot.className = 'text-lg font-bold text-emerald-600 mt-1';
                            btnVot.innerText = 'Pausar Votación';
                            btnVot.className = 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-amber-600 hover:bg-amber-700 transition shadow-sm';
                        } else {
                            txtVot.innerText = 'Cerrada';
                            txtVot.className = 'text-lg font-bold text-red-600 mt-1';
                            btnVot.innerText = 'Abrir Votación';
                            btnVot.className = 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm';
                        }

                        // Estado Desvelar
                        estadoDesvelar = res.desvelar_ganadores;
                        const btnDes = document.getElementById('btn-toggle-desvelar');
                        const txtDes = document.getElementById('status-desvelar-text');
                        if (estadoDesvelar) {
                            txtDes.innerText = 'Ganadores Visibles';
                            txtDes.className = 'text-lg font-bold text-blue-600 mt-1';
                            btnDes.innerText = 'Ocultar Ganadores';
                            btnDes.className = 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-slate-600 hover:bg-slate-700 transition shadow-sm';
                        } else {
                            txtDes.innerText = 'Anónimo (Solo Tapas)';
                            txtDes.className = 'text-lg font-bold text-slate-600 mt-1';
                            btnDes.innerText = 'Desvelar Ganadores';
                            btnDes.className = 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm';
                        }
                    }
                });
        }

        function toggleVotacion() {
            fetch('api_admin.php?action=toggle_votacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ estado: !estadoVotacion })
            }).then(() => cargarEstado());
        }

        function toggleDesvelar() {
            fetch('api_admin.php?action=toggle_desvelar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ estado: !estadoDesvelar })
            }).then(() => cargarEstado());
        }
    </script>
</body>
</html>