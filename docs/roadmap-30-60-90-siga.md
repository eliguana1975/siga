# Roadmap 30/60/90 - SIGA

## Objetivo
Lanzar mejoras de alto impacto en operacion, control y trazabilidad con entregas incrementales cada 30 dias.

## Resultado esperado al dia 90
- App movil operativa por roles clave.
- Flujos de aprobacion implementados en modulos criticos.
- Alertas automticas por vencimientos y stock.
- Dashboard ejecutivo con KPI de mantenimiento e inventario.
- Auditoria funcional de acciones sensibles.

## Fase 1 (Dia 1-30) - Fundacion y quick wins
### Entregables
- API de autenticacion para app movil con token.
- Endpoint de perfil/permisos por usuario.
- Primer modulo movil: checklist de unidad (lectura + carga).
- Notificaciones internas por vencimientos criticos.
- Dashboard base (3 KPI): unidades vencidas, reparaciones pendientes, stock critico.

### KPI fase
- Tiempo medio de carga de checklist < 2 min.
- 100% de acciones del modulo con usuario y timestamp.
- 0 endpoints sin control de permiso en modulo movil inicial.

## Fase 2 (Dia 31-60) - Flujo operativo completo
### Entregables
- Flujo de aprobaciones (pendiente/aprobado/rechazado/cerrado) en compras y reparaciones.
- App movil: solicitudes de repuestos y devoluciones de reparacion.
- Adjuntos de evidencia (fotos/pdf) por registro clave.
- Reportes operativos por base/proveedor/categoria.

### KPI fase
- Reduccion de tiempo de aprobacion >= 25%.
- 90% de solicitudes con evidencia adjunta cuando aplica.
- Tasa de registros rechazados por datos incompletos < 10%.

## Fase 3 (Dia 61-90) - Analitica y robustez
### Entregables
- Dashboard ejecutivo ampliado (costo por unidad, costo por km, tendencia mensual).
- Auditoria avanzada (antes/despues por campo critico).
- Reglas de consistencia automatica y tareas de integridad.
- Exportacion para BI (Power BI/Metabase).

### KPI fase
- Costo por unidad visible y trazable para 100% de flota activa.
- 100% de cambios sensibles con historial de auditoria.
- Reduccion de incidentes operativos por falta de datos >= 30%.

## Riesgos y mitigacion
- Riesgo: sobrecarga funcional en primera etapa.
- Mitigacion: limitar MVP movil a 2 o 3 casos de uso por rol.

- Riesgo: permisos inconsistentes entre web y app.
- Mitigacion: unificar matriz en backend y mapear rutas a permisos existentes.

- Riesgo: datos historicos incompletos para KPI.
- Mitigacion: backfill incremental y etiquetas de calidad de dato.

## Dependencias
- Permisos centralizados en App\Support\SystemPermissions.
- API Laravel autenticada por token.
- Reglas de negocio consolidadas en backend (no solo UI).
