# Validaciones Implementadas

## 1. Frontend — Inputs numéricos (solo dígitos)

**Archivo:** `frontend/js/utils.js` (líneas 48-58)

```js
function soloNumeros(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
}
function bindSoloNumeros() {
    document.querySelectorAll('[data-solo-numeros]').forEach(el => {
        el.addEventListener('input', soloNumeros);
    });
}
document.addEventListener('DOMContentLoaded', bindSoloNumeros);
```

**Uso en HTML:** atributo `data-solo-numeros` en cédula y teléfonos (sedes, socios).

---

## 2. Frontend — Validación en `guardarSocio()` (socios.js)

**Archivo:** `frontend/js/socios.js` (líneas 67-85)

```js
if (!/^\d{10}$/.test(payload.cedula)) {
    Swal.fire('Dato invalido', 'La cedula debe tener exactamente 10 digitos numericos', 'warning');
    return;
}
if (payload.telefono && !/^\d{7,10}$/.test(payload.telefono)) {
    Swal.fire('Dato invalido', 'El telefono debe tener entre 7 y 10 digitos numericos', 'warning');
    return;
}
if (payload.correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.correo)) {
    Swal.fire('Dato invalido', 'El correo electronico no es valido', 'warning');
    return;
}
```

---

## 3. Frontend — Validación en `guardarSede()` (sedes.js)

**Archivo:** `frontend/js/sedes.js` (líneas 75-92)

```js
if (payload.nombre.length > 100) {
    Swal.fire('Dato invalido', 'El nombre no puede exceder 100 caracteres', 'warning');
    return;
}
if (payload.direccion.length > 200) {
    Swal.fire('Dato invalido', 'La direccion no puede exceder 200 caracteres', 'warning');
    return;
}
if (payload.telefono && !/^\d{7,10}$/.test(payload.telefono)) {
    Swal.fire('Dato invalido', 'El telefono debe tener entre 7 y 10 digitos numericos', 'warning');
    return;
}
```

---

## 4. Frontend — Validación en `guardarLibro()` (libros.js)

**Archivo:** `frontend/js/libros.js` (líneas 72-90)

```js
if (payload.isbn.length > 20) {
    Swal.fire('Dato invalido', 'El ISBN no puede exceder 20 caracteres', 'warning');
    return;
}
if (payload.anio_publicacion && (payload.anio_publicacion < 1000 || payload.anio_publicacion > 2099)) {
    Swal.fire('Dato invalido', 'El anio debe estar entre 1000 y 2099', 'warning');
    return;
}
if (payload.num_paginas && payload.num_paginas < 1) {
    Swal.fire('Dato invalido', 'El numero de paginas debe ser mayor a 0', 'warning');
    return;
}
```

---

## 5. Frontend — Selects buscables (searchable-select)

**CSS:** `frontend/css/styles.css` (líneas 297-361)

Clases: `.searchable-select`, `.ss-search`, `.ss-dropdown`, `.ss-item`, `.ss-selected`.

**HTML estructural:**
```html
<div class="searchable-select" id="wrapper-xxx">
  <input type="hidden" id="pre-xxx" value="">
  <input type="text" class="ss-search" id="pre-xxx-search" placeholder="..." autocomplete="off">
  <div class="ss-dropdown" id="dropdown-xxx"></div>
</div>
```

**Usado en:** `prestamos.php` (socio y ejemplar), `ejemplares.php` (libro y sede).

---

## 6. Backend — Cascada sede → ejemplares

**Archivo:** `backend/api_sedes.php` (líneas 55-69)

```php
$stmtEstado = $pdo->prepare("SELECT estado FROM sedes_biblioteca WHERE id = :id");
$stmtEstado->execute([':id' => $id]);
$estadoAnterior = $stmtEstado->fetchColumn();
// ... update sede ...
if ($estadoAnterior !== false && (int)$estadoAnterior !== $estado) {
    if ($estado === 0) {
        // Al desactivar sede: ejemplares Disponible → Sin disponibilidad
        $pdo->prepare("UPDATE ejemplares SET estado = 'Sin disponibilidad'
            WHERE sede_id = :id AND estado = 'Disponible'")->execute([':id' => $id]);
    } else {
        // Al reactivar sede: ejemplares Sin disponibilidad → Disponible
        $pdo->prepare("UPDATE ejemplares SET estado = 'Disponible'
            WHERE sede_id = :id AND estado = 'Sin disponibilidad'")->execute([':id' => $id]);
    }
}
```

---

## 7. Render — conexion.php con variables de entorno

**Archivo:** `backend/conexion.php`

```php
$host     = getenv('DB_HOST') ?: '127.0.0.1';
$port     = getenv('DB_PORT') ?: '3307';
$db       = getenv('DB_NAME') ?: 'biblioteca_db';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root123';
```

Usa env vars de Render si existen, si no → defaults para local (Docker XAMPP).

---

## 8. Render — Dockerfile con pdo_mysql

**Archivo:** `Dockerfile`

```dockerfile
FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpng-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
```

---

## 9. HTML — Restricciones en inputs

En todos los formularios (`socios.php`, `sedes.php`, `libros.php`, `ejemplares.php`):

| Atributo | Uso |
|---|---|
| `data-solo-numeros` | Cédula, teléfono — solo dígitos |
| `maxlength="10"` | Cédula y teléfono |
| `inputmode="numeric"` | Teclado numérico en mobile |
| `pattern="\d{10}"` | Validación HTML5 en cédula |
| `maxlength="100"` / `maxlength="200"` | Nombre de sede / dirección |
| `maxlength="20"` | ISBN |
