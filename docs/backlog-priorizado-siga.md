# Backlog priorizado - SIGA

## P0 (critico)
- API login/token + endpoint perfil/permisos.
- Matriz unificada de permisos movil/web.
- Checklist movil (alta + listado + detalle).
- Notificaciones de vencimiento (servicios y verificaciones).
- Auditoria basica por accion sensible.

## P1 (alto)
- Flujo de aprobaciones en compras.
- Flujo de aprobaciones en reparaciones.
- Solicitudes de repuestos desde app.
- Devoluciones de reparacion desde app.
- Adjuntos de evidencia en modulos criticos.

## P2 (medio)
- Dashboard ejecutivo con costo por unidad.
- Exportacion BI (dataset curado diario).
- Reglas automaticas de consistencia de datos.
- Filtros guardados por usuario.
- Acciones masivas en vistas administrativas.

## Historias tecnicas transversales
- Test feature por endpoint critico (auth, permisos, aprobaciones).
- Politicas de acceso (Policies/Gates) donde aplique.
- Registro de eventos de negocio para trazabilidad.
- Monitoreo de jobs y colas de notificacion.

## Definicion de terminado (DoD)
- Validaciones de backend completas.
- Permisos aplicados y probados.
- Log de auditoria generado.
- Tests pasando en CI.
- Documentacion de uso actualizada en docs.
