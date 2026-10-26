# Smart Inventory

Sistema web de inventario para un supermercado o bodega, construido con **PHP (procedural) + JavaScript + MySQL (mysqli)**, sin frameworks ni programación orientada a objetos: fácil de entender, explicar y extender.

## Funcionalidades

| Módulo | Archivo | Descripción |
|---|---|---|
| 🔐 Inicio de sesión | `login.php`, `instalar.php` | Login con sesiones PHP y contraseñas hasheadas (bcrypt). El primer acceso crea el administrador. |
| 🏠 Panel principal | `index.php` | Resumen de productos, valor de inventario, alertas y actividad reciente. |
| 📦 Productos | `productos.php` | Alta, edición y listado de productos por categoría. |
| ➕ Entradas | `entradas.php` | Registra ingresos de stock (compras, devoluciones, ajustes). |
| ➖ Salidas | `salidas.php` | Registra salidas de stock (ventas, pérdidas), validando que no exceda el disponible. |
| 🔎 Búsqueda | `busqueda.php` | Búsqueda en vivo por código, nombre o descripción. |
| ⚠️ Alertas de stock bajo | `alertas.php` | Lista de productos en o por debajo de su stock mínimo. |
| 📜 Historial | `historial.php` | Historial de movimientos con filtros por tipo, producto y fecha. |
| 💾 Guardado automático | `assets/js/autoguardado.js` | Los formularios de Entradas/Salidas guardan el borrador en `localStorage` mientras escribes. |
| 🗑️ Eliminación de productos | `api/eliminar_producto.php` | Baja lógica (soft delete) vía AJAX, sin perder el historial. |
| 📊 Estadísticas | `estadisticas.php` | Gráficas (Chart.js) de movimientos, categorías y productos más retirados. |

## Estilo de código

Todo el backend está escrito en **PHP procedural con mysqli**: solo funciones normales (`mysqli_connect()`, `mysqli_prepare()`, `mysqli_stmt_bind_param()`, `mysqli_query()`, etc.), sin clases, sin `new` y sin la flecha `->`. La conexión a la base de datos vive en una sola variable, `$conexion`, definida en [config/database.php](config/database.php) y disponible en cualquier página que incluya `includes/bootstrap.php`.

## Requisitos

- PHP 8.0 o superior, con la extensión `mysqli` habilitada (viene activada por defecto en XAMPP/MAMP/Laragon y en el PHP de Homebrew).
- MySQL 5.7+/MariaDB.
- Un servidor web (Apache/Nginx) o basta con el servidor embebido de PHP.
- Conexión a internet para cargar Bootstrap, Bootstrap Icons y Chart.js desde su CDN (si necesitas que funcione 100% sin internet, descarga esas librerías y cambia las rutas en `includes/header.php`, `instalar.php`, `login.php` y `estadisticas.php`).

## Instalación

1. **Copia el proyecto** a tu servidor local, por ejemplo:
   - XAMPP: `htdocs/smart_inventory`
   - MAMP: `htdocs/smart_inventory`
   - Laragon: `www/smart_inventory`

2. **Crea la base de datos** ejecutando el script SQL (phpMyAdmin, Adminer o la consola de `mysql`):

   ```bash
   mysql -u root -p < sql/schema.sql
   ```

   Esto crea la base `smart_inventory` con sus tablas (usuarios, categorías, productos, movimientos).

3. *(Opcional)* Carga datos de ejemplo para probar la app con productos ya cargados:

   ```bash
   mysql -u root -p < sql/datos_ejemplo.sql
   ```

4. **Configura la conexión** en [config/config.php](config/config.php) si tu usuario/contraseña de MySQL no son `root` / (vacía):

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'smart_inventory');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Levanta el servidor**:
   - Con XAMPP/MAMP/Laragon, solo visita `http://localhost/smart_inventory/`.
   - O, para probar rápido sin instalar nada más, desde la carpeta del proyecto:

     ```bash
     php -S localhost:8000
     ```

     y abre `http://localhost:8000/`.

6. **Primer uso**: si no hay ningún usuario creado, la app te redirige automáticamente a `instalar.php` para crear el administrador (nombre, usuario y contraseña). Después de eso, inicia sesión normalmente desde `login.php`.

## Estructura del proyecto

```
smart_inventory/
├── api/                 Endpoints AJAX (JSON/HTML): buscar, eliminar, estadísticas
├── assets/
│   ├── css/estilo.css    Estilos propios (sobre Bootstrap)
│   └── js/               Scripts: sidebar, autoguardado, productos, movimientos, búsqueda, gráficas
├── config/               Configuración y conexión a MySQL con mysqli ($conexion)
├── includes/             Bootstrap, autenticación, CSRF, funciones, layout (header/footer)
├── sql/                  schema.sql (requerido) y datos_ejemplo.sql (opcional)
├── instalar.php          Asistente de primer uso (crea el admin)
├── login.php / logout.php
├── index.php             Panel principal
├── productos.php / entradas.php / salidas.php
├── busqueda.php / alertas.php / historial.php / estadisticas.php
```

## Notas de seguridad

- Todas las consultas usan **mysqli con consultas preparadas** (`mysqli_prepare` + `mysqli_stmt_bind_param`), nunca concatenando datos del usuario directamente en el SQL.
- Las contraseñas se guardan con `password_hash()` (bcrypt) y se verifican con `password_verify()`.
- Formularios protegidos con **token CSRF** (`includes/csrf.php`).
- Los movimientos de stock (`entradas.php`/`salidas.php`) corren dentro de una **transacción** con `SELECT ... FOR UPDATE`, para evitar condiciones de carrera si dos personas registran movimientos al mismo tiempo.
- Eliminar un producto es una **baja lógica** (`activo = 0`): el historial de movimientos nunca se pierde.

## Próximos pasos sugeridos (si quieres seguir ampliándolo)

- Roles o permisos distintos entre usuarios (por ahora todos los usuarios tienen el mismo acceso).
- Exportar historial/estadísticas a Excel o PDF.
- Código de barras para búsqueda rápida de productos.
