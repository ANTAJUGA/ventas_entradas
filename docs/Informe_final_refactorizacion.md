# Informe final de refactorización - módulo Artistas

## 1. Objetivo

Se refactorizó el CRUD de artistas sin modificar su comportamiento observable.
El trabajo se dividió en seis cambios independientes para conservar un historial
auditable y permitir la comparación antes/después en cada punto.

## 2. Línea base

El punto de partida es el commit `a7b510d`, que revierte la aplicación conjunta
de las refactorizaciones. En ese estado, el controlador contiene los seis malos
olores activos y las pruebas específicas del módulo producen:

```text
OK (3 tests, 11 assertions)
```

Comandos utilizados:

```bash
php -l app/Controllers/Admin/Artistas.php
vendor/bin/phpunit --no-coverage --filter Artista
```

## 3. Refactorizaciones realizadas

### 3.1 Nombres expresivos

- **Nivel:** métodos.
- **Mal olor:** variables `$x` y `$a` sin significado de dominio.
- **Solución:** se sustituyeron por `$artistas` y `$artista`.
- **Commit:** `98af896 refactor(artistas): reemplazar nombres misteriosos`.
- **Resultado:** el flujo de `index()` se entiende sin deducir qué contiene cada
  variable.

### 3.2 Simplificación del condicional

- **Nivel:** condicionales.
- **Mal olor:** cadena `if/elseif` para traducir el tipo de artista.
- **Solución:** se reemplazó por una expresión `match`.
- **Commit:** `3d537d8 refactor(artistas): simplificar condicional de tipos`.
- **Resultado:** la correspondencia entre valores y etiquetas queda expresada
  directamente.

### 3.3 Extracción de método

- **Nivel:** métodos.
- **Mal olor:** `create()` capturaba la petición, normalizaba datos, persistía y
  construía la respuesta.
- **Solución:** se extrajo `datosFormulario()`.
- **Commit:** `173e583 refactor(artistas): dividir método largo de creación`.
- **Resultado:** `create()` conserva una única responsabilidad de coordinación.

### 3.4 Eliminación de código duplicado

- **Nivel:** métodos.
- **Mal olor:** `create()` y `update()` repetían la construcción de los mismos
  datos.
- **Solución:** ambos métodos reutilizan `datosFormulario()`.
- **Commit:** `6017360 refactor(artistas): eliminar duplicación de datos del formulario`.
- **Resultado:** existe un solo punto para normalizar los campos del formulario.

### 3.5 Inyección de dependencia

- **Nivel:** clases/objetos.
- **Mal olor:** el controlador creaba una instancia nueva de `ArtistaModel` cada
  vez que la necesitaba.
- **Solución:** `ArtistaModel` se recibe opcionalmente en el constructor y se
  conserva como atributo.
- **Commit:** `ab90e9c refactor(artistas): inyectar dependencia ArtistaModel`.
- **Resultado:** disminuye el acoplamiento y el controlador admite dobles de
  prueba.

### 3.6 Sustitución de número mágico

- **Nivel:** datos.
- **Mal olor:** el literal `80` aparecía dentro de la lógica de resumen.
- **Solución:** se creó la constante `LIMITE_RESUMEN_DESCRIPCION`.
- **Commit:** `5636981 refactor(artistas): reemplazar número mágico por constante`.
- **Resultado:** el dato tiene nombre, propósito y un único lugar de definición.

## 4. Cobertura de los cuatro niveles

| Nivel solicitado | Refactorizaciones que lo cubren |
|---|---|
| Métodos | Nombres expresivos, extracción de método y eliminación de duplicación |
| Clases/objetos | Inyección de `ArtistaModel` |
| Datos | Constante `LIMITE_RESUMEN_DESCRIPCION` |
| Condicionales | Sustitución de `if/elseif` por `match` |

## 5. Verificación

Antes del primer cambio y después de cada uno de los seis commits se ejecutaron:

```bash
php -l app/Controllers/Admin/Artistas.php
vendor/bin/phpunit --no-coverage --filter Artista
```

En los siete puntos de control el resultado fue:

```text
No syntax errors detected in app/Controllers/Admin/Artistas.php
OK (3 tests, 11 assertions)
```

La suite global ejecuta nueve pruebas. Ocho pasan y una prueba preexistente,
`DashboardTest::testAdminDashboardIsAvailable`, falla porque solicita `/admin`
sin sesión y espera HTTP `200`; el filtro de autenticación responde
correctamente con HTTP `302`. Este comportamiento no fue introducido por el
refactoring de Artistas y debe corregirse en la prueba de autenticación en un
cambio separado.

## 6. Evolución del diseño

Antes del refactoring, `Artistas` construía directamente `ArtistaModel`, repetía
la normalización de datos y mezclaba varias responsabilidades en `create()`.
Después del refactoring:

- `Artistas` recibe su dependencia `ArtistaModel`;
- `datosFormulario()` centraliza la captura y normalización;
- `create()` y `update()` reutilizan esa operación;
- `tipoEtiqueta()` expresa la decisión mediante `match`;
- el límite del resumen está definido como dato con nombre.

El diagrama actualizado se encuentra en
[`Diagrama_clases_actualizado.md`](Diagrama_clases_actualizado.md). También
incorpora la tabla `artistas`, añadida al diseño persistente, y aclara que aún no
existe una relación implementada entre `Artista` y `Evento`.
