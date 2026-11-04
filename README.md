# Centro de Asesorías (Aprende +)

## 2. Guía de Instalación y Configuración del Entorno Local

### Prerrequisitos del Sistema e Instalación de Herramientas

Antes de clonar el proyecto, verifica que tienes las herramientas instaladas en tu computadora con **Windows**. Para cada una, primero comprueba si la tienes y, si no, instálala directamente desde tu terminal.

> [!TIP]
> Después de instalar cualquier herramienta con `winget`, **cierra y vuelve a abrir la terminal** para que Windows reconozca el nuevo comando.

### A. Git (Control de Versiones)

**1. Verificar si está instalado.** Abre tu terminal (PowerShell o Command Prompt) y ejecuta:

```powershell
git --version
```

- Si devuelve un número de versión (ej. `git version 2.40.0`), pasa al siguiente prerrequisito.
- Si marca error o "no se reconoce el comando", instálalo con el comando de abajo.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):**

```powershell
winget install --id Git.Git -e --source winget
```

### B. PHP 8.4 y configuración de extensiones (php.ini)

**1. Verificar si está instalado.** En la terminal, ejecuta:

```powershell
php -v
```

- Si muestra `PHP 8.4.x`, pasa al punto 3 (habilitación de extensiones).
- Si no lo tienes o tu versión es inferior a 8.2, realiza el punto 2.

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```powershell
winget install --id PHP.PHP.8.4 -e
```

  (O usando Chocolatey: `choco install php --version=8.4.0 -y`).

**3. Habilitar las extensiones de PostgreSQL en php.ini (obligatorio).**

Para solucionar el error `Could not find driver` al conectar Laravel con PostgreSQL, activa las extensiones en PHP:

1. **Localiza la ruta de tu archivo php.ini.** Ejecuta en tu terminal:

   ```powershell
   php --ini
   ```

   Busca la línea `Loaded Configuration File:` (ej. `C:\php\php.ini`).

   > [!NOTE]
   > Si esa línea dice `(none)`, aún no existe un `php.ini`. Ve a la carpeta indicada en `Configuration File (php.ini) Path`, copia el archivo `php.ini-development` y renombra la copia como `php.ini`.

2. **Edita el archivo php.ini.** Ábrelo con VS Code o con el Bloc de notas:

   ```powershell
   code C:\php\php.ini
   ```

3. **Descomenta las extensiones requeridas.** Busca las siguientes líneas y elimina el punto y coma `;` inicial de cada una:

   ```ini
   ; Cambiar esto:
   ;extension=openssl
   ;extension=pdo_pgsql
   ;extension=pgsql
   ;extension=fileinfo
   ;extension=mbstring

   ; Para que quede exactamente así:
   extension=openssl
   extension=pdo_pgsql
   extension=pgsql
   extension=fileinfo
   extension=mbstring
   ```

4. Guarda el archivo y cierra el editor.

5. **Verifica que quedaron activas:**

   ```powershell
   php -m | findstr /i "pgsql openssl fileinfo mbstring"
   ```

   Deben aparecer `pdo_pgsql`, `pgsql`, `openssl`, `fileinfo` y `mbstring`.

### C. Composer (Gestor de Dependencias de PHP)

> [!IMPORTANT]
> En Windows **no usamos `winget` para instalar Composer**, debido a cambios frecuentes en su identificador en los repositorios. El método oficial mediante un script de PHP es directo y funciona siempre.

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```powershell
composer --version
```

- Si responde con `Composer version 2.x`, continúa con PostgreSQL.
- Si marca error o no se reconoce, usa el comando de instalación.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):** ejecuta este bloque en tu terminal:

```powershell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Esto genera el archivo `composer.phar` en la carpeta donde ejecutaste los comandos. Con este método se usa `php composer.phar <comando>` en lugar de `composer`, por lo que el archivo debe estar dentro de la carpeta del proyecto (`AprendeMas`). Puedes ejecutar este bloque ya dentro de esa carpeta (después del Paso 2) o copiar ahí el archivo.

### D. PostgreSQL 16 (Motor de Base de Datos)

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```powershell
psql --version
```

- Si responde con `psql (PostgreSQL) 16.x`, el motor está listo.
- Si no se reconoce el comando, instálalo desde la terminal (o revisa el Paso 6, por si está instalado pero fuera del PATH).

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```powershell
winget install --id PostgreSQL.PostgreSQL.16 -e
```

  > **Nota:** Durante la instalación interactiva de PostgreSQL, asigna una contraseña fácil de recordar al usuario por defecto `postgres` (ej. `postgres`) y conserva el puerto `5432`.

---

## 3. Guía Paso a Paso de Instalación y Ejecución del Proyecto

Una vez instalados todos los prerrequisitos, sigue estos comandos en orden dentro de tu terminal.

### Paso 1: Clonar el repositorio

Descarga el código fuente del repositorio en tu equipo:

```powershell
git clone https://github.com/CrustitlaJose/AprendeMas.git
```

✅ **Verificación:** se creará la carpeta `AprendeMas` en tu directorio actual.

### Paso 2: Entrar a la carpeta del proyecto

Es indispensable ingresar a la subcarpeta antes de ejecutar comandos de Composer o Laravel:

```powershell
cd AprendeMas
```

✅ **Verificación:** tu terminal mostrará la ruta actual terminando en `\AprendeMas>`.

### Paso 3: Instalar dependencias de PHP y Laravel

Descarga todas las librerías del framework especificadas en `composer.json`. Si el comando global `composer` no se reconoce en tu terminal, utiliza el ejecutable local generado con PHP:

```powershell
php composer.phar install
```

✅ **Verificación:** la terminal leerá el archivo `composer.json` y descargará todas las librerías, creando la carpeta `vendor`.

### Paso 4: Configurar variables de entorno y generar la clave

Copia la plantilla `.env.example` para crear tu archivo `.env` local y genera la clave de cifrado. En Windows se usa `copy` en lugar de `cp`:

```powershell
copy .env.example .env
php artisan key:generate
```

✅ **Verificación:** el archivo `.env` aparecerá en la raíz del proyecto y la consola confirmará que la clave de la aplicación se generó correctamente.

### Paso 5: Configurar la conexión a PostgreSQL

Abre el archivo `.env` recién creado en VS Code y actualiza tus parámetros de acceso local:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aprende_mas
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña_local
```

✅ **Verificación:** los datos coinciden con tu usuario y contraseña locales de PostgreSQL.

### Paso 6: Crear la base de datos en PostgreSQL

Crea la base de datos `aprende_mas`. Si tu terminal indica que `psql` no se reconoce como comando, apunta directamente a la ruta absoluta del ejecutable según tu consola:

- **Si usas PowerShell:**

```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -c "CREATE DATABASE aprende_mas;"
```

- **Si usas CMD (Símbolo del sistema):**

```bat
"C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -c "CREATE DATABASE aprende_mas;"
```

(Introduce tu contraseña de PostgreSQL cuando te la solicite).

✅ **Verificación:** la consola confirmará la creación de la base de datos `aprende_mas`.

### Paso 7: Ejecutar las migraciones y cargar los datos de prueba

Construye las tablas normalizadas en 3FN e inserta el catálogo inicial de materias y usuarios de prueba:

```powershell
php artisan migrate:fresh --seed
```

> [!WARNING]
> `migrate:fresh` elimina todas las tablas de la base de datos antes de recrearlas. Úsalo solo en tu entorno local.

✅ **Verificación:** la terminal enlistará la creación exitosa de cada una de las tablas del sistema.

### Paso 8: Iniciar el servidor local

Levanta el servidor web integrado de Laravel:

```powershell
php artisan serve
```

✅ **Verificación:** la terminal mostrará la URL local lista para abrirse en tu navegador: <http://127.0.0.1:8000> (o <http://127.0.0.1:8001> si el puerto 8000 está ocupado). Para detener el servidor, presiona `Ctrl + C`.
