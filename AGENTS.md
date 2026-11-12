# AGENTS.md

# Aprende+ - Academic Advising Management System

## Descripción General

Aprende+ es una plataforma web para la gestión integral de asesorías académicas universitarias.

El sistema permite:

- Registro y administración de disponibilidad docente.
- Consulta de oferta de asesorías.
- Agendamiento de sesiones académicas.
- Asignación de espacios físicos.
- Control de cancelaciones.
- Notificaciones automáticas.
- Seguimiento de citas activas e históricas.

El proyecto surge de las necesidades identificadas durante el análisis realizado al área de coordinación académica.

---

# Stack Tecnológico

## Backend

- PHP 8.4
- Laravel 11
- PostgreSQL 16

## Herramientas

- GitHub
- Visual Studio Code

---

# Convenciones Generales

## Modelos

Usar PascalCase y singular:

- Alumno
- Profesor
- Materia
- Aula
- Disponibilidad
- Asesoria
- Notificacion
- Cancelacion
- ProfesorMateria

Ejemplo:

```php
class Asesoria extends Model
{
}
```

---

## Tablas

Todas las tablas utilizan:

- snake_case
- singular

Ejemplos:

- alumno
- profesor
- materia
- aula
- disponibilidad
- asesoria
- notificacion
- cancelacion
- profesor_materia

Todos los modelos deben declarar explícitamente:

```php
protected $table = 'nombre_tabla';
```

---

## Timestamps

El modelo relacional aprobado no contempla timestamps automáticos.

Por defecto:

```php
public $timestamps = false;
```

Solo agregar timestamps cuando exista justificación funcional.

---

# Requisitos Funcionales Aprobados

## Gestión de Disponibilidad

RF-01 Registrar disponibilidad.

RF-02 Modificar disponibilidad.

RF-03 Eliminar disponibilidad.

RF-04 Consultar disponibilidad docente.

RF-05 Consultar disponibilidad general.

---

## Gestión de Espacios

RF-06a Validar disponibilidad de aula.

RF-06b Notificar conflictos o cambios.

---

## Gestión de Asesorías

RF-07 Consultar oferta de asesorías.

RF-08 Agendar asesoría.

RF-09a Cancelar asesoría.

RF-09b Validar política de cancelación.

RF-10 Sincronizar estados del sistema.

RF-11 Liberar cupos automáticamente.

RF-12 Consultar asesorías activas y agendadas.

---

# Requisitos Pendientes de Autorización

Los siguientes requisitos surgieron durante la fase de diseño e implementación.

NO forman parte del alcance original y deben considerarse sujetos a aprobación docente.

RF-13 Alta de alumnos.

RF-14 Baja de alumnos.

RF-15 Consulta de alumnos.

RF-16 Modificación de alumnos.

La IA puede implementarlos únicamente si el usuario indica que han sido aprobados.

---

# Reglas de Negocio

## RN-01 Política de Cancelación

Una asesoría únicamente puede cancelarse si faltan 12 horas o más para la sesión programada.

En caso contrario:

- la operación debe bloquearse;
- se debe informar el motivo al usuario.

---

## RN-02 Empalme de Espacios

Dos asesorías no pueden ocupar la misma aula durante el mismo horario.

Toda reserva debe validar disponibilidad física antes de confirmarse.

---

# Modelo Conceptual

## Disponibilidad

Representa bloques ofertados por un profesor.

No contiene:

- aula;
- materia seleccionada por el alumno.

La disponibilidad solo representa tiempo disponible.

---

## Asesoria

Representa una cita concreta.

Contiene:

- alumno;
- profesor;
- materia;
- aula;
- fecha;
- horario;
- estado.

---

## Cancelacion

Representa el evento de cancelación de una asesoría.

Relación:

Asesoria 1 ---- 0..1 Cancelacion

Una asesoría puede tener como máximo una cancelación.

Información mínima:

- asesoria_id
- motivo
- fecha_hora_cancelacion
- cancelado_por
- observaciones

No almacenar estos datos directamente en la tabla asesoria.

---

# Catálogos

Las entidades:

- Materia
- Aula

son catálogos institucionales.

Deben poblarse mediante seeders.

No requieren CRUD administrativo salvo instrucción explícita.

---

# Seguridad

El sistema utiliza autenticación obligatoria.

Roles:

- Alumno
- Profesor
- Coordinador

Toda ruta debe estar protegida mediante Middleware.

---

# Eloquent

## Fillable

Incluir únicamente datos provenientes del usuario.

Ejemplo:

```php
protected $fillable = [
    'id_alumno',
    'id_profesor',
    'id_materia',
    'fecha',
    'hora'
];
```

No incluir:

- estados automáticos;
- folios;
- identificadores internos;
- datos calculados.

---

# Buenas Prácticas para IA

Cuando se generen:

- modelos;
- controladores;
- migraciones;
- requests;
- servicios;
- vistas Blade;

respetar estrictamente:

- Laravel 11;
- PostgreSQL;
- relaciones definidas;
- normalización 3FN;
- reglas RN-01 y RN-02;
- trazabilidad de requisitos aprobados.

No agregar entidades o funcionalidades fuera del alcance sin indicarlo explícitamente.