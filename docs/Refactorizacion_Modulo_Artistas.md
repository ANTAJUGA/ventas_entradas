# Guía de exposición: módulo Artistas

El módulo se implementó como un CRUD independiente. La tabla `artistas` no
contiene claves foráneas y no modifica `eventos` ni `funciones`.

El controlador contiene seis malos olores intencionales. Están organizados
como seis secciones consecutivas. Cada sección presenta:

1. una función o bloque completo que está activo y funciona;
2. una explicación breve del problema;
3. el reemplazo completo comentado inmediatamente debajo.

No hay métodos privados declarados dentro de otros métodos. Los reemplazos
comentados están ubicados en el nivel de la clase, que es donde PHP permite
declararlos.

## Secuencia sugerida de commits manuales

1. **Nombres misteriosos:** sustituir el método `index()` por la versión que
   utiliza `$artistas` y `$artista`.
2. **Condicional larga:** sustituir los `if/elseif` de `tipoEtiqueta()` por una
   expresión `match`.
3. **Método largo:** extraer la captura y normalización de datos de `create()`
   hacia `datosFormulario()`.
4. **Código duplicado:** reutilizar `datosFormulario()` desde `create()` y
   `update()`.
5. **Dependencia creada directamente:** inyectar `ArtistaModel`; las demás
   funciones pueden seguir llamando a `artistaModel()`.
6. **Número mágico:** sustituir el valor `80` por la constante
   `LIMITE_RESUMEN_DESCRIPCION`.

Antes de descomentar un reemplazo debe eliminarse el bloque activo equivalente,
porque no pueden coexistir dos métodos con el mismo nombre.

Mensajes sugeridos para los commits de la exposición:

```text
refactor(artistas): reemplazar nombres misteriosos
refactor(artistas): simplificar condicional de tipos
refactor(artistas): dividir método largo de creación
refactor(artistas): eliminar duplicación de datos del formulario
refactor(artistas): inyectar dependencia ArtistaModel
refactor(artistas): reemplazar número mágico por constante
```

Después de cada cambio se recomienda ejecutar:

```powershell
php -l app\Controllers\Admin\Artistas.php
vendor\bin\phpunit --no-coverage --filter Artista
```

## Relación futura

La etapa futura propuesta es una relación muchos-a-muchos:

```text
Artista --< evento_artista >-- Evento --< Funcion
```

La tabla intermedia se incorporará en otra migración cuando el CRUD haya sido
refactorizado. No forma parte de esta entrega para conservar el aislamiento
solicitado.
