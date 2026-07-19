-- Limpieza selectiva operativa SIGA
-- Fecha: 2026-06-28
--
-- OBJETIVO
-- Reiniciar datos operativos sin borrar tablas maestras importantes.
--
-- CONSERVA, entre otras:
-- users, roles, permissions, model_has_roles, model_has_permissions, role_has_permissions
-- (Nota: por pedido operativo puntual, este script tambien limpia varias tablas maestras)
--
-- LIMPIA:
-- inventarios, cubiertas, herramientas y ropa/EPP entregados,
-- junto con movimientos/documentos operativos relacionados.
-- Adicionalmente, por pedido operativo puntual, limpia tambien maestras:
-- empleados, flota, articulos, categorias, unidad_medidas, depositos, bases,
-- ajustes, proveedores, bancos, provincias, ciudades,
-- titular, tipo_vehiculo, cia_seguro, marca_vehiculo, marca_carroceria,
-- tipo_motor, modelo_motor, tipo_caja, modelo_caja,
-- servicios_asignados, configuracion_intervalos_servicio,
-- configuracion_vencimientos_verificacion,
-- cronograma_patrones.
--
-- IMPORTANTE:
-- 1) Hacer backup completo antes de ejecutar.
-- 2) Ejecutar solo en la base correcta.
-- 3) Este script no borra estructura ni migraciones.
-- 4) No usar migrate:fresh para este caso.

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- Comunicacion / auditoria operativa
-- =========================================================
DELETE FROM chat_mensajes;
DELETE FROM chat_conversaciones;
DELETE FROM bitacoras;

-- =========================================================
-- Herramientas entregadas
-- =========================================================
DELETE FROM entrega_herramienta_detalles;
DELETE FROM entregas_herramientas;

-- =========================================================
-- Ropa y EPP entregados
-- =========================================================
DELETE FROM entrega_ropa_epp_detalles;
DELETE FROM entregas_ropa_epp;

-- =========================================================
-- Cronograma operativo de empleados
-- Conserva cronograma_patrones.
-- =========================================================
DELETE FROM cronograma_novedades;
DELETE FROM cronograma_asignaciones;

-- =========================================================
-- Inventario, entradas, compras, pedidos y transferencias
-- Conserva articulos, categorias, proveedores, depositos y bases.
-- =========================================================
DELETE FROM solicitudes_repuestos;

DELETE FROM transferencia_deposito_detalles;
DELETE FROM transferencias_deposito;

DELETE FROM detalle_entrada;
DELETE FROM entrada;

DELETE FROM compra_pagos;
DELETE FROM compra_detalles;
DELETE FROM compras;
DELETE FROM tmp_compra;

DELETE FROM pedido_detalle_articulo;
DELETE FROM pedidos_articulo;
DELETE FROM tmp_pedido_articulo;

DELETE FROM inventarios;

-- =========================================================
-- Servicios y verificaciones realizadas
-- Conserva configuraciones.
-- =========================================================
DELETE FROM registros_servicios_kilometraje;
DELETE FROM registros_verificaciones_tecnicas;

-- =========================================================
-- Ordenes de trabajo, checklists y articulos usados
-- Conserva empleados, flota, articulos y motivos catalogo.
-- =========================================================
DELETE FROM orden_trabajo_motivo;
DELETE FROM orden_trabajo_articulos;
DELETE FROM controles_unidad;
DELETE FROM ordenes_trabajo;

-- =========================================================
-- Gestion de cubiertas
-- Conserva articulos y configuracion de ejes de flota.
-- Limpia cubiertas numeradas y movimientos.
-- =========================================================
DELETE FROM detalle_cambio_cubiertas;
DELETE FROM cambios_cubiertas;
DELETE FROM cubiertas;

-- =========================================================
-- Repuestos/servicios asociados historicos de flota
-- Conserva flota y servicios_asignados.
-- =========================================================
DELETE FROM flota_repuestos;
DELETE FROM flota_servicio_asignado_historial;

-- =========================================================
-- Reparaciones de articulos
-- Conserva proveedores, articulos y ajustes.
-- =========================================================
DELETE FROM reparacion_articulo_reclamos;
DELETE FROM reparacion_articulo_detalles;
DELETE FROM reparaciones_articulos;

-- =========================================================
-- Maestras solicitadas para limpieza puntual
-- =========================================================
DELETE FROM flota;
DELETE FROM empleados;

DELETE FROM articulos;
DELETE FROM categorias;
DELETE FROM unidad_medidas;

DELETE FROM bases;
DELETE FROM depositos;

DELETE FROM ajustes;
DELETE FROM proveedores;
DELETE FROM bancos;

DELETE FROM servicios_asignados;
DELETE FROM configuracion_intervalos_servicio;
DELETE FROM configuracion_vencimientos_verificacion;

DELETE FROM cronograma_patrones;

DELETE FROM marca_vehiculo;
DELETE FROM marca_carroceria;
DELETE FROM cia_seguro;
DELETE FROM tipo_vehiculo;
DELETE FROM titular;
DELETE FROM modelo_motor;
DELETE FROM tipo_motor;
DELETE FROM modelo_caja;
DELETE FROM tipo_caja;

DELETE FROM ciudades;
DELETE FROM provincias;

-- =========================================================
-- Reinicio de autoincrementales de tablas limpiadas
-- =========================================================
ALTER TABLE chat_mensajes AUTO_INCREMENT = 1;
ALTER TABLE chat_conversaciones AUTO_INCREMENT = 1;
ALTER TABLE bitacoras AUTO_INCREMENT = 1;

ALTER TABLE entrega_herramienta_detalles AUTO_INCREMENT = 1;
ALTER TABLE entregas_herramientas AUTO_INCREMENT = 1;

ALTER TABLE entrega_ropa_epp_detalles AUTO_INCREMENT = 1;
ALTER TABLE entregas_ropa_epp AUTO_INCREMENT = 1;

ALTER TABLE cronograma_novedades AUTO_INCREMENT = 1;
ALTER TABLE cronograma_asignaciones AUTO_INCREMENT = 1;

ALTER TABLE solicitudes_repuestos AUTO_INCREMENT = 1;

ALTER TABLE transferencia_deposito_detalles AUTO_INCREMENT = 1;
ALTER TABLE transferencias_deposito AUTO_INCREMENT = 1;

ALTER TABLE detalle_entrada AUTO_INCREMENT = 1;
ALTER TABLE entrada AUTO_INCREMENT = 1;

ALTER TABLE compra_pagos AUTO_INCREMENT = 1;
ALTER TABLE compra_detalles AUTO_INCREMENT = 1;
ALTER TABLE compras AUTO_INCREMENT = 1;
ALTER TABLE tmp_compra AUTO_INCREMENT = 1;

ALTER TABLE pedido_detalle_articulo AUTO_INCREMENT = 1;
ALTER TABLE pedidos_articulo AUTO_INCREMENT = 1;
ALTER TABLE tmp_pedido_articulo AUTO_INCREMENT = 1;

ALTER TABLE inventarios AUTO_INCREMENT = 1;

ALTER TABLE registros_servicios_kilometraje AUTO_INCREMENT = 1;
ALTER TABLE registros_verificaciones_tecnicas AUTO_INCREMENT = 1;

ALTER TABLE orden_trabajo_motivo AUTO_INCREMENT = 1;
ALTER TABLE orden_trabajo_articulos AUTO_INCREMENT = 1;
ALTER TABLE controles_unidad AUTO_INCREMENT = 1;
ALTER TABLE ordenes_trabajo AUTO_INCREMENT = 1;

ALTER TABLE detalle_cambio_cubiertas AUTO_INCREMENT = 1;
ALTER TABLE cambios_cubiertas AUTO_INCREMENT = 1;
ALTER TABLE cubiertas AUTO_INCREMENT = 1;

ALTER TABLE flota_repuestos AUTO_INCREMENT = 1;
ALTER TABLE flota_servicio_asignado_historial AUTO_INCREMENT = 1;

ALTER TABLE reparacion_articulo_reclamos AUTO_INCREMENT = 1;
ALTER TABLE reparacion_articulo_detalles AUTO_INCREMENT = 1;
ALTER TABLE reparaciones_articulos AUTO_INCREMENT = 1;

ALTER TABLE flota AUTO_INCREMENT = 1;
ALTER TABLE empleados AUTO_INCREMENT = 1;

ALTER TABLE articulos AUTO_INCREMENT = 1;
ALTER TABLE categorias AUTO_INCREMENT = 1;
ALTER TABLE unidad_medidas AUTO_INCREMENT = 1;

ALTER TABLE bases AUTO_INCREMENT = 1;
ALTER TABLE depositos AUTO_INCREMENT = 1;

ALTER TABLE ajustes AUTO_INCREMENT = 1;
ALTER TABLE proveedores AUTO_INCREMENT = 1;
ALTER TABLE bancos AUTO_INCREMENT = 1;

ALTER TABLE servicios_asignados AUTO_INCREMENT = 1;
ALTER TABLE configuracion_intervalos_servicio AUTO_INCREMENT = 1;
ALTER TABLE configuracion_vencimientos_verificacion AUTO_INCREMENT = 1;

ALTER TABLE cronograma_patrones AUTO_INCREMENT = 1;

ALTER TABLE marca_vehiculo AUTO_INCREMENT = 1;
ALTER TABLE marca_carroceria AUTO_INCREMENT = 1;
ALTER TABLE cia_seguro AUTO_INCREMENT = 1;
ALTER TABLE tipo_vehiculo AUTO_INCREMENT = 1;
ALTER TABLE titular AUTO_INCREMENT = 1;
ALTER TABLE modelo_motor AUTO_INCREMENT = 1;
ALTER TABLE tipo_motor AUTO_INCREMENT = 1;
ALTER TABLE modelo_caja AUTO_INCREMENT = 1;
ALTER TABLE tipo_caja AUTO_INCREMENT = 1;

ALTER TABLE ciudades AUTO_INCREMENT = 1;
ALTER TABLE provincias AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

-- =========================================================
-- Permisos nuevos de entrega de ropa y EPP
-- Conserva roles existentes y asigna los permisos a roles administradores.
-- =========================================================
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at)
VALUES
    ('entregas-ropa-epp.ver', 'web', NOW(), NOW()),
    ('entregas-ropa-epp.crear', 'web', NOW(), NOW()),
    ('entregas-ropa-epp.editar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.ver', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.crear', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.editar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.aprobar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.rechazar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.catalogar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.generar-pedido', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.cerrar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.exportar', 'web', NOW(), NOW()),
    ('solicitudes-repuestos.imprimir', 'web', NOW(), NOW());

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT permissions.id, roles.id
FROM permissions
CROSS JOIN roles
WHERE permissions.name IN (
    'entregas-ropa-epp.ver',
    'entregas-ropa-epp.crear',
    'entregas-ropa-epp.editar',
    'solicitudes-repuestos.ver',
    'solicitudes-repuestos.crear',
    'solicitudes-repuestos.editar',
    'solicitudes-repuestos.aprobar',
    'solicitudes-repuestos.rechazar',
    'solicitudes-repuestos.catalogar',
    'solicitudes-repuestos.generar-pedido',
    'solicitudes-repuestos.cerrar',
    'solicitudes-repuestos.exportar',
    'solicitudes-repuestos.imprimir'
)
AND roles.name IN (
    'ADMIN',
    'ADMINISTRADOR',
    'Administrador',
    'SUPERUSUARIO',
    'SUPER USER',
    'SUPERUSER',
    'RROT'
);

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT permissions.id, roles.id
FROM permissions
CROSS JOIN roles
WHERE permissions.name IN (
    'solicitudes-repuestos.ver',
    'solicitudes-repuestos.crear',
    'solicitudes-repuestos.editar'
)
AND roles.name IN (
    'SUPERVISOR',
    'JEFE DE TALLER',
    'TALLER'
);
