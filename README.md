# Centro de Asesorías (Aprende +)

## 2. Guía de Instalación y Configuración del Entorno Local

### Prerrequisitos del Sistema e Instalación de Herramientas

Antes de clonar el proyecto, verifica que tienes las herramientas instaladas en tu computadora. Para cada una, primero comprueba si la tienes y, si no, instálala directamente desde tu terminal.

### A. Git (Control de Versiones)

**1. Verificar si está instalado.** Abre tu terminal (PowerShell, Command Prompt o Bash) y ejecuta:

```bash
git --version
```

- Si devuelve un número de versión (ej. `git version 2.40.0`), pasa al siguiente prerrequisito.
- Si marca error o "comando no encontrado", instálalo con los comandos de abajo.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):**

```bash
winget install --id Git.Git -e --source winget
```

- **Linux (Ubuntu/Debian):**

```bash
sudo apt update && sudo apt install git -y
```

### B. PHP 8.4 y configuración de extensiones (php.ini)

**1. Verificar si está instalado.** En la terminal, ejecuta:

```bash
php -v
```

- Si muestra `PHP 8.4.x`, pasa al punto 3 (habilitación de extensiones).
- Si no lo tienes o tu versión es inferior a 8.2, realiza el punto 2.

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```bash
winget install --id PHP.PHP.8.4 -e
```

  (O usando Chocolatey: `choco install php --version=8.4.0 -y`).

- **Linux (Ubuntu/Debian vía PPA):**

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.4 php8.4-cli php8.4-pgsql php8.4-xml php8.4-mbstring php8.4-curl -y
```

**3. Habilitar la extensión de PostgreSQL en php.ini (obligatorio).**

Para solucionar el error `Could not find driver` al conectar Laravel con PostgreSQL, activa las extensiones en PHP:

1. **Localiza la ruta de tu archivo php.ini.** Ejecuta en tu terminal:

   ```bash
   php --ini
   ```

   Busca la línea `Loaded Configuration File:` (ej. `C:\php\php.ini` o `/etc/php/8.4/cli/php.ini`).

2. **Edita el archivo php.ini.** Ábrelo con VS Code, Bloc de notas o Nano desde la terminal:

   ```bash
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

### C. Composer (Gestor de Dependencias de PHP)

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```bash
composer --version
```

- Si responde con `Composer version 2.x`, continúa con PostgreSQL.
- Si marca error o no se reconoce, usa el comando de instalación.

**2. Comandos de instalación:**

- **Windows (PowerShell / CMD):**

```bash
winget install --id Composer.Composer -e
```

- **Linux / Mac:**

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### D. PostgreSQL 16 (Motor de Base de Datos)

**1. Verificar si está instalado.** Ejecuta en tu terminal:

```bash
psql --version
```

- Si responde con `psql (PostgreSQL) 16.x`, el motor está listo.
- Si no se reconoce el comando, instálalo desde la terminal.

**2. Comandos de instalación:**

- **Windows (PowerShell como Administrador):**

```bash
winget install --id PostgreSQL.PostgreSQL.16 -e
```

  > **Nota:** Durante la instalación interactiva de PostgreSQL en Windows, asigna una contraseña al usuario por defecto `postgres` y conserva el puerto `5432`.

- **Linux (Ubuntu/Debian):**

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib -y
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

---

## 3. Guía Paso a Paso de Instalación y Ejecución del Proyecto

Una vez instalados todos los prerrequisitos, sigue estos comandos en orden dentro de tu terminal.

### Paso 1: Clonar el repositorio

Descarga el código fuente del repositorio en tu equipo:

```bash
git clone https://github.com/CrustitlaJose/AprendeMas.git
```

### Paso 2: Entrar a la carpeta del proyecto

Es indispensable ingresar a la subcarpeta antes de ejecutar comandos de Composer o Laravel:

```bash
cd AprendeMas
```

### Paso 3: Instalar dependencias de PHP y Laravel

Descarga todas las librerías del framework especificadas en `composer.json`:

```bash
composer install
```

### Paso 4: Configurar variables de entorno y generar la clave

Copia la plantilla `.env.example` para crear tu archivo `.env` local y genera la clave de cifrado:

```bash
cp .env.example .env
php artisan key:generate
```

### Paso 5: Configurar la conexión a PostgreSQL

Abre el archivo `.env` recién creado en la raíz del proyecto y actualiza tus parámetros de acceso local:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aprende_mas
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña_local
```

### Paso 6: Crear la base de datos en PostgreSQL

Crea la base de datos `aprende_mas`:

```bash
psql -U postgres -c "CREATE DATABASE aprende_mas;"
```

### Paso 7: Ejecutar las migraciones y cargar los datos de prueba

Construye las tablas normalizadas en 3FN e inserta el catálogo inicial de materias y usuarios de prueba:

```bash
php artisan migrate:fresh --seed
```

### Paso 8: Iniciar el servidor local

Levanta el servidor web integrado de Laravel:

```bash
php artisan serve
```

El sistema estará ejecutándose localmente en: <http://127.0.0.1:8000> (o <http://127.0.0.1:8001>).
