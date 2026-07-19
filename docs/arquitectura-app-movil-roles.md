# Arquitectura app movil por roles - SIGA

## Principios
- Seguridad en backend, no en cliente.
- Menus dinamicos por permisos efectivos.
- Flujos simples y rapidos para uso en campo.
- Soporte offline basico para formularios cortos.

## Roles propuestos
- admin
- supervisor
- operador
- consulta

## Capacidades por rol (MVP)
- admin: todo.
- supervisor: aprobaciones, reportes, seguimiento de pendientes.
- operador: carga checklist, solicitudes de repuesto, devoluciones.
- consulta: solo lectura de estado y reportes asignados.

## Componentes tecnicos
- Frontend movil: Kotlin + Jetpack Compose.
- Auth: token via Laravel Sanctum.
- API: endpoints REST versionados (/api/v1).
- Cache local: Room (catalogos, formularios pendientes).
- Sync: cola simple de envios pendientes con reintento.

## Endpoints base recomendados
- POST /api/v1/auth/login
- POST /api/v1/auth/logout
- GET /api/v1/me
- GET /api/v1/me/permisos

- GET /api/v1/checklists
- POST /api/v1/checklists
- GET /api/v1/reparaciones/pendientes
- POST /api/v1/reparaciones/{id}/devoluciones
- GET /api/v1/solicitudes-repuestos
- POST /api/v1/solicitudes-repuestos

## Seguridad
- Middleware auth:sanctum en toda ruta API privada.
- Verificacion de permisos por accion (mapa route -> permission).
- Rotacion de token y cierre de sesion invalida.
- Auditoria de actor, dispositivo y origen.

## UX minima recomendada
- Login.
- Home por rol con cards.
- Lista + filtros.
- Formulario de carga rapida.
- Confirmacion y estado de sincronizacion.

## Estrategia de lanzamiento
- Piloto con 10 usuarios (operador + supervisor).
- 2 semanas de feedback en campo.
- Ajustes de UX y performance.
- Escalado por base o area.
