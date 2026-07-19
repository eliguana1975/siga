# Manual de Usuario SIGA

Este manual esta pensado para usuarios operativos y administrativos.
El objetivo es explicar que hacer en cada modulo, en palabras simples y paso a paso.

## 1) Ingreso al sistema

1. Abrir SIGA en el navegador.
2. Ingresar usuario y contrasena.
3. Hacer clic en Ingresar.
4. Si no puede entrar, pedir a un administrador que revise usuario, clave y permisos.

## 2) Que muestra el Dashboard

El Dashboard es el panel principal. Muestra alertas y resumen de estado.

Vas a ver principalmente:
- Internos para realizar servicio (km/hs).
- Vencimientos proximos de verificaciones.
- Graficos de estado de flota y ordenes de trabajo.

Graficos del Dashboard:
- Estado de flota: muestra cuantas unidades estan activas y cuantas estan de baja.
- Estado de ordenes de trabajo: muestra cantidad por estado (pendiente, en proceso, completada, cancelada).
- Vehiculos parados por motivo: agrupa por causa principal (repuestos, terceros, taller, compras, autorizacion u otro).
- Vehiculos parados por servicio asignado: muestra que tipo de servicio concentra mas unidades detenidas.
- Distribucion por tipo de vehiculo: muestra cuantos vehiculos hay por cada tipo cargado en el sistema.

Como usar los graficos:
1. Cada grafico permite elegir vista Barras, Dona o Torta.
2. La eleccion se guarda por usuario para los proximos ingresos.
3. Si un grafico no tiene datos, revisar primero que los registros esten cargados en su modulo origen.

Uso recomendado diario:
1. Revisar primero alertas en rojo o proximas.
2. Entrar al modulo correspondiente desde Ver control completo.
3. Registrar acciones para que el Dashboard se actualice.

Casos practicos (si pasa esto, hace esto):
- Si ves muchos vehiculos parados por el mismo motivo: entrar a OT y priorizar ese cuello de botella primero.
- Si un grafico aparece vacio: verificar que existan registros en el modulo origen y que tengan estado vigente.
- Si hay alertas vencidas repetidas: revisar que se este registrando cierre de OT y servicio realizado.

## 3) Modulo Flota

Sirve para alta, edicion y control general de unidades.

Tareas comunes:
1. Ir a Flota.
2. Buscar interno o dominio.
3. Crear o editar datos de la unidad.
4. Definir medidor de servicio:
- km para vehiculos comunes.
- horas para maquinaria.
5. Guardar cambios.

Funcionalidades clave del modulo:
- Alta de unidad con datos tecnicos y administrativos.
- Edicion de unidad (estado, caracteristicas, observaciones, medidor).
- Asignacion de servicio principal de la unidad.
- Gestion de repuestos asociados por unidad.
- Historial de articulos aplicados a cada unidad.
- Gestion de cubiertas y movimiento de cubiertas.
- Acceso directo a servicios por km/hs y verificaciones tecnicas.

Menus de Flota (que hace cada uno):
- Flota: alta, edicion y consulta general de unidades.
- Servicios por km / hs: control de mantenimiento preventivo por medidor.
- Verificaciones tecnicas: control de vencimientos y comprobantes tecnicos.
- Historial de articulos: consulta de insumos/repuestos aplicados por unidad.
- Gestion de cubiertas: administracion de cubiertas por unidad/eje.
- Movimiento de cubiertas: registro de rotaciones, cambios y reemplazos.

Campos recomendados para completar siempre:
- Interno, dominio, marca y tipo de vehiculo.
- Tipo de medidor de servicio (km u horas).
- Horometro actual si corresponde.
- Tipo/modelo de caja cuando aplique.
- Observaciones operativas utiles para taller.

Acciones operativas sugeridas:
1. Revisar estado de unidad antes de crear una OT.
2. Confirmar que el medidor configurado sea correcto (km u horas).
3. Verificar si tiene servicios o verificaciones proximas.
4. Actualizar datos tecnicos cuando hay cambios reales en la unidad.
5. Registrar repuestos o cubiertas para mantener trazabilidad.

Cuando usar cada opcion relacionada:
- Servicio asignado: cuando cambia el tipo de servicio base de la unidad.
- Repuestos por unidad: cuando se instala, reemplaza o quita un componente relevante.
- Historial de articulos: para auditar que insumos se usaron por unidad.
- Gestion/movimiento de cubiertas: para rotaciones, cambios y seguimiento por posicion.

Consulta de historial de articulos por unidad:
1. Entrar a Flota > Historial de articulos.
2. Filtrar por interno, dominio o rango de fechas.
3. Revisar que articulo se aplico, cantidad, fecha y OT asociada.
4. Usar esta vista para trazabilidad tecnica y auditoria de consumo.

Resultado esperado en este modulo:
- Unidad correctamente configurada para que OT, servicios y dashboard reflejen datos reales.

Buenas practicas:
- Completar marca, tipo de vehiculo y observaciones.
- Mantener actualizado el horometro si la unidad usa horas.

## 4) Servicios por kilometraje / horas

Este modulo controla mantenimiento preventivo por intervalo.

Como usarlo:
1. Entrar en Servicios por km / hs.
2. Filtrar por unidad o por tipo de medidor.
3. Revisar estado de cada servicio (vencido, proximo, ok).
4. Cuando se realiza un servicio, registrar lectura actual.
5. Guardar para reiniciar el indicador de ese servicio.

Importante:
- Si la flota esta en horas, registrar horas.
- Si la flota esta en km, registrar kilometraje.
- El sistema valida que unidad e intervalo coincidan.

Casos practicos (si pasa esto, hace esto):
- Si un interno no aparece en la lista: revisar medidor de la flota, estado de la unidad y filtros aplicados.
- Si no te deja registrar servicio: validar que la unidad del intervalo coincida con el medidor de la flota.
- Si el servicio queda vencido despues de registrar: confirmar que cargaste la lectura actual correcta (km/hs).

## 5) Verificaciones tecnicas

Controla vencimientos de documentacion/verificaciones de flota.

Pasos:
1. Ir a Verificaciones tecnicas.
2. Registrar tipo de verificacion, fecha y comprobante.
3. Revisar periodicamente los vencimientos proximos.

Casos practicos (si pasa esto, hace esto):
- Si una verificacion vence pronto: programar turno y dejar comprobante cargado apenas se realice.
- Si no aparece en vencimientos proximos: controlar fecha de vencimiento, estado del registro y estado de la unidad.
- Si tenes varias verificaciones de una misma unidad: usar observaciones para diferenciar tipo y alcance.

## 6) Ordenes de trabajo (OT)

Se usan para gestionar tareas de taller por unidad.

Flujo sugerido:
1. Crear OT para una unidad.
2. Cargar diagnostico y observaciones.
3. Marcar si la unidad esta parada.
4. Agregar articulos utilizados.
5. Cambiar estado de la OT hasta completada.
6. Registrar servicio km/hs si corresponde.

Novedades en carga de articulos OT:
- Se puede escanear codigo de barras en el campo Codigo / barras para autoseleccionar el articulo.
- Si un codigo se repite, el sistema desambigua por categoria para elegir el articulo correcto para OT.
- En OT no se pueden cargar prendas ni EPP. Esos articulos se gestionan solo en Entrega de ropa y EPP.
- Desde OT, jefe de taller/supervisor puede registrar faltantes y luego crear un pedido especifico de articulos.

Casos practicos (si pasa esto, hace esto):
- Si la unidad no puede salir: marcar vehiculo parado y cargar motivo real.
- Si faltan repuestos: dejar la OT en seguimiento y disparar pedido de articulos.
- Si la OT se resolvio: cerrar OT y registrar servicio para actualizar dashboard e indicadores.

## 7) Inventario, entradas y herramientas

### Inventario
1. Consultar stock por articulo y deposito.
2. Revisar listados de bajo stock o sin stock.
3. Ajustar/actualizar datos cuando sea necesario.

Casos practicos inventario:
- Si un articulo queda en minimo: generar pedido antes de que pase a sin stock.
- Si hay diferencia fisica vs sistema: revisar movimientos recientes y luego ajustar con respaldo.
- Si no encontras un articulo: buscar por nombre, codigo o deposito alternativo.

### Entradas
1. Registrar ingreso de mercaderia.
2. Asociar proveedor y deposito.
3. Confirmar cantidades recibidas.

Casos practicos entradas:
- Si llega parcial una compra: registrar solo lo recibido y dejar pendiente el resto.
- Si la cantidad no coincide con remito: cargar observacion en el ingreso.
- Si no impacta en stock: verificar deposito seleccionado y detalle de items.

### Transferencias entre depositos
1. Ir a Inventario > Transferencias.
2. Crear transferencia indicando deposito origen y destino.
3. Agregar articulos y cantidades a mover.
4. Confirmar para descontar del origen e ingresar en destino.

Casos practicos transferencias:
- Si no aparece un articulo para transferir: revisar stock disponible en deposito origen.
- Si la transferencia no impacta: verificar que se guardo en estado confirmado/finalizado.
- Si hubo error de deposito destino: corregir con una transferencia inversa para mantener trazabilidad.

### Entrega de herramientas
1. Crear entrega al empleado.
2. Registrar detalle de herramientas.
3. Cuando regresan, marcar devolucion.

Casos practicos herramientas:
- Si una herramienta no vuelve: dejar estado pendiente y observacion de seguimiento.
- Si se devuelve en mal estado: registrar novedad para reposicion o reparacion.
- Si se entrego por error: corregir el detalle inmediatamente para no afectar stock.

### Entrega de ropa y EPP
1. Crear entrega al empleado desde el modulo Entrega de ropa y EPP.
2. Cargar deposito, fecha y detalle de prendas/EPP.
3. Se puede usar lector de codigo de barras para seleccionar mas rapido.
4. Confirmar cantidades y guardar la entrega.
5. Cuando corresponda, registrar devolucion desde el detalle.

Reglas clave de este modulo:
- Solo permite articulos marcados como ropa/EPP.
- Si un codigo de barras se repite, el sistema usa categoria para filtrar y elegir la opcion correcta.
- Los articulos de OT no deben salir por este modulo.

Casos practicos ropa/EPP:
- Si un codigo escaneado no carga: verificar que el articulo este marcado como ropa/EPP y tenga stock en el deposito.
- Si hay codigos repetidos: confirmar categoria del articulo para evitar seleccionar un item incorrecto.
- Si la entrega fue mal cargada: editar la entrega (si no tiene devoluciones) o registrar devolucion y volver a cargar.

### Check List vehicular
1. Crear check list para la unidad.
2. Completar estado general y observaciones operativas.
3. Si detectas falla critica, generar o vincular OT desde el control.
4. Guardar para dejar trazabilidad del estado de la unidad.

Casos practicos check list:
- Si una unidad repite fallas: revisar historial de controles y OT vinculadas.
- Si no permite cerrar el control: completar campos obligatorios del formulario.
- Si la novedad deja unidad fuera de servicio: marcarlo y avisar a jefe de taller.

## 8) Pedidos, compras y pagos

### Pedido de articulos
1. Crear pedido.
2. Cargar items faltantes.
3. Confirmar y guardar.

Pedido especifico (jefe de taller / supervisor):
1. Ingresar a Pedidos de articulos.
2. Crear pedido nuevo con los articulos puntuales faltantes para una OT o servicio.
3. Definir cantidades reales y prioridad en observaciones.
4. Guardar y dar seguimiento hasta compra/entrada.

Importante:
- Este pedido especifico se usa para faltantes concretos (no para reposicion general).
- Es recomendable referenciar interno u OT en observaciones para trazabilidad.

Casos practicos pedidos:
- Si el sistema bloquea por stock: revisar regla y pedir autorizacion si corresponde.
- Si hay urgencia operativa: priorizar items criticos en observaciones del pedido.
- Si se cargo un item incorrecto: editar o eliminar antes de confirmar.

### Solicitudes de repuestos no catalogados
1. Crear solicitud cuando el repuesto no existe en catalogo.
2. Cargar descripcion tecnica, urgencia y contexto operativo (interno/OT).
3. En revision, se puede aprobar, rechazar o asociar/crear articulo del catalogo.
4. Si se aprueba, generar pedido para continuar el circuito de compra.

Casos practicos solicitudes no catalogadas:
- Si una solicitud queda trabada: revisar estado y observaciones del aprobador.
- Si el repuesto ya existe: asociar articulo para evitar duplicados.
- Si es urgente: dejar justificacion clara para priorizacion.

### Orden de compra
1. Crear orden desde pedido o manualmente.
2. Cargar proveedor, items y condiciones.
3. Enviar al proveedor.

Casos practicos orden de compra:
- Si cambia el precio despues de emitir: actualizar detalle y dejar trazabilidad del cambio.
- Si proveedor rechaza: cambiar proveedor o ajustar condiciones y reenviar.
- Si la orden no avanza: verificar estado y contacto con proveedor.

### Pendientes de entrega
1. Ingresar a Pendientes de entrega desde Entradas.
2. Revisar ordenes/items con recepcion incompleta.
3. Registrar nuevas recepciones parciales o cierre total cuando llega el faltante.

Casos practicos pendientes:
- Si un pendiente permanece mucho tiempo: contactar proveedor y actualizar seguimiento.
- Si se recibio todo pero sigue pendiente: revisar cantidades recibidas vs ordenadas.
- Si llega mercaderia sin referencia clara: validar OC antes de imputar recepcion.

### Pagos
1. Registrar pago de la orden.
2. Adjuntar/actualizar comprobante.
3. Verificar saldo y estado final.

Casos practicos pagos:
- Si hay pago parcial: registrar monto real y controlar saldo pendiente.
- Si falta comprobante: dejar pago registrado y cargar comprobante apenas se reciba.
- Si el saldo no cierra: revisar historial de pagos vinculados a esa orden.

## 9) Empleados y cronogramas

### Empleados
1. Crear o editar ficha del empleado.
2. Completar datos personales y laborales.
3. Guardar cambios.

Casos practicos empleados:
- Si un dato obligatorio falta: completar primero campos base y luego ampliar la ficha.
- Si cambia condicion laboral: actualizar estado y fecha de vigencia del cambio.
- Si vence documentacion: registrar observacion y avisar al responsable.

### Cronogramas laborales
1. Crear patrones de turnos.
2. Asignar patron a empleados.
3. Registrar novedades (ausencias, cambios, etc.).

Casos practicos cronogramas:
- Si hay ausencia imprevista: cargar novedad en el dia para mantener planificacion real.
- Si cambia el turno por semanas: actualizar asignacion con fechas claras.
- Si un empleado figura doble: revisar superposicion de asignaciones.

## 10) Configuracion y seguridad

### Catalogos y parametros
- Intervalos de servicio (permite configurar por km y por horas).
- Vencimientos de verificaciones.
- Tipos, marcas y demas catalogos.

Menus de catalogos de flota (que hace cada uno):
- Intervalos de servicios: define cada servicio preventivo, su intervalo y la unidad (km u horas).
- Tipos de verificaciones: define que verificaciones tecnicas se controlan y su regla de vencimiento.
- Titulares: administra los titulares o responsables legales de las unidades.
- Marca vehiculo: administra las marcas base de las unidades (ej.: Ford, Iveco, etc.).
- Cia. seguro: administra las companias de seguro usadas en la flota.
- Tipo vehiculo: administra los tipos de unidad (ej.: camion, utilitario, motoniveladora, etc.).
- Marca carroceria: administra marcas de carroceria para clasificacion tecnica.
- Tipo motor: administra la clasificacion del motor (diesel, naftero, etc., segun catalogo interno).
- Modelo motor: administra modelos de motor para detalle tecnico de la unidad.
- Tipo caja: administra el tipo de caja/transmision (manual, automatica, etc.).
- Modelo caja: administra el modelo especifico de caja para precision tecnica.

Cuando usar estos menus:
1. Al dar de alta una unidad nueva en flota y faltan opciones en desplegables.
2. Cuando cambia un proveedor tecnico (seguro, marca, modelo) y hay que actualizar catalogos.
3. Antes de carga masiva, para asegurar datos consistentes y evitar duplicados.

Menus de configuracion general (que hace cada uno):
- Ajustes del sistema: define parametros generales de la empresa y comportamiento global del sistema.
- Bases: administra bases operativas para clasificar personal/unidades segun estructura interna.
- Servicio asignado: administra los tipos de servicio asignables a unidades y reportes de parada.
- Roles: define perfiles de acceso (que puede hacer cada tipo de usuario).
- Usuarios: administra cuentas de acceso, datos de login y asignacion de rol.
- Empleados: administra legajos y datos del personal para operacion y cronogramas.
- Cronogramas laborales: administra patrones, asignaciones y novedades de turnos.
- Provincias: administra catalogo geografico de provincias para datos maestros.
- Ciudades: administra catalogo geografico de ciudades y su vinculacion con provincias.

Cuando usar estos menus:
1. Al iniciar implementacion o cambio de estructura organizativa.
2. Cuando se incorpora personal nuevo o cambia su rol/perfil de acceso.
3. Cuando se necesita mantener consistencia de ubicaciones geograficas en formularios.

Menus de operacion diaria (que hace cada uno):
- Inicio: abre el Dashboard principal con alertas e indicadores generales.
- Bitacora: muestra historial de acciones realizadas por los usuarios para auditoria.
- Chat interno: permite comunicacion operativa rapida entre sectores.
- Depositos: administra los depositos donde se guarda stock.
- Categorias: organiza los articulos por rubro para busqueda y reportes.
- Proveedores: administra proveedores de compra y sus datos de contacto.
- Articulos: administra el catalogo maestro de insumos/repuestos.
- Inventarios: muestra y controla stock por articulo y deposito.
- Transferencias: mueve stock entre depositos con trazabilidad.
- Entradas: registra ingresos de mercaderia y actualiza stock.
- Entregas de herramientas: controla prestamos y devoluciones por empleado.
- Entrega ropa y EPP: controla entrega y devolucion de indumentaria y elementos de proteccion personal.
- Check List Vehicular: registra controles operativos de cada unidad antes/durante uso.
- Ordenes de trabajo: gestiona tareas de taller desde apertura hasta cierre.
- Historial vehiculos: consulta historica de articulos y movimientos vinculados a cada unidad.
- Pedidos de articulos: registra necesidades internas de abastecimiento.
- Ordenes de compra: emite y administra compras a proveedores.
- Compras: muestra compras consolidadas y su estado administrativo.
- Pendientes de entrega: muestra ordenes/items pendientes de recepcion en entradas.
- Ingresos de articulos: registra recepcion de mercaderia e impacto de stock.
- Listados: muestra reportes/listados de articulos para consulta e impresion.
- Unid de medida: administra unidades de medida usadas en articulos y stock.

Menus de catalogos tecnicos (que hace cada uno):
- Tipo vehiculo: define tipos de unidad para clasificacion de flota.
- Marca vehiculo: define marcas de unidad para datos maestros.
- Marca carroceria: define marcas de carroceria para detalle tecnico.
- Tipo motor: define tipos de motor para clasificacion.
- Modelo motor: define modelos de motor para mayor precision.
- Tipo caja: define tipos de transmision (manual, automatica, etc.).
- Modelo caja: define modelos especificos de caja.
- Cia. seguro: define companias de seguro disponibles.
- Titulares: define titulares/responsables de unidades.

Menus de personas y organizacion (que hace cada uno):
- Empleados: administra legajos y datos de personal.
- Cronogramas laborales: organiza turnos, patrones y novedades.
- Provincias: administra catalogo de provincias.
- Ciudades: administra catalogo de ciudades.

Cuando usar estos menus:
1. En la operacion diaria para registrar movimientos reales (taller, stock, compras).
2. Al detectar que falta una opcion en un formulario (crear/ajustar catalogo).
3. Para mantener datos maestros limpios y evitar duplicados.

### Usuarios, roles y permisos
1. Crear usuario.
2. Asignar rol.
3. Verificar permisos segun tarea real.

Casos practicos configuracion y seguridad:
- Si un usuario no ve un modulo: revisar rol y permisos asignados.
- Si puede hacer mas de lo debido: ajustar permisos al minimo necesario.
- Si aparece error de acceso: validar sesion activa y permisos del perfil.

## 11) Recomendaciones por perfil

### Jefe de taller
- Revisar Dashboard al inicio y cierre del dia.
- Priorizar OT con unidad parada.
- Si hay faltantes criticos, generar pedido especifico de articulos con referencia de OT/interno.

### Panol / deposito
- Confirmar cada salida y devolucion.
- Revisar bajo stock todos los dias.

### Compras
- Consolidar pedidos pendientes.
- Mantener comprobantes de pagos actualizados.

### RRHH
- Mantener fichas de empleado completas.
- Revisar cronogramas y novedades semanalmente.

### Administrador
- Auditar permisos y bitacora en forma periodica.
- Mantener catalogos consistentes para evitar errores operativos.

## 12) Problemas frecuentes y solucion rapida

No veo un menu:
- Su usuario no tiene permiso para ese modulo.

No puedo guardar un registro:
- Revise campos obligatorios y formato de datos.

El servicio no se reinicia:
- Verifique que registro lectura correcta (km/hs) segun la unidad.

No aparecen datos en Dashboard:
- Confirmar que la accion se guardo en el modulo origen (OT, servicio, verificacion).

## 13) Checklist diario sugerido

1. Revisar Dashboard.
2. Resolver alertas vencidas o proximas.
3. Registrar OT/servicios realizados.
4. Controlar stock critico.
5. Cerrar pendientes administrativos del dia.

## 14) Checklist semanal sugerido

1. Revisar OT abiertas y depurar las que no tienen avance.
2. Verificar servicios proximos de la proxima semana.
3. Controlar pendientes de entrada de compras parciales.
4. Auditar herramientas entregadas sin devolucion.
5. Revisar empleados con documentacion por vencer.

## 15) Checklist mensual sugerido

1. Revisar calidad de datos de flota (medidor, estado, observaciones).
2. Controlar permisos de usuarios por rol real.
3. Revisar articulos con rotacion critica y sin stock recurrente.
4. Validar que no existan OT antiguas sin cierre administrativo.
5. Confirmar que los catalogos de configuracion esten actualizados.

## 16) Prioridades operativas (orden recomendado)

Prioridad alta:
- Unidad parada por falla critica.
- Servicio vencido de seguridad.
- Verificacion tecnica vencida o por vencer inmediato.
- Stock agotado de insumo critico.

Prioridad media:
- Servicios proximos dentro del periodo de control.
- OT en proceso con espera de repuestos.
- Diferencias de inventario con impacto parcial.

Prioridad baja:
- Mejoras de carga descriptiva.
- Ordenamiento de datos historicos.
- Ajustes no urgentes de catalogos.

## 17) Criterios de calidad de carga

Para evitar errores de gestion, confirmar siempre:
- Que la unidad del dato sea correcta (km u horas).
- Que el estado del registro refleje situacion real.
- Que toda accion relevante tenga observacion breve y clara.
- Que proveedor, deposito y fechas queden correctamente vinculados.
- Que el usuario cierre el proceso completo y no solo una parte.

Errores tipicos a evitar:
- Cerrar OT sin registrar servicio cuando corresponde.
- Registrar horas en una unidad configurada en km (o al reves).
- Cargar entradas sin confirmar deposito correcto.
- Dejar pagos sin comprobante por tiempo prolongado.

## 18) Bitacora y trazabilidad

Cuando exista una duda operativa, usar la bitacora para responder:
1. Quien hizo el cambio.
2. Cuando lo hizo.
3. Sobre que modulo o registro.
4. Que accion se ejecuto (alta, edicion, baja, cierre).

Recomendacion:
- Antes de corregir manualmente datos sensibles, revisar primero trazabilidad.

## 19) Comunicacion interna (chat)

Uso recomendado:
- Informar bloqueos operativos que afecten taller, compras o deposito.
- Avisar cierres de tareas criticas para sincronizar equipos.
- Evitar usar chat para datos que deben quedar en observaciones del registro.

## 20) Escalamiento de incidentes

Si no podes resolver un problema en el modulo:
1. Confirmar permisos y datos obligatorios.
2. Reintentar con la secuencia correcta de pasos.
3. Registrar evidencia minima: pantalla, interno/OT/orden y mensaje de error.
4. Escalar al referente del area.
5. Si persiste, escalar a administrador del sistema.

Tiempo orientativo de respuesta:
- Incidente critico (operacion detenida): inmediato.
- Incidente medio (impacto parcial): dentro del dia.
- Incidente bajo (sin impacto operativo directo): planificar correccion.

## 21) Glosario rapido

- OT: orden de trabajo del taller.
- Interno: identificador operativo de la unidad.
- Medidor: tipo de lectura para mantenimiento (km u horas).
- Intervalo: cantidad de km/hs entre servicios.
- Vehiculo parado: unidad fuera de servicio por un motivo registrado.
- Entrada: ingreso de mercaderia al deposito.
- Pedido: solicitud interna de insumos.
- Orden de compra: documento emitido al proveedor.

## 22) Funciones nuevas incorporadas

Esta seccion resume las funciones nuevas agregadas a SIGA y explica para que sirve cada una en la operacion diaria.

### Acceso a SIGA Mobile antes de entrar al sistema

En la pantalla de inicio de sesion ahora aparece un acceso directo llamado SIGA Mobile.

Para que sirve:
- Permite entrar rapidamente a la aplicacion movil sin buscarla dentro del panel administrativo.
- Esta pensada para choferes, mecanicos y supervisores que trabajan desde el celular.
- Evita que el usuario operativo tenga que navegar todos los menus del sistema.

Como usarlo:
1. Abrir SIGA en el navegador del celular.
2. Tocar Abrir aplicacion movil.
3. Ingresar con usuario y contrasena.
4. Usar las opciones disponibles segun el rol/permisos del usuario.

### App movil SIGA Mobile

SIGA Mobile permite registrar tareas operativas desde el celular.

Que permite hacer:
- Crear checklists de unidad.
- Cargar solicitudes de repuestos con descripcion, prioridad, cantidad y foto.
- Consultar reparaciones de articulos.
- Registrar devoluciones de articulos enviados a reparar.
- Ver un resumen rapido de checklists, solicitudes, reparaciones y pendientes.

Uso recomendado:
1. Chofer o mecanico registra el checklist antes o durante la operacion.
2. Si detecta un faltante, carga una solicitud de repuesto con foto.
3. Si recibe articulos reparados, registra la devolucion desde el celular.
4. El supervisor revisa la informacion en el panel administrativo.

Acceso por rol:
- Chofer: entra directo al checklist y solo ve las opciones necesarias para cargar o consultar controles de unidad.
- Mecanico: entra a la pantalla de reparaciones y ve tambien repuestos y checklist si tiene esos permisos asignados.
- Supervisor: ve el resumen operativo y puede consultar los modulos moviles de seguimiento.
- Superusuario: ve todos los modulos moviles, porque conserva acceso completo al sistema.

Checklist desde el celular:
- El formulario usa la misma estructura del check list vehicular del sistema web.
- Cada punto se marca como cumple, no cumple o no aplica, segun corresponda.
- Cuando un checklist se envia con algun punto en no cumple, SIGA registra el control y genera la orden de trabajo asociada para que el taller pueda hacer el seguimiento.
- El checklist enviado desde el celular queda disponible en el listado web de Check List Vehicular.

Pendientes sin conexion:
- Si el celular se queda sin internet, la app guarda checklists, solicitudes y devoluciones como pendientes.
- Cuando vuelve la conexion, intenta enviarlos automaticamente.
- El contador Pendientes muestra cuantos registros todavia no fueron enviados.

Importante:
- Para fotos y camara, conviene probar desde el celular real.
- El usuario debe tener permisos correctos para que la app muestre y permita las acciones esperadas.

### Flujo de aprobacion de solicitudes de repuestos

Las solicitudes de repuestos no catalogados ahora tienen un circuito mas claro.

Estados principales:
- Borrador o pendiente: la solicitud fue cargada y espera revision.
- Aprobada: el responsable autorizo avanzar.
- Rechazada: no se autoriza la solicitud.
- Cerrada o gestionada: ya se resolvio con articulo, pedido o compra.

Que puede hacer el responsable:
- Aprobar una solicitud.
- Rechazarla con observacion.
- Asociarla a un articulo existente para evitar duplicados.
- Crear un articulo nuevo si realmente no existe.
- Generar un pedido de articulos para continuar el proceso de compra.

Para que sirve:
- Evita compras sin autorizacion.
- Ordena pedidos urgentes.
- Deja registro de quien aprobo, rechazo o cambio el estado.

### Acciones masivas

Algunos listados permiten seleccionar varios registros y aplicar una accion de una sola vez.

Donde se usa:
- Solicitudes de repuestos.
- Reparaciones de articulos.

Para que sirve:
- Aprobar o rechazar varias solicitudes.
- Cambiar estado de varias solicitudes.
- Refrescar estado de reparaciones.
- Cancelar reparaciones seleccionadas cuando corresponda.

Uso recomendado:
1. Filtrar el listado.
2. Marcar los registros correctos.
3. Elegir la accion masiva.
4. Revisar antes de confirmar para no afectar registros equivocados.

### Notificaciones operativas

El sistema genera notificaciones para avisar situaciones que necesitan atencion.

Que puede avisar:
- Servicios vencidos o proximos.
- Verificaciones tecnicas por vencer.
- Reparaciones demoradas.
- Stock critico.
- Reposiciones sugeridas.

Como se ven:
- En la campana del panel superior.
- En el menu Notificaciones operativas.

Que hacer con una notificacion:
1. Leer el detalle.
2. Ir al modulo relacionado.
3. Resolver la causa real.
4. Marcarla como leida o resuelta cuando ya no requiera seguimiento.

Envio por WhatsApp/email:
- El sistema deja preparado el resumen para copiar, enviar por email o abrir WhatsApp Web.
- Para envio automatico real se necesitan credenciales de un proveedor externo.

### Alertas operativas del Dashboard

El Dashboard muestra alertas para ayudar a priorizar.

Para que sirve:
- Ver rapido que unidades, servicios, verificaciones o stock requieren atencion.
- Priorizar lo urgente antes de revisar listados completos.
- Reducir olvidos de vencimientos o demoras.

Uso recomendado:
1. Entrar al Dashboard al inicio del dia.
2. Revisar primero alertas rojas o vencidas.
3. Abrir el modulo de origen.
4. Registrar la accion correctiva.

### Costeo real por vehiculo

El modulo de costeo por vehiculo muestra cuanto cuesta operar o mantener cada unidad.

Que suma:
- Repuestos usados.
- Mano de obra si esta cargada.
- Cubiertas.
- Servicios.
- Reparaciones.
- Otros costos disponibles en el sistema.

Para que sirve:
- Saber que unidades son mas caras.
- Comparar costo por unidad.
- Revisar costo mensual.
- Detectar vehiculos con mantenimiento repetitivo.

Uso recomendado:
1. Entrar a Flota > Costeo de vehiculos.
2. Filtrar por periodo.
3. Revisar unidades con mayor costo.
4. Entrar al detalle o historial para entender la causa.

### Documentos adjuntos operativos

Ahora se pueden adjuntar archivos a registros importantes.

Donde se pueden usar:
- Compras.
- Reparaciones.
- Ordenes de trabajo.
- Solicitudes de repuestos.
- Entradas.
- Entregas de herramientas.
- Entregas de ropa y EPP.

Que archivos cargar:
- PDF.
- Fotos.
- Remitos.
- Comprobantes.
- Presupuestos.
- Evidencia tecnica.

Para que sirve:
- Tener respaldo del registro.
- Evitar buscar papeles o archivos fuera del sistema.
- Facilitar auditorias y consultas futuras.

Buenas practicas:
- Subir documentos con nombre claro.
- Adjuntar comprobantes apenas se reciben.
- No cargar archivos duplicados si ya existe uno correcto.

### Auditoria y bitacora

La bitacora permite revisar acciones realizadas por usuarios.

Que muestra:
- Usuario que hizo la accion.
- Fecha y hora.
- Modulo afectado.
- Tipo de accion.
- Cambios importantes antes/despues cuando estan disponibles.

Nuevas mejoras:
- Filtros por modulo, usuario, accion y fecha.
- Resumen de actividad.
- Exportacion para revision externa.

Para que sirve:
- Saber quien cambio un dato sensible.
- Revisar errores de carga.
- Controlar aprobaciones, cierres y cambios de estado.
- Responder consultas de auditoria interna.

### Auditoria operativa e integridad de datos

La auditoria operativa busca problemas de consistencia en la informacion.

Que puede detectar:
- Stock negativo.
- Duplicados criticos.
- Reparaciones cerradas con pendientes.
- OT abiertas antiguas.
- OT sin articulos cuando deberian tenerlos.
- Pedidos o detalles duplicados.
- Devoluciones inconsistentes.

Como usarla:
1. Entrar a Auditoria operativa.
2. Revisar las observaciones.
3. Corregir el registro indicado.
4. Volver a ejecutar o revisar para confirmar que el problema desaparecio.

### Filtros guardados por usuario

Algunos listados permiten guardar filtros frecuentes.

Para que sirve:
- No tener que repetir la misma busqueda todos los dias.
- Tener accesos rapidos a vistas de trabajo.
- Cada usuario puede guardar sus propios filtros.

Ejemplos:
- Solicitudes pendientes urgentes.
- Reparaciones demoradas.
- Entradas de un proveedor.
- Bitacora de un modulo especifico.

### Busqueda global

La busqueda global permite encontrar registros desde un solo lugar.

Para que sirve:
- Buscar por interno, dominio, articulo, proveedor, solicitud, OT u otros datos.
- Llegar mas rapido al registro sin recordar el menu exacto.
- Reducir tiempo de navegacion.

Uso recomendado:
1. Escribir una palabra, numero de interno, dominio o codigo.
2. Revisar sugerencias.
3. Entrar al resultado correcto.

### BI y datasets para analisis

SIGA incluye salidas de datos para conectar herramientas de analisis.

Que datos entrega:
- Costeo de vehiculos.
- Stock critico.
- Solicitudes de repuestos.
- Reparaciones vencidas o demoradas.
- Reposicion sugerida.

Para que sirve:
- Conectar Power BI, Metabase u otra herramienta.
- Crear reportes por base, proveedor, categoria o periodo.
- Analizar tendencias de consumo y reposicion.

Importante:
- El conector queda disponible desde SIGA.
- La herramienta externa se configura aparte segun la decision de la empresa.

### Reposicion sugerida

El sistema puede sugerir reposicion tomando consumo e inventario.

Para que sirve:
- Anticipar compras antes de quedar sin stock.
- Comparar consumo reciente contra minimos y maximos.
- Ayudar a deposito/compras a priorizar.

Como interpretarlo:
- Si un articulo aparece como sugerido, revisar stock actual, consumo y criticidad.
- La sugerencia ayuda a decidir, pero la compra debe validarse con la necesidad real.

### Automatizaciones programadas

SIGA ya tiene tareas programadas en el sistema.

Que hacen:
- Generar o actualizar notificaciones operativas.
- Revisar alertas operativas.
- Ejecutar auditoria de consistencia.
- Limpiar mensajes antiguos del chat.

Importante:
- Para que corran solas en el servidor, debe estar activo el programador de tareas de Windows ejecutando Laravel Scheduler.
- Si el servidor no ejecuta el scheduler, las funciones manuales siguen disponibles, pero las automatizaciones no se actualizan solas.

## 23) Cierre del manual

Si cada area carga datos en tiempo y forma, SIGA permite:
- Anticipar problemas operativos.
- Reducir paradas no planificadas.
- Mejorar trazabilidad de compras, stock y taller.
- Tomar decisiones con datos confiables en Dashboard.
