# Limpieza selectiva operativa

Script: `database/scripts/limpieza_selectiva_operativa.sql`

Este script reinicia datos operativos sin borrar tablas maestras importantes.

## Conserva

- Usuarios, roles y permisos.
- Empleados.
- Flota.
- Articulos, categorias y unidades de medida.
- Depositos, bases, proveedores y ajustes.
- Configuraciones y catalogos.
- Motivos de orden de trabajo.

## Limpia

- Inventarios.
- Cubiertas y movimientos de cubiertas.
- Ordenes de trabajo y checklists.
- Compras, pagos, pedidos, entradas y transferencias.
- Solicitudes de repuestos no catalogados.
- Servicios/verificaciones realizadas.
- Entregas de herramientas.
- Entregas de ropa y EPP.
- Reparaciones de articulos (ordenes, detalles y reclamos).
- Bitacoras y chat.
- Cronograma operativo de empleados, conservando patrones.
- Reasegura los permisos de entrega de ropa/EPP para roles administradores.

## Uso recomendado

1. Hacer backup completo de la base.
2. Confirmar que la conexion apunta a la base correcta.
3. Ejecutar el SQL desde phpMyAdmin, MySQL Workbench o consola MySQL.
4. Revisar que usuarios, permisos, articulos, empleados y flota sigan cargados.

No ejecutar `migrate:fresh` para este caso.
