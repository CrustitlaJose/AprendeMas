# Aprende+ — Centro de Asesorías

Sistema web para la gestión de asesorías académicas: disponibilidad docente, agendado y cancelación de asesorías (mínimo 12 horas de anticipación), liberación de cupos y administración de alumnos y profesores por parte del coordinador.

**Stack:** PHP 8.3+ · Laravel 13 · PostgreSQL 16 · Vite + Tailwind CSS 4

**Roles:** Alumno · Profesor · Coordinador

---

## 1. Requisitos

| Herramienta | Versión | Notas |
|---|---|---|
| Git | cualquiera reciente | |
| PHP | **8.3 o superior** (recomendado 8.4) | Con PHP 8.2 `composer install` falla |
| Composer | 2.x | |
| PostgreSQL | 16 | |
| Node.js | LTS actual | Necesario para compilar el frontend (Vite) |

Extensiones de PHP requeridas: `openssl`, `pdo_pgsql`, `pgsql`, `fileinfo`, `mbstring`, `curl`, `zip`.

> **Windows:** tras instalar algo con `winget`, cierra y vuelve a abrir la terminal.
> **Linux:** los comandos con `sudo` piden tu contraseña (no se muestran caracteres al escribirla; es normal).

### 1.1 Git

Verifica con `git --version`. Si no está instalado:

- **Windows:** `winget install --id Git.Git -e --source winget`
- **Linux (Ubuntu/Debian):** `sudo apt update && sudo apt install -y git curl unzip`

### 1.2 PHP 8.4 y extensiones

Verifica con `php -v` (debe ser 8.3 o superior).

- **Windows (PowerShell como Administrador):** `winget install --id PHP.PHP.8.4 -e`
- **Linux (Ubuntu):**

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip
```

Si `php -v` sigue mostrando otra versión: `sudo update-alternatives --set php /usr/bin/php8.4`.
En Debian (sin Ubuntu) usa `packages.sury.org` en lugar de `ppa:ondrej/php`.

**Extensiones en Linux:** ya quedan activas con los paquetes anteriores. Verifica:

```bash
php -m | grep -Ei "pgsql|openssl|fileinfo|mbstring|curl|zip"
```

**Extensiones en Windows:**

1. Ejecuta `php --ini` y busca `Loaded Configuration File`. Si dice `(none)`, copia `php.ini-development` (en la carpeta indicada por `Configuration File (php.ini) Path`) y renómbrala `php.ini`.
2. Abre `php.ini` (como Administrador si no te deja guardar) y quita el `;` inicial de estas líneas:

```ini
extension_dir = "ext"
extension=openssl
extension=pdo_pgsql
extension=pgsql
extension=fileinfo
extension=mbstring
extension=curl
extension=zip
```

3. Verifica: `php -m | findstr /i "pgsql openssl fileinfo mbstring curl zip"`

### 1.3 Composer

Verifica con `composer --version`. Si no está instalado:

- **Linux:**

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

- **Windows:** ejecuta los tres primeros comandos anteriores **dentro de la carpeta del proyecto** (después del Paso 2 de la sección 2). Se genera `composer.phar` y se usa como `php composer.phar <comando>` en lugar de `composer <comando>`.

### 1.4 PostgreSQL 16

Verifica con `psql --version`. Si no está instalado:

- **Windows (PowerShell como Administrador):** `winget install --id PostgreSQL.PostgreSQL.16 -e`
  Durante la instalación asigna una contraseña al usuario `postgres` (ej. `postgres`) y conserva el puerto `5432`.
- **Linux:**

```bash
sudo apt install -y postgresql-common
sudo /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y
sudo apt install -y postgresql-16
sudo systemctl status postgresql
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"
```

### 1.5 Node.js

Verifica con `node -v` y `npm -v`. Si no están instalados:

- **Windows:** `winget install --id OpenJS.NodeJS.LTS -e`
- **Linux:**

```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Instalación paso a paso

### Paso 1 — Clonar y entrar al proyecto

```bash
git clone https://github.com/CrustitlaJose/AprendeMas.git
cd AprendeMas
```

### Paso 2 — Instalar dependencias de PHP

- **Linux / Composer global:** `composer install`
- **Windows con `composer.phar` local:** `php composer.phar install`

Se crea la carpeta `vendor`.

### Paso 3 — Crear el `.env` y generar la clave

- **Linux:** `cp .env.example .env`
- **Windows:** `copy .env.example .env`

Después:

```bash
php artisan key:generate
```

### Paso 4 — Configurar el `.env`

Abre `.env` (`nano .env` en Linux, `notepad .env` en Windows) y revisa **estas variables**:

```env
# Base de datos (usa tus datos locales de PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aprende_mas
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña_de_postgres

# Cuenta del coordinador (OBLIGATORIO antes del Paso 6)
COORDINADOR_EMAIL=coordinacion@aprendemas.com
COORDINADOR_PASSWORD=define_una_contraseña_aqui

# Recomendado en local: los correos (p. ej. recuperar contraseña) se escriben en storage/logs/laravel.log
MAIL_MAILER=log
```

- **`COORDINADOR_PASSWORD` no puede quedar vacío:** si lo está, el Paso 6 falla con el error `Define COORDINADOR_EMAIL y COORDINADOR_PASSWORD antes de ejecutar CoordinadorSeeder`. Esta será la contraseña con la que inicies sesión como coordinador. Úsala solo en entornos locales o de prueba.
- Si una línea `DB_` empieza con `#`, quítalo; si no, Laravel la ignora.
- Si la contraseña contiene espacios o `#`, escríbela entre comillas: `DB_PASSWORD="mi#clave"`.
- Si un cambio no se refleja: `php artisan config:clear`.

### Paso 5 — Crear la base de datos

- **Linux:** `sudo -u postgres psql -c "CREATE DATABASE aprende_mas;"`
- **Windows:** `& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -c "CREATE DATABASE aprende_mas;"` (en CMD, sin el `&`)

Debe responder `CREATE DATABASE`.

### Paso 6 — Migraciones y datos iniciales

```bash
php artisan migrate:fresh --seed
```

> ⚠️ `migrate:fresh` borra todas las tablas antes de recrearlas. Úsalo solo en local.

Esto crea las tablas y carga: catálogo de **materias**, **aulas** y la **cuenta del coordinador** (con el correo y contraseña del `.env`). **No se cargan alumnos ni profesores:** los da de alta el coordinador (ver sección 3).

### Paso 7 — Enlazar el almacenamiento público

```bash
php artisan storage:link
```

Si indica que el enlace ya existe, es correcto.

### Paso 8 — Compilar el frontend

```bash
npm install
npm run build
```

Se crea `public/build/manifest.json`.

### Paso 9 — Iniciar el servidor

```bash
php artisan serve
```

Abre <http://127.0.0.1:8000> (o el puerto que indique la terminal). Detén el servidor con `Ctrl + C`.

---

## 3. Primer acceso y creación de usuarios

1. Inicia sesión como **coordinador** con `COORDINADOR_EMAIL` y `COORDINADOR_PASSWORD` del `.env`.
2. Desde el panel del coordinador, da de alta **profesores** y **alumnos**.
3. Al registrar cada usuario, el sistema genera una **contraseña temporal** y la muestra en pantalla **una sola vez**. Anótala para poder iniciar sesión con esa cuenta.
4. Flujo de prueba sugerido: el profesor registra disponibilidad → el alumno consulta y agenda → el alumno cancela (la cancelación se rechaza si faltan menos de 12 horas).

> Si cambias `COORDINADOR_PASSWORD` en el `.env` después de la primera ejecución, el cambio **no** actualiza una cuenta ya creada. Para aplicarlo, repite `php artisan migrate:fresh --seed` (borra todos los datos).

---

## 4. Pruebas automatizadas

```bash
php artisan test
```

---

## 5. Solución de problemas

| Error | Causa y solución |
|---|---|
| `winget` no se reconoce (Windows) | Instala o actualiza "Instalador de aplicación" desde Microsoft Store. |
| `php`, `git`, `composer`, `psql` o `node` no se reconocen | Cierra y reabre la terminal. Si persiste, no están en el PATH (para `psql`, usa la ruta absoluta del Paso 5). |
| `Unable to load dynamic library` (Windows) | En `php.ini` deja activa `extension_dir = "ext"` y reabre la terminal. |
| `could not find driver` | Extensión de PostgreSQL inactiva. Windows: activa `pdo_pgsql` y `pgsql` en `php.ini`. Linux: `sudo apt install -y php8.4-pgsql`. Verifica con `php -m`. |
| `Your requirements could not be resolved` / `requires php >= ...` | PHP menor a 8.3 o extensión faltante. Revisa `php -v` y `php -m`. |
| `Could not open input file: composer.phar` (Windows) | Ejecuta el instalador de Composer dentro de la carpeta del proyecto, o usa `composer` global. |
| Composer avisa que faltan `zip` / `unzip` | Windows: activa `extension=zip`. Linux: `sudo apt install -y php8.4-zip unzip`. |
| `Define COORDINADOR_EMAIL y COORDINADOR_PASSWORD...` | `COORDINADOR_PASSWORD` está vacío en el `.env`. Llénalo (Paso 4) y repite el Paso 6. |
| `password authentication failed for user "postgres"` | La contraseña del `.env` no coincide. Linux: `sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"` y luego `php artisan config:clear`. |
| `Connection refused` / `could not connect to server` | PostgreSQL detenido o puerto distinto de 5432. Windows: `Start-Service postgresql-x64-16`. Linux: `sudo systemctl start postgresql`. |
| `database "aprende_mas" does not exist` | Falta el Paso 5 o `DB_DATABASE` no coincide. |
| `No application encryption key has been specified` | Falta `.env` o clave. Repite el Paso 3. |
| `relation "sessions" does not exist` (u otra tabla) | Migraciones sin ejecutar. Repite el Paso 6. |
| `Vite manifest not found` | Falta compilar el frontend. Repite el Paso 8. |
| `npm.ps1 cannot be loaded because running scripts is disabled` (Windows) | `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`, o usa CMD. |
| Imágenes o archivos subidos no se ven | Falta el Paso 7. |
| `Permission denied` en `storage/logs/laravel.log` (Linux) | `sudo chown -R $USER:$USER storage bootstrap/cache && chmod -R ug+rwx storage bootstrap/cache` |
| Puerto 8000 ocupado | `php artisan serve --port=8001` |
| Los cambios del `.env` no se reflejan | `php artisan config:clear` |
| Reiniciar la base con datos iniciales | `php artisan migrate:fresh --seed` (borra todos los datos). |
