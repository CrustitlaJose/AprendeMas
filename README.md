# Centro de Asesorías (Aprende +)

## 2. Guía de Instalación y Configuración del Entorno Local

### Prerrequisitos del Sistema e Instalación de Herramientas

Antes de clonar el proyecto, verifica que tienes las herramientas instaladas en tu computadora con **Windows** o **Linux (Ubuntu/Debian)**. Para cada una, primero comprueba si la tienes y, si no, instálala directamente desde tu terminal usando los comandos de tu sistema operativo.

> [!TIP]
> **Windows:** después de instalar cualquier herramienta con `winget`, **cierra y vuelve a abrir la terminal** para que Windows reconozca el nuevo comando.
> **Linux:** los comandos con `sudo` te pedirán la contraseña de tu usuario (no se muestran caracteres al escribirla; es normal).

### A. Git (Control de Versiones)

**1. Verificar si está instalado.** Abre tu terminal (PowerShell, Command Prompt o terminal de Linux) y ejecuta:

```bash
git --version
```

- Si devuelve un número de versión (ej. `git version 2.43.0`), pasa al siguiente prerrequisito.
- Si marca error o "no se reconoce el comando", instálalo con el comando de abajo.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):**

```powershell
winget install --id Git.Git -e --source winget
```

- **Linux (Ubuntu/Debian):**

```bash
sudo apt update
sudo apt install -y git curl unzip
```

### B. PHP 8.4 y configuración de extensiones

**1. Verificar si está instalado.** En la terminal, ejecuta:

```bash
php -v
```

- Si muestra `PHP 8.2` o superior (se recomienda `8.4.x`), pasa al punto 3 (extensiones).
- Si no lo tienes o tu versión es inferior a 8.2, realiza el punto 2.

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```powershell
winget install --id PHP.PHP.8.4 -e
```

  (Cierra y vuelve a abrir la terminal al terminar).

- **Linux (Ubuntu):** Ubuntu trae por defecto una versión anterior de PHP, por lo que se agrega el repositorio de Ondřej Surý:

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip
```

  > [!NOTE]
  > Si después de instalar, `php -v` sigue mostrando otra versión, ejecuta `sudo update-alternatives --set php /usr/bin/php8.4`.
  > En Debian (sin Ubuntu) el repositorio `ppa:ondrej/php` no aplica; usa `packages.sury.org`.

**3. Habilitar las extensiones requeridas.**

Para solucionar el error `Could not find driver` al conectar Laravel con PostgreSQL, y para que Composer y Laravel funcionen correctamente, deben estar activas estas extensiones: `openssl`, `pdo_pgsql`, `pgsql`, `fileinfo`, `mbstring`, `curl` y `zip`.

- **Linux:** ya quedaron activas al instalar los paquetes `php8.4-*` del punto anterior. No hay que editar el `php.ini`. Solo verifica:

```bash
php -m | grep -Ei "pgsql|openssl|fileinfo|mbstring|curl|zip"
```

- **Windows:**

1. **Localiza la ruta de tu archivo php.ini.** Ejecuta en tu terminal:

   ```powershell
   php --ini
   ```

   Busca la línea `Loaded Configuration File:` (ej. `C:\php\php.ini`).

   > [!NOTE]
   > Si esa línea dice `(none)`, aún no existe un `php.ini`. Ve a la carpeta indicada en `Configuration File (php.ini) Path`, copia el archivo `php.ini-development` y renombra la copia como `php.ini`.

2. **Edita el archivo php.ini.** Ábrelo con VS Code o con el Bloc de notas (si no te deja guardar, ábrelo como Administrador):

   ```powershell
   code C:\php\php.ini
   ```

3. **Descomenta las líneas requeridas.** Busca las siguientes líneas y elimina el punto y coma `;` inicial de cada una:

   ```ini
   ; Cambiar esto:
   ;extension_dir = "ext"
   ;extension=openssl
   ;extension=pdo_pgsql
   ;extension=pgsql
   ;extension=fileinfo
   ;extension=mbstring
   ;extension=curl
   ;extension=zip

   ; Para que quede exactamente así:
   extension_dir = "ext"
   extension=openssl
   extension=pdo_pgsql
   extension=pgsql
   extension=fileinfo
   extension=mbstring
   extension=curl
   extension=zip
   ```

   > [!NOTE]
   > En algunos `php.ini` la línea de `extension_dir` tiene un espacio después del `;` (`; extension_dir = "ext"`). Déjala sin el `;` y sin ese espacio inicial.

4. Guarda el archivo y cierra el editor.

5. **Verifica que quedaron activas:**

   ```powershell
   php -m | findstr /i "pgsql openssl fileinfo mbstring curl zip"
   ```

   Deben aparecer `pdo_pgsql`, `pgsql`, `openssl`, `fileinfo`, `mbstring`, `curl` y `zip`.

### C. Composer (Gestor de Dependencias de PHP)

> [!IMPORTANT]
> En Windows **no usamos `winget` para instalar Composer**, debido a cambios frecuentes en su identificador en los repositorios. El método oficial mediante un script de PHP es directo y funciona siempre.

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```bash
composer --version
```

- Si responde con `Composer version 2.x`, continúa con PostgreSQL.
- Si marca error o no se reconoce, usa el comando de instalación.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):** ejecuta este bloque **dentro de la carpeta del proyecto `AprendeMas`** (es decir, después del Paso 2):

```powershell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

  Esto genera el archivo `composer.phar` en la carpeta donde ejecutaste los comandos. Con este método se usa `php composer.phar <comando>` en lugar de `composer`, por lo que el archivo debe estar en la raíz del proyecto.

- **Linux (Ubuntu/Debian):** ejecuta este bloque desde cualquier carpeta. Deja Composer disponible de forma global como `composer`:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### D. PostgreSQL 16 (Motor de Base de Datos)

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```bash
psql --version
```

- Si responde con `psql (PostgreSQL) 16.x`, el motor está listo.
- Si no se reconoce el comando, instálalo desde la terminal (en Windows revisa también el Paso 6, por si está instalado pero fuera del PATH).

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```powershell
winget install --id PostgreSQL.PostgreSQL.16 -e
```

  > **Nota:** Durante la instalación interactiva de PostgreSQL, asigna una contraseña fácil de recordar al usuario por defecto `postgres` (ej. `postgres`) y conserva el puerto `5432`.

- **Linux (Ubuntu/Debian):** se usa el repositorio oficial de PostgreSQL para asegurar la versión 16:

```bash
sudo apt install -y postgresql-common
sudo /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y
sudo apt install -y postgresql-16
```

  Verifica que el servicio esté activo y asigna una contraseña al usuario `postgres` (ej. `postgres`):

```bash
sudo systemctl status postgresql
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"
```

### E. Node.js (solo si el proyecto incluye `package.json`)

Es necesario únicamente si en la raíz del proyecto existe el archivo `package.json` (compilación de estilos y scripts con Vite). Puedes comprobarlo después del Paso 2. Si no existe, omite esta sección y el Paso 9.

**1. Verificar si está instalado.**

```bash
node -v
npm -v
```

- Si ambos devuelven un número de versión, continúa.
- Si marca error o no se reconoce, instálalo con el comando de abajo.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):**

```powershell
winget install --id OpenJS.NodeJS.LTS -e
```

- **Linux (Ubuntu/Debian):**

```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 3. Guía Paso a Paso de Instalación y Ejecución del Proyecto

Una vez instalados todos los prerrequisitos, sigue estos comandos en orden dentro de tu terminal.

### Paso 1: Clonar el repositorio

Descarga el código fuente del repositorio en tu equipo:

```bash
git clone https://github.com/CrustitlaJose/AprendeMas.git
```

✅ **Verificación:** se creará la carpeta `AprendeMas` en tu directorio actual.

### Paso 2: Entrar a la carpeta del proyecto

Es indispensable ingresar a la subcarpeta antes de ejecutar comandos de Composer o Laravel:

```bash
cd AprendeMas
```

✅ **Verificación:** tu terminal mostrará la ruta actual terminando en `AprendeMas` (`\AprendeMas>` en Windows, `/AprendeMas` en Linux).

### Paso 3: Instalar dependencias de PHP y Laravel

Descarga todas las librerías del framework especificadas en `composer.json`.

- **Windows:** si el comando global `composer` no se reconoce, primero ejecuta el bloque de instalación de la sección C (dentro de esta carpeta) y luego usa el ejecutable local:

```powershell
php composer.phar install
```

  (Si ya tienes Composer global, puedes usar directamente `composer install`).

- **Linux:**

```bash
composer install
```

✅ **Verificación:** la terminal leerá el archivo `composer.json` y descargará todas las librerías, creando la carpeta `vendor`.

### Paso 4: Configurar variables de entorno y generar la clave

Copia la plantilla `.env.example` para crear tu archivo `.env` local y genera la clave de cifrado:

- **Windows (PowerShell / CMD):**

```powershell
copy .env.example .env
php artisan key:generate
```

- **Linux:**

```bash
cp .env.example .env
php artisan key:generate
```

✅ **Verificación:** el archivo `.env` aparecerá en la raíz del proyecto y la consola confirmará que la clave de la aplicación se generó correctamente.

### Paso 5: Configurar la conexión a PostgreSQL

Abre el archivo `.env` recién creado:

- **Windows:** `code .env` (VS Code) o `notepad .env`
- **Linux:** `nano .env` (para guardar: `Ctrl + O`, `Enter`; para salir: `Ctrl + X`)

Actualiza tus parámetros de acceso local:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aprende_mas
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña_local
```

> [!NOTE]
> - Si alguna de las líneas `DB_` aparece con `#` al inicio, quítalo; de lo contrario Laravel la ignora y usa valores por defecto.
> - Si tu contraseña contiene espacios o el carácter `#`, escríbela entre comillas: `DB_PASSWORD="mi#clave"`.
> - Después de modificar el `.env`, si algún cambio no se refleja, ejecuta `php artisan config:clear`.

✅ **Verificación:** los datos coinciden con tu usuario y contraseña locales de PostgreSQL.

### Paso 6: Crear la base de datos en PostgreSQL

Crea la base de datos `aprende_mas`:

- **Windows, si usas PowerShell:**

```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -c "CREATE DATABASE aprende_mas;"
```

- **Windows, si usas CMD (Símbolo del sistema):**

```bat
"C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -c "CREATE DATABASE aprende_mas;"
```

  (Introduce tu contraseña de PostgreSQL cuando te la solicite. Se usa la ruta absoluta por si `psql` no está en el PATH).

- **Linux:**

```bash
sudo -u postgres psql -c "CREATE DATABASE aprende_mas;"
```

✅ **Verificación:** la consola confirmará la creación con el mensaje `CREATE DATABASE`.

### Paso 7: Ejecutar las migraciones y cargar los datos de prueba

Construye las tablas normalizadas en 3FN e inserta el catálogo inicial de materias y usuarios de prueba (las credenciales de los usuarios de prueba se definen en `database/seeders/`):

```bash
php artisan migrate:fresh --seed
```

> [!WARNING]
> `migrate:fresh` elimina todas las tablas de la base de datos antes de recrearlas. Úsalo solo en tu entorno local.

✅ **Verificación:** la terminal enlistará la creación exitosa de cada una de las tablas del sistema.

### Paso 8: Enlazar el almacenamiento público

Crea el enlace simbólico que permite mostrar en el navegador los archivos e imágenes subidos al sistema:

```bash
php artisan storage:link
```

✅ **Verificación:** la consola mostrará que el enlace `public/storage` fue creado (si indica que ya existe, es correcto).

### Paso 9: Instalar y compilar los recursos del frontend (solo si existe `package.json`)

Descarga las dependencias de JavaScript y genera los estilos y scripts del sistema:

```bash
npm install
npm run build
```

✅ **Verificación:** se creará la carpeta `node_modules` y la carpeta `public/build` con el archivo `manifest.json`.

### Paso 10: Iniciar el servidor local

Levanta el servidor web integrado de Laravel:

```bash
php artisan serve
```

✅ **Verificación:** la terminal mostrará la URL local lista para abrirse en tu navegador: <http://127.0.0.1:8000> (o <http://127.0.0.1:8001> si el puerto 8000 está ocupado). Para detener el servidor, presiona `Ctrl + C`.

---

## 4. Solución de Problemas Comunes

- **`winget` no se reconoce (Windows):** instala o actualiza "Instalador de aplicación" (App Installer) desde la Microsoft Store.
- **`php`, `git`, `composer`, `psql` o `node` no se reconocen después de instalarlos:** cierra y vuelve a abrir la terminal. Si persiste, el programa no está en el PATH del sistema (en el caso de `psql`, usa la ruta absoluta del Paso 6).
- **`Unable to load dynamic library ...` (Windows):** en el `php.ini` deja activa la línea `extension_dir = "ext"` y vuelve a abrir la terminal.
- **`could not find driver` al migrar:** la extensión de PostgreSQL no está activa. En Windows descomenta `extension=pdo_pgsql` y `extension=pgsql` en el `php.ini`; en Linux ejecuta `sudo apt install -y php8.4-pgsql`. Verifica con `php -m`.
- **No me deja guardar `php.ini` (Windows):** abre el editor como Administrador.
- **`Could not open input file: composer.phar` (Windows):** el archivo no está en la carpeta actual. Ejecuta el instalador de la sección C dentro de `AprendeMas`, o usa `composer install` si lo tienes global.
- **Composer advierte que faltan `zip` / `unzip`:** en Windows activa `extension=zip` en el `php.ini`; en Linux ejecuta `sudo apt install -y php8.4-zip unzip`.
- **`Your requirements could not be resolved` / `requires php >= ...` / `ext-... is missing`:** tu versión de PHP o alguna extensión no cumple lo que pide `composer.json`. Revisa con `php -v` y `php -m`.
- **`password authentication failed for user "postgres"`:** la contraseña del `.env` no coincide con la de PostgreSQL. En Linux puedes restablecerla con `sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"`. Después ejecuta `php artisan config:clear`.
- **`Connection refused` / `could not connect to server`:** el servicio de PostgreSQL no está iniciado o el puerto no es el 5432. En Windows: `Get-Service postgresql*` y, si está detenido, `Start-Service postgresql-x64-16`. En Linux: `sudo systemctl start postgresql`.
- **`database "aprende_mas" does not exist`:** falta ejecutar el Paso 6 o el nombre en `DB_DATABASE` no coincide con el de la base creada.
- **Los cambios del `.env` no se reflejan:** ejecuta `php artisan config:clear`.
- **`No application encryption key has been specified`:** falta el `.env` o la clave. Ejecuta el Paso 4 (`php artisan key:generate`).
- **`relation "sessions" does not exist` (o cualquier tabla inexistente):** las migraciones no se ejecutaron. Repite el Paso 7.
- **`Vite manifest not found at ... public/build/manifest.json`:** falta compilar el frontend. Ejecuta el Paso 9 (y la sección E si no tienes Node.js).
- **`npm.ps1 cannot be loaded because running scripts is disabled` (Windows PowerShell):** ejecuta `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned` y vuelve a intentarlo, o usa CMD.
- **Las imágenes o archivos subidos no se ven:** falta el Paso 8 (`php artisan storage:link`).
- **`Permission denied` en `storage/logs/laravel.log` (Linux):** ejecuta `sudo chown -R $USER:$USER storage bootstrap/cache && chmod -R ug+rwx storage bootstrap/cache`.
- **El puerto 8000 está ocupado:** inicia el servidor en otro puerto con `php artisan serve --port=8001`.
- **Quiero reiniciar la base de datos con los datos de prueba:** ejecuta `php artisan migrate:fresh --seed` (borra todos los datos).
