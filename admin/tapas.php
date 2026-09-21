<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Tapas y Chefs - Premios Ñam Ñam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CSS y JS de Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen font-sans pb-12">

    <!-- Navegación Superior -->
    <nav class="bg-[#1E5A32] text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="font-serif font-bold text-lg">Ñam Ñam Admin</span>
                <span class="bg-emerald-800 text-emerald-200 text-xs px-2 py-0.5 rounded-full font-mono">VII Edición</span>
            </div>
            <div class="flex items-center space-x-4 text-xs font-semibold">
                <a href="index.php" class="hover:underline">Dashboard Principal</a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Gestor de Tapas y Chefs</h1>
                <p class="text-xs text-slate-500">Añade participantes y recorta las imágenes en formato 1:1.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Izquierda: Formularios -->
            <div class="space-y-6">
                <!-- Formulario 1: Nuevo Chef -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">1. Crear Nuevo Chef</h2>
                    <form id="form-chef" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre del Chef</label>
                            <input type="text" name="nombre" required class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-1 focus:ring-[#1E5A32]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Restaurante</label>
                            <input type="text" name="restaurante" required class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-1 focus:ring-[#1E5A32]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Foto del Chef (1:1)</label>
                            <input type="file" id="input-foto-chef" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-[#1E5A32]">
                        </div>
                        <button type="submit" class="w-full bg-[#1E5A32] hover:bg-[#164325] text-white text-xs font-bold py-2 rounded-lg transition">
                            Guardar Chef
                        </button>
                    </form>
                </div>

                <!-- Formulario 2: Nueva Tapa -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">2. Registrar Tapa</h2>
                    <form id="form-tapa" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Chef Asignado</label>
                            <select id="select-chef" name="chef_id" required class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-1 focus:ring-[#1E5A32]">
                                <option value="">Cargando chefs...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de la Tapa</label>
                            <input type="text" name="nombre_tapa" required class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-1 focus:ring-[#1E5A32]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Foto de la Tapa (1:1)</label>
                            <input type="file" id="input-foto-tapa" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-[#1E5A32]">
                        </div>
                        <button type="submit" class="w-full bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold py-2 rounded-lg transition">
                            Añadir Tapa
                        </button>
                    </form>
                </div>
            </div>

            <!-- Columna Derecha: Listado Completo -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Propuestas Culinarias Registradas</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 uppercase">
                                <tr>
                                    <th class="p-3">Foto Tapa</th>
                                    <th class="p-3">Nombre Tapa</th>
                                    <th class="p-3">Chef & Restaurante</th>
                                    <th class="p-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-tapas" class="divide-y divide-slate-100">
                                <tr><td colspan="4" class="p-4 text-center text-slate-400">Cargando propuestas...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal para Recortar Imagen (Proporción 1:1) -->
    <div id="modal-crop" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ajustar Imagen (Proporción 1:1)</h3>
                <button onclick="cerrarCropModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>
            <div class="p-4 max-h-[60vh] overflow-hidden flex justify-center bg-slate-900">
                <img id="crop-image" class="max-w-full">
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                <button onclick="cerrarCropModal()" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-200 rounded-lg hover:bg-slate-300">Cancelar</button>
                <button onclick="confirmarRecorte()" class="px-4 py-2 text-xs font-bold text-white bg-[#1E5A32] rounded-lg hover:bg-[#164325]">Aplicar y Guardar</button>
            </div>
        </div>
    </div>

    <script>
        let cropper = null;
        let blobImagenRecortada = null;
        let targetFileInput = null;

        document.addEventListener('DOMContentLoaded', cargarListado);

        // Detectar selección de archivo para Chef
        document.getElementById('input-foto-chef').addEventListener('change', function(e) {
            if (e.target.files.length > 0) abrirCropModal(e.target);
        });

        // Detectar selección de archivo para Tapa
        document.getElementById('input-foto-tapa').addEventListener('change', function(e) {
            if (e.target.files.length > 0) abrirCropModal(e.target);
        });

        function abrirCropModal(input) {
            targetFileInput = input;
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.getElementById('crop-image');
                img.src = e.target.result;
                document.getElementById('modal-crop').classList.remove('hidden');

                if (cropper) cropper.destroy();

                cropper = new Cropper(img, {
                    aspectRatio: 1, // Fuerza proporción 1:1 estricta
                    viewMode: 1,
                    autoCropArea: 1
                });
            };
            reader.readAsDataURL(file);
        }

        function cerrarCropModal() {
            document.getElementById('modal-crop').classList.add('hidden');
            if (cropper) cropper.destroy();
            cropper = null;
        }

        function confirmarRecorte() {
            if (!cropper) return;

            // Generar canvas cuadrado a resolución fija de alta calidad (600x600 px)
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 });
            
            canvas.toBlob((blob) => {
                blobImagenRecortada = blob;
                cerrarCropModal();
                alert('Imagen ajustada en proporción 1:1 correctamente.');
            }, 'image/jpeg', 0.9);
        }

        function cargarListado() {
            fetch('api_admin.php?action=get_tapas_chefs')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const select = document.getElementById('select-chef');
                        select.innerHTML = '<option value="">-- Selecciona un Chef --</option>';
                        res.chefs.forEach(c => {
                            select.innerHTML += `<option value="${c.id}">${c.nombre} (${c.restaurante})</option>`;
                        });

                        const tbody = document.getElementById('tabla-tapas');
                        tbody.innerHTML = '';

                        if (res.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-400">No hay tapas registradas.</td></tr>';
                            return;
                        }

                        res.data.forEach(t => {
                            const fotoTapa = t.foto_tapa ? `../img/${t.foto_tapa}` : 'https://via.placeholder.com/50';
                            
                            tbody.innerHTML += `
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3">
                                        <img src="${fotoTapa}" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                                    </td>
                                    <td class="p-3 font-bold text-slate-800">${t.nombre_tapa}</td>
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-700">${t.chef_nombre}</div>
                                        <div class="text-[10px] text-slate-400 uppercase">${t.restaurante}</div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button onclick="eliminarTapa(${t.tapa_id})" class="text-red-600 hover:text-red-800 font-bold text-[11px] bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-md transition">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                });
        }

        // Guardar Chef enviando la imagen recortada
        document.getElementById('form-chef').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            if (blobImagenRecortada && targetFileInput.id === 'input-foto-chef') {
                formData.append('foto_chef', blobImagenRecortada, 'chef_cropped.jpg');
            }

            fetch('api_admin.php?action=guardar_chef', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.reset();
                        blobImagenRecortada = null;
                        cargarListado();
                    } else {
                        alert(res.error);
                    }
                });
        });

        // Guardar Tapa enviando la imagen recortada
        document.getElementById('form-tapa').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            if (blobImagenRecortada && targetFileInput.id === 'input-foto-tapa') {
                formData.append('foto_tapa', blobImagenRecortada, 'tapa_cropped.jpg');
            }

            fetch('api_admin.php?action=guardar_tapa', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.reset();
                        blobImagenRecortada = null;
                        cargarListado();
                    } else {
                        alert(res.error);
                    }
                });
        });

        function eliminarTapa(tapaId) {
            if (!confirm('¿Eliminar esta tapa? Se perderán los datos vinculados.')) return;

            fetch('api_admin.php?action=eliminar_tapa', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tapa_id: tapaId })
            }).then(() => cargarListado());
        }
    </script>
</body>
</html>