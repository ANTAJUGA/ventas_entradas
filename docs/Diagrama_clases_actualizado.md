# TicketFlow - Diagrama de clases actualizado

Este diagrama representa el modelo persistente implementado en las migraciones
de CodeIgniter 4. Los campos de auditoría (`created_at`, `updated_at` y
`deleted_at`) se omiten para conservar la legibilidad.

```mermaid
classDiagram
direction TB

class Rol {
  +int id PK
  +string nombre
  +string slug UQ
  +string descripcion
  +bool activo
}

class Usuario {
  +int id PK
  +int rol_id FK
  +string nombres
  +string apellidos
  +string email UQ
  +string password_hash
  +string telefono
  +string estado
  +datetime ultimo_acceso
}

class Artista {
  +int id PK
  +string nombre_artistico
  +string nombre_real
  +string tipo
  +string genero
  +string pais
  +text descripcion
  +string imagen
  +string estado
}

class ArtistasController {
  -ArtistaModel artistaModel
  -int LIMITE_RESUMEN_DESCRIPCION
  +index() string
  +new() string
  +create() RedirectResponse
  +edit(int id) string
  +update(int id) RedirectResponse
  +delete(int id) RedirectResponse
  -datosFormulario() array
  -tipoEtiqueta(string tipo) string
  -resumirDescripcion(string descripcion) string
}

class ArtistaModel {
  +string table
  +array allowedFields
  +array validationRules
}

class Evento {
  +int id PK
  +int organizador_id FK
  +string nombre
  +string slug UQ
  +text descripcion
  +string categoria
  +string imagen
  +string estado
  +datetime publicado_at
}

class Funcion {
  +int id PK
  +int evento_id FK
  +string nombre
  +datetime fecha_inicio
  +datetime fecha_fin
  +datetime venta_inicio
  +datetime venta_fin
  +string recinto
  +string direccion
  +string ciudad
  +int aforo_total
  +string estado
}

class TipoEntrada {
  +int id PK
  +int funcion_id FK
  +string nombre
  +string descripcion
  +decimal precio
  +int cupo
  +int limite_por_compra
  +bool activo
}

class Descuento {
  +int id PK
  +int evento_id FK
  +string codigo UQ
  +string nombre
  +string descripcion
  +string tipo
  +decimal valor
  +decimal compra_minima
  +datetime fecha_inicio
  +datetime fecha_fin
  +int limite_usos
  +int usos_actuales
  +bool activo
}

class Venta {
  +int id PK
  +string codigo UQ
  +int cliente_id FK
  +int vendedor_id FK
  +int descuento_id FK
  +string cliente_nombre
  +string cliente_email
  +decimal subtotal
  +decimal descuento_total
  +decimal total
  +string estado
  +string metodo_pago
  +string referencia_pago
  +datetime pagado_at
}

class DetalleVenta {
  +int id PK
  +int venta_id FK
  +int tipo_entrada_id FK
  +int cantidad
  +decimal precio_unitario
  +decimal descuento_unitario
  +decimal subtotal
}

class Entrada {
  +int id PK
  +int detalle_venta_id FK
  +string codigo UQ
  +string qr_token_hash UQ
  +string titular_nombre
  +string titular_email
  +string asiento
  +string estado
  +datetime emitida_at
  +datetime usada_at
  +datetime anulada_at
}

class ControlAcceso {
  +int id PK
  +int entrada_id FK
  +int usuario_id FK
  +string resultado
  +string motivo
  +string punto_acceso
  +string direccion_ip
  +datetime registrado_at
}

Rol "1" --> "0..*" Usuario : asigna
Usuario "1" --> "0..*" Evento : organiza
Evento "1" *-- "0..*" Funcion : contiene
Funcion "1" *-- "0..*" TipoEntrada : ofrece
Evento "0..1" --> "0..*" Descuento : aplica
Usuario "0..1" --> "0..*" Venta : cliente
Usuario "0..1" --> "0..*" Venta : vendedor
Descuento "0..1" --> "0..*" Venta : se utiliza en
Venta "1" *-- "0..*" DetalleVenta : incluye
TipoEntrada "1" --> "0..*" DetalleVenta : clasifica
DetalleVenta "1" *-- "0..*" Entrada : genera
Entrada "1" *-- "0..*" ControlAcceso : registra intentos
Usuario "0..1" --> "0..*" ControlAcceso : operador

ArtistasController --> ArtistaModel : dependencia inyectada
ArtistaModel --> Artista : persiste

note for Artista "Módulo independiente: actualmente no tiene claves foráneas."
note for ArtistasController "Evolución: extracción de método, eliminación de duplicación e inyección de dependencia."
```

## Restricciones compuestas

- `TipoEntrada`: (`funcion_id`, `nombre`) es único.
- `DetalleVenta`: (`venta_id`, `tipo_entrada_id`) es único.

## Relación futura no implementada

El repositorio propone relacionar `Artista` y `Evento` mediante una tabla
intermedia `evento_artista`. Esa relación no aparece en el diagrama porque
todavía no existe en las migraciones ni en los modelos.
