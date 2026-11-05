# Instrucciones para Agentes de IA - Proyecto "Aprende +"

## Contexto del Proyecto
"Aprende +" es un sistema web centralizado para la gestión y agendamiento de asesorías académicas universitarias (Matemáticas, Programación e Inglés). Su objetivo es automatizar el registro de asesorías y la disponibilidad docente, evitar empalmes de aulas físicas y gestionar cancelaciones con políticas de tiempo (mínimo 12 horas).

## Stack Tecnológico
*   **Framework:** Laravel (PHP) bajo el patrón MVC.
*   **Base de Datos:** PostgreSQL.

## Convenciones de Código y Nomenclatura
*   **Modelos (Eloquent):** Deben nombrarse siempre en `PascalCase` y en singular (ej. `Asesoria.php`, `Aula.php`, `ProfesorMateria.php`).
*   **Tablas de Base de Datos:** El diseño relacional original establece que las tablas van en `snake_case` y en **singular**. 
*   **Fijación de Tablas:** Como Laravel espera plurales por defecto, TODOS los modelos deben forzar el nombre de la tabla usando `protected $table = 'nombre_en_singular';`.
*   **Timestamps:** El diseño original no contempla `created_at` ni `updated_at`. Todos los modelos deben incluir `public $timestamps = false;` a menos que se indique lo contrario.

## Reglas de Seguridad y Eloquent (Mass Assignment)
*   Usa el arreglo `protected $fillable` **exclusivamente** para los datos de entrada autorizados que provienen de formularios o peticiones del usuario (ej. `id_alumno`, `id_materia`, `fecha`).
*   **NUNCA** incluyas atributos calculados, de control interno o generados por el sistema (como `folio`, estatus por defecto, o IDs de sesión) dentro del `$fillable`. Estos deben asignarse a nivel de Controlador.

## Lógica de Negocio y Base de Datos (Diseño 3FN)
*   **Asignación de Aulas:** El `id_aula` no se asigna en la disponibilidad del profesor. Se asigna directamente en la tabla `asesoria` al momento de confirmar la cita para evitar bloquear espacios físicos innecesariamente.
*   **Selección de Materia:** El `id_materia` reside en la tabla `asesoria` (el alumno elige la materia al agendar), no en la disponibilidad general del docente.
*   **Catálogos:** Las entidades `materia` y `aula` son tablas de catálogo. Sus modelos existen para resolver relaciones, pero se pueblan mediante Seeders, no mediante interfaces de usuario.
*   **Validaciones Críticas:** La cancelación de citas exige verificar que falten al menos 12 horas para la sesión (Regla RN-01).

## Instrucción de Asistencia
Cuando generes código, vistas Blade o controladores, respeta estrictamente estas convenciones. No agregues funciones, atributos o dependencias que no estén justificadas por el modelo de datos relacional del proyecto.

## Mapa de Procesos Funcionales (P-01 a P-04)
Al generar Controladores y rutas, respeta la siguiente separación de responsabilidades:
*   **P-01 Gestión de disponibilidad docente:** CRUD exclusivo para el Profesor (RF-01 a RF-05). La IA debe programar la creación, edición, baja y consulta de bloques de horario en el modelo `Disponibilidad`.
*   **P-02 Control de agendamiento:** Rutas y vistas que permitan al Alumno consultar la oferta (RF-07) y ejecutar reservas directas sin intermediación manual (RF-08).
*   **P-03 Administración de cancelaciones y cupos:** Lógica de seguimiento para que el Alumno consulte sus citas (RF-12), con capacidad de cancelación (RF-09a) evaluando límites de tiempo (RF-09b) y liberando el cupo automáticamente (RF-11).
*   **P-04 Control de espacio físico y sincronización:** Rutinas de backend (eventos u *Observers* en Laravel) para validar choques de aulas (RF-06a), emitir alertas (RF-06b) y sincronizar estados en tiempo real (RF-10).

## Reglas de Negocio Críticas (Backend Validations)
La IA debe programar estas reglas en los `FormRequests` o dentro de transacciones de Base de Datos:
*   **RN-01 (Regla de 12 horas):** En la cancelación (P-03), el sistema DEBE bloquear la petición si la diferencia entre la hora actual y la `fecha` de la sesión es menor a 12 horas.
*   **RN-02 (Empalme de aulas):** Al agendar (P-02), se DEBE verificar la disponibilidad física del espacio y el margen de traslado del docente. No se permite asignar el mismo `id_aula` en el mismo horario a dos sesiones distintas.
*   *(Nota: RN-03 sobre límite de inasistencias se encuentra pendiente de autorización, no programar lógica punitiva compleja por el momento).*

## Requisitos No Funcionales (Arquitectura y UI)
*   **Seguridad y Roles (RNF-02):** Todo el sistema requiere autenticación. Protege las rutas mediante *Middleware* verificando los roles (Alumno, Profesor, Coordinador) antes de permitir transacciones sobre la disponibilidad o citas.
*   **Desempeño y Usabilidad (RNF-03, RNF-04):** Aplica *Eager Loading* (`with()`) en consultas de Eloquent para evitar el problema N+1 y garantizar tiempos de respuesta menores a 2 segundos. La interfaz de agendamiento web debe requerir un máximo de 3 clics del usuario para confirmar la reserva.
*   **Sincronización Inmediata (RNF-05):** Las liberaciones de cupo por cancelación deben reflejarse de forma inmediata en las sesiones activas de otros alumnos.

## Catálogo de Requisitos Funcionales (RF) - Implementación en Laravel
Para la generación de código, los RF aprobados se estructuran en los siguientes controladores y servicios de la arquitectura:

**Módulo Docente (`DisponibilidadController`)**
*   **RF-01 (Registrar):** Método `store()`. Genera bloques indicando materia y modalidad. NO asigna aula en este paso.
*   **RF-02 (Modificar):** Método `update()`. Actualiza bloques. Es obligatorio invocar la validación de empalmes (RF-06a) antes de persistir cambios.
*   **RF-03 (Eliminar):** Método `destroy()`. Aplica la baja del bloque ofertado.
*   **RF-04 y RF-05 (Consultar):** Método `index()`. Vistas de solo lectura para la agenda del docente y la supervisión global del coordinador.

**Módulo Estudiantil (`AsesoriaController`)**
*   **RF-07 (Consultar Oferta):** Retorna la vista con los filtros de búsqueda para explorar docentes, materias y horarios libres.
*   **RF-12 (Consultar Mis Citas):** Lista el historial y citas activas del alumno autenticado (opera como precondición en la interfaz para poder cancelar).
*   **RF-08 (Agendar):** Método `store()`. Crea el registro en `asesoria`, asigna el aula física y actualiza la `disponibilidad` vinculada a estatus 'Ocupado'.
*   **RF-09a (Cancelar):** Método que modifica el estatus de la cita a 'Cancelada'.

**Automatización del Sistema (Services y Observers)**
*   **RF-06a (Validar Aulas):** Lógica interna (ej. *FormRequest* o capa de *Service*) invocada por RF-02 y RF-08 para verificar disponibilidad y prevenir traslapes físicos de cubículos.
*   **RF-06b (Notificar):** Disparo de alertas mediante *Events/Listeners* al confirmar o cancelar citas.
*   **RF-09b (Validar 12 hrs):** Condición de backend que bloquea la ejecución del RF-09a si el cálculo `fecha_asesoria - now()` es inferior a 12 horas.
*   **RF-10 y RF-11 (Sincronizar y Liberar Cupo):** Utilizar *Eloquent Observers* (ej. evento `updated`) para devolver automáticamente la disponibilidad original a estatus 'Disponible' en tiempo real tras concretarse una cancelación.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
