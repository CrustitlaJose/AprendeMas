# Centro de Asesorías (Aprende +)

## Configuración de Entorno local

1. **Clonar el repositorio:** Descarga el código fuente del proyecto a tu máquina local.

```bash
git clone https://github.com/CrustitlaJose/AprendeMas.git

```

2. **Entrar a la raíz del repositorio:** Navega al directorio principal donde se encuentra la configuración de Laravel.

```bash
cd AprendeMas

```

3. **Crea la BD dentro de tu maquina local:** Genera la BD para recibir las tablas del proyecto.

```bash
createdb -U postgres aprende_mas

```

4. **Migración de la BD del script:**

```bash
php artisan migrate

```

5. **Ejecutar el servidor local:** Levanta el entorno de desarrollo integrado de PHP.

```bash
php artisan serve

```

6. **Acceder a la aplicación:** Abre tu navegador web e ingresa al dominio o dirección IP local que arroja la terminal (por defecto suele ser `http://localhost:8000`).
