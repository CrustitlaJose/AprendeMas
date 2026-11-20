# Aprende+ — Centro de Asesorías

Sistema web para la gestión de asesorías académicas: disponibilidad docente, agendado y cancelación de asesorías (mínimo 12 horas de anticipación), liberación de cupos y administración de alumnos y profesores por parte del coordinador.

**Stack:** PHP 8.4+ · Laravel 13 · PostgreSQL 16 · Vite + Tailwind CSS 4

**Roles:** Alumno · Profesor · Coordinador

---

## Cómo usar esta guía

- Elige tu sistema: **[Instalación en Windows](#instalación-en-windows)** o **[Instalación en Linux](#instalación-en-linux-ubuntu)**.
- Ve **en orden**, de arriba abajo. Cada bloque gris se **copia completo y se pega** en la terminal; luego presiona `Enter`.
- No necesitas editar archivos a mano: los comandos lo hacen por ti.
- Debajo de cada bloque dice **qué debe aparecer**. Si ves otra cosa, busca el mensaje en [Solución de problemas](#solución-de-problemas).

| Herramienta | Versión |
|---|---|
| PHP | **8.4 o superior (obligatorio)**. Con PHP 8.3 el proyecto no instala: sus dependencias exigen PHP ≥ 8.4.1 |
| PostgreSQL | 16 |
| Node.js | LTS |
| Git y Composer | cualquiera reciente |

Datos de acceso que quedan configurados al terminar (solo para uso local):

| Qué | Usuario | Contraseña |
|---|---|---|
| PostgreSQL | `postgres` | `postgres` |
| Coordinador (en la página) | `coordinacion@aprendemas.com` | `admin123` |

---

## Instalación en Windows

Todo se hace en **PowerShell**. Para abrirlo: presiona la tecla **Windows**, escribe `PowerShell` y presiona `Enter`. La línea debe empezar con `PS C:\...>` (si empieza solo con `C:\...>` estás en CMD: escribe `powershell` y `Enter`).

Usa **la misma ventana** para todos los pasos. Si Windows pregunta "¿Quieres permitir que esta aplicación haga cambios?", elige **Sí**.

### W1 — Permitir que PowerShell ejecute npm

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned -Force
```

No muestra nada; es normal.

### W2 — Instalar Git, PHP 8.4, Node.js y PostgreSQL 16

Tarda varios minutos. Si una herramienta ya estaba instalada, winget lo indica y continúa.

```powershell
winget install --id Git.Git -e --source winget --accept-source-agreements --accept-package-agreements
winget install --id PHP.PHP.8.4 -e --accept-source-agreements --accept-package-agreements
winget install --id OpenJS.NodeJS.LTS -e --accept-source-agreements --accept-package-agreements
winget install --id PostgreSQL.PostgreSQL.16 -e --accept-source-agreements --accept-package-agreements --override "--mode unattended --superpassword postgres --serverport 5432"
```

> Si el instalador de PostgreSQL abre una ventana con preguntas, usa la contraseña `postgres`, el puerto `5432` y deja todo lo demás como viene.

Ahora actualiza la terminal para que reconozca lo instalado y verifica:

```powershell
$env:Path = [Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [Environment]::GetEnvironmentVariable("Path","User")
git --version
php -v
node -v
npm -v
```

Debe aparecer la versión de cada uno. **`php -v` debe decir `PHP 8.4.x`.** Si dice 8.3 o menor, ver [Solución de problemas](#solución-de-problemas).

### W3 — Activar las extensiones de PHP

El PHP que instala winget viene sin archivo de configuración (`php.ini`) y con las extensiones apagadas. Este bloque lo crea y las activa:

```powershell
$p = (Get-Command php).Source
$t = @((Get-Item $p).Target)[0]
if ($t) { $p = $t }
$dir = Split-Path $p
$ini = Join-Path $dir "php.ini"
if (-not (Test-Path $ini)) { Copy-Item (Join-Path $dir "php.ini-development") $ini }
$ext = Join-Path $dir "ext"
$c = Get-Content $ini
$c = $c -replace '^;extension_dir = "ext"', ('extension_dir = "' + $ext + '"')
$c = $c -replace '^;extension=(openssl|pdo_pgsql|pgsql|fileinfo|mbstring|curl|zip)\s*$', 'extension=$1'
$c | Set-Content $ini
php -m | findstr /i "pgsql openssl fileinfo mbstring curl zip"
```

Deben aparecer **7 líneas**: `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `zip`.

### W4 — Descargar el proyecto

```powershell
cd $HOME
git clone https://github.com/CrustitlaJose/AprendeMas.git
cd AprendeMas
```

Desde aquí, todos los comandos se ejecutan dentro de la carpeta `AprendeMas` (la línea debe terminar en `\AprendeMas>`). Si cierras la terminal, vuelve a entrar con `cd $HOME\AprendeMas`.

### W5 — Instalar Composer y las dependencias de PHP

```powershell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install
```

Al final debe decir `Generating optimized autoload files` y se crea la carpeta `vendor`.

### W6 — Crear la configuración (`.env`)

```powershell
Copy-Item .env.example .env
(Get-Content .env) -replace '^MAIL_MAILER=.*', 'MAIL_MAILER=log' | Set-Content .env
php artisan key:generate
```

Debe decir `Application key set successfully`.

> El `.env` ya trae la contraseña de PostgreSQL `postgres` y el coordinador `coordinacion@aprendemas.com` / `admin123`. **Solo si** tu PostgreSQL ya existía con otra contraseña, ejecuta esto cambiando `TU_CONTRASEÑA`:
>
> ```powershell
> (Get-Content .env) -replace '^DB_PASSWORD=.*', 'DB_PASSWORD="TU_CONTRASEÑA"' | Set-Content .env
> php artisan config:clear
> ```

### W7 — Crear las bases de datos

```powershell
$env:PGPASSWORD = "postgres"
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -h 127.0.0.1 -c "CREATE DATABASE aprende_mas;"
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -h 127.0.0.1 -c "CREATE DATABASE aprende_mas_test;"
```

Debe responder `CREATE DATABASE` dos veces (si dice `already exists`, también está bien). Si usas otra contraseña de PostgreSQL, cámbiala en la primera línea.

### W8 — Crear tablas y datos iniciales

```powershell
php artisan migrate:fresh --seed
php artisan storage:link
```

Debe terminar sin errores en rojo. Se cargan materias, aulas y la cuenta del coordinador.

> ⚠️ `migrate:fresh` borra todas las tablas antes de recrearlas. Úsalo solo en local.

### W9 — Compilar el frontend

```powershell
npm install
npm run build
```

Debe terminar con `✓ built in ...`.

### W10 — Iniciar el sistema

```powershell
php artisan serve
```

Abre <http://127.0.0.1:8000> en el navegador e inicia sesión con `coordinacion@aprendemas.com` / `admin123`. Para detener el servidor: `Ctrl + C`.

**Para volver a abrirlo otro día:**

```powershell
cd $HOME\AprendeMas
php artisan serve
```

---

## Instalación en Linux (Ubuntu)

Abre una terminal (`Ctrl + Alt + T`). Los comandos con `sudo` piden tu contraseña: al escribirla **no se ven caracteres**, es normal; escríbela y presiona `Enter`.

### L1 — Instalar Git, PHP 8.4 y extensiones

```bash
sudo apt update
sudo apt install -y git curl unzip software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip
sudo update-alternatives --set php /usr/bin/php8.4
php -v
php -m | grep -Ei "pgsql|openssl|fileinfo|mbstring|curl|zip"
```

`php -v` debe decir `PHP 8.4.x` y deben aparecer 7 extensiones: `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `zip`.

> En Debian (no Ubuntu), `add-apt-repository ppa:ondrej/php` no funciona; usa el repositorio de `packages.sury.org`.

### L2 — Instalar Composer

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### L3 — Instalar PostgreSQL 16

```bash
sudo apt install -y postgresql-common
sudo /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y
sudo apt install -y postgresql-16
sudo systemctl start postgresql
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"
```

La última línea debe responder `ALTER ROLE`.

### L4 — Instalar Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

### L5 — Descargar el proyecto e instalar dependencias de PHP

```bash
cd ~
git clone https://github.com/CrustitlaJose/AprendeMas.git
cd AprendeMas
composer install
```

Desde aquí, todos los comandos se ejecutan dentro de `~/AprendeMas`.

### L6 — Crear la configuración (`.env`)

```bash
cp .env.example .env
sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=log/' .env
php artisan key:generate
```

Debe decir `Application key set successfully`. (El `.env` ya trae la contraseña de PostgreSQL `postgres` y el coordinador `coordinacion@aprendemas.com` / `admin123`.)

### L7 — Crear las bases de datos

```bash
sudo -u postgres psql -c "CREATE DATABASE aprende_mas;"
sudo -u postgres psql -c "CREATE DATABASE aprende_mas_test;"
```

Debe responder `CREATE DATABASE` dos veces (si dice `already exists`, también está bien).

### L8 — Tablas, datos iniciales y frontend

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
```

> ⚠️ `migrate:fresh` borra todas las tablas antes de recrearlas. Úsalo solo en local.

### L9 — Iniciar el sistema

```bash
php artisan serve
```

Abre <http://127.0.0.1:8000> e inicia sesión con `coordinacion@aprendemas.com` / `admin123`. Para detener: `Ctrl + C`.

**Para volver a abrirlo otro día:** `cd ~/AprendeMas && php artisan serve`

---

## Primer uso del sistema

1. Inicia sesión como **coordinador** (`coordinacion@aprendemas.com` / `admin123`).
2. Desde el panel del coordinador, da de alta **profesores** y **alumnos**.
3. Al registrar cada usuario, el sistema muestra una **contraseña temporal una sola vez**. Anótala para iniciar sesión con esa cuenta.
4. Flujo de prueba sugerido: el profesor registra disponibilidad → el alumno consulta y agenda → el alumno cancela (la cancelación se rechaza si faltan menos de 12 horas).

---

## Pruebas automatizadas

Dentro de la carpeta del proyecto (la base `aprende_mas_test` ya se creó en W7 / L7):

```bash
php artisan test
```

---

## Solución de problemas

En Windows, ejecuta los comandos en PowerShell dentro de la carpeta `AprendeMas`.

| Mensaje | Qué hacer (copiar y pegar) |
|---|---|
| `winget` no se reconoce (Windows) | Instala o actualiza "Instalador de aplicación" desde Microsoft Store y repite W2. |
| `php`, `git`, `node` o `npm` no se reconocen | Windows: `$env:Path = [Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [Environment]::GetEnvironmentVariable("Path","User")` · Linux: cierra y abre la terminal. |
| `php -v` muestra 8.3 o menor (Windows) | `winget uninstall --id PHP.PHP.8.3 -e` y luego repite W2 y W3. |
| `php -v` muestra 8.3 o menor (Linux) | `sudo update-alternatives --set php /usr/bin/php8.4` |
| `php -m \| findstr ...` no muestra nada (Windows) | Repite el bloque de W3 completo y abre una terminal nueva. |
| `Unable to load dynamic library` (Windows) | Repite el bloque de W3 completo. |
| `could not find driver` | Windows: repite W3. Linux: `sudo apt install -y php8.4-pgsql` |
| `requires php >=8.4.1` / `Your requirements could not be resolved` | Tu PHP es menor a 8.4: ver las filas de `php -v` de arriba. |
| `Could not open input file: composer.phar` (Windows) | No estás en la carpeta del proyecto: `cd $HOME\AprendeMas` y repite W5. |
| Composer avisa que faltan `zip` / `unzip` | Windows: repite W3. Linux: `sudo apt install -y php8.4-zip unzip` |
| `npm.ps1 cannot be loaded because running scripts is disabled` | Repite W1. |
| `password authentication failed for user "postgres"` | Windows: usa el comando de la nota de W6 con tu contraseña. Linux: `sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"` y `php artisan config:clear` |
| `Connection refused` / `could not connect to server` | Windows (PowerShell como Administrador): `Start-Service postgresql-x64-16` · Linux: `sudo systemctl start postgresql` |
| `psql.exe` no se encuentra (Windows) | PostgreSQL no se instaló: repite la última línea del primer bloque de W2. |
| `database "aprende_mas" does not exist` | Repite W7 / L7. |
| `database "aprende_mas_test" does not exist` (al correr pruebas) | Repite W7 / L7. |
| `No application encryption key has been specified` | `php artisan key:generate` |
| `Define COORDINADOR_EMAIL y COORDINADOR_PASSWORD...` | Windows: `Copy-Item .env.example .env -Force` · Linux: `cp .env.example .env` — luego repite W6 / L6 y W8 / L8. |
| `relation "sessions" does not exist` (u otra tabla) | `php artisan migrate:fresh --seed` |
| `Vite manifest not found` | `npm install` y `npm run build` |
| Imágenes o archivos subidos no se ven | `php artisan storage:link` |
| `Permission denied` en `storage/logs/laravel.log` (Linux) | `sudo chown -R $USER:$USER storage bootstrap/cache && chmod -R ug+rwx storage bootstrap/cache` |
| Puerto 8000 ocupado | `php artisan serve --port=8001` y abre <http://127.0.0.1:8001> |
| Los cambios del `.env` no se reflejan | `php artisan config:clear` |
| Quiero borrar todo y empezar la base de cero | `php artisan migrate:fresh --seed` (borra todos los datos). |
