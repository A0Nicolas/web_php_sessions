<?php
$rol_usuario = $usuario['rol'] ?? '';
$nombre_usuario = $usuario['nombre'] ?? $usuario['usuario'] ?? 'Usuario';
$rol_display = ucfirst($rol_usuario);
?>
<header class="bg-white shadow-sm border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-3">
        <h1 class="text-lg font-bold text-indigo-900"><?php echo htmlspecialchars($tituloPagina ?? 'Sistema'); ?></h1>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                <?php echo $rol_usuario === 'administrador' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'; ?>">
                <?php echo $rol_display; ?>
            </span>
            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($nombre_usuario); ?></span>
        </div>
        <a href="backend/logout.php"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition-all duration-200 border border-red-200 hover:border-red-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Salir
        </a>
    </div>
</header>
