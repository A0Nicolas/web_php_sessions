<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-gradient-to-b from-indigo-900 via-indigo-800 to-slate-900 text-white z-40 flex flex-col shadow-2xl">
    <div class="p-5 text-center border-b border-indigo-700/50">
        <div class="w-12 h-12 bg-indigo-500 rounded-full mx-auto mb-2 flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h3 class="font-bold text-lg tracking-wide">SISTEMA BIBLIOTECA</h3>
        <small class="text-indigo-300 text-xs">Gestion Cultural</small>
    </div>

    <ul class="flex-1 py-4 space-y-1 px-3">
        <li>
            <a href="dashboard.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'dashboard.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                INICIO
            </a>
        </li>
        <?php if ($usuario['rol'] !== 'bibliotecario'): ?>
        <li>
            <a href="sedes.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'sedes.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                SEDES
            </a>
        </li>
        <li>
            <a href="libros.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'libros.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                LIBROS
            </a>
        </li>
        <li>
            <a href="ejemplares.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'ejemplares.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                EJEMPLARES
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="socios.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'socios.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                SOCIOS
            </a>
        </li>
        <li>
            <a href="prestamos.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?php echo $pagina_actual === 'prestamos.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-700/50 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                PRESTAMOS
            </a>
        </li>
    </ul>

    <div class="p-4 border-t border-indigo-700/50 text-center">
        <small class="text-indigo-400 text-xs">Biblioteca v1.0 &copy; 2026</small>
    </div>
</nav>
