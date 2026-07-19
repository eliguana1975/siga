<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SystemPermissions
{
    public const MOBILE_MENU = [
        'Inicio' => [
            ['label' => 'Inicio', 'permission' => 'dashboard.ver', 'route' => 'admin.index'],
            ['label' => 'BI', 'permission' => 'bi.ver', 'route' => 'admin.bi.index'],
            ['label' => 'Notificaciones', 'permission' => 'notificaciones-operativas.ver', 'route' => 'admin.notificaciones-operativas.index'],
            ['label' => 'Auditoria operativa', 'permission' => 'auditoria-operativa.ver', 'route' => 'admin.auditoria-operativa.index'],
            ['label' => 'Chat interno', 'permission' => 'chat.ver', 'route' => 'admin.chat.index'],
            ['label' => 'Manual de Usuario', 'permission' => null, 'route' => 'admin.manual-usuario.index'],
            ['label' => 'Bitacora', 'permission' => 'bitacoras.ver', 'route' => 'admin.bitacoras.index'],
        ],
        'Ordenes de trabajo' => [
            ['label' => 'Ordenes de Trabajo', 'permission' => 'ordenes-trabajo.ver', 'route' => 'admin.ordenes-trabajo.index'],
            ['label' => 'Crear orden de trabajo', 'permission' => 'ordenes-trabajo.crear', 'route' => 'admin.ordenes-trabajo.index'],
            ['label' => 'Motivos de ordenes', 'permission' => 'ordenes-trabajo-motivos.ver', 'route' => 'admin.ordenes-trabajo-motivos.index'],
            ['label' => 'Gestion cubiertas', 'permission' => 'gestion-cubiertas.ver', 'route' => 'admin.gestion-cubiertas.index'],
            ['label' => 'Movimiento cubiertas', 'permission' => 'movimiento-cubiertas.ver', 'route' => 'admin.movimiento-cubiertas.index'],
            ['label' => 'Check List Vehicular', 'permission' => 'controles-unidad.ver', 'route' => 'admin.controles-unidad.index'],
            ['label' => 'Crear Check List', 'permission' => 'controles-unidad.crear', 'route' => 'admin.controles-unidad.create'],
        ],
        'Flota' => [
            ['label' => 'Flota', 'permission' => 'flota.ver', 'route' => 'admin.flota.index'],
            ['label' => 'Crear flota', 'permission' => 'flota.crear', 'route' => 'admin.flota.create'],
            ['label' => 'Servicios por km / hs', 'permission' => 'servicios-kilometraje.ver', 'route' => 'admin.servicios-kilometraje.index'],
            ['label' => 'Verificaciones tecnicas', 'permission' => 'verificaciones-tecnicas.ver', 'route' => 'admin.verificaciones-tecnicas.index'],
            ['label' => 'Historial vehiculos', 'permission' => 'historial-articulos-vehiculo.ver', 'route' => 'admin.historial-articulos-vehiculo.index'],
            ['label' => 'Costeo vehiculos', 'permission' => 'historial-articulos-vehiculo.ver', 'route' => 'admin.costeo-vehiculos.index'],
        ],
        'Flota > Tablas auxiliares' => [
            ['label' => 'Intervalos de servicios', 'permission' => 'configuracion-intervalos-servicio.ver', 'route' => 'admin.configuracion-intervalos-servicio.index'],
            ['label' => 'Tipos de verificaciones', 'permission' => 'configuracion-vencimientos-verificacion.ver', 'route' => 'admin.configuracion-vencimientos-verificacion.index'],
            ['label' => 'Titulares', 'permission' => 'titulares.ver', 'route' => 'admin.titulares.index'],
            ['label' => 'Marca vehiculo', 'permission' => 'marca-vehiculo.ver', 'route' => 'admin.marca-vehiculo.index'],
            ['label' => 'Cia. seguro', 'permission' => 'cia-seguro.ver', 'route' => 'admin.cia-seguro.index'],
            ['label' => 'Tipo vehiculo', 'permission' => 'tipo-vehiculo.ver', 'route' => 'admin.tipo-vehiculo.index'],
            ['label' => 'Marca carroceria', 'permission' => 'marca-carroceria.ver', 'route' => 'admin.marca-carroceria.index'],
            ['label' => 'Tipo motor', 'permission' => 'tipo-motor.ver', 'route' => 'admin.tipo-motor.index'],
            ['label' => 'Modelo motor', 'permission' => 'modelo-motor.ver', 'route' => 'admin.modelo-motor.index'],
            ['label' => 'Tipo caja', 'permission' => 'tipo-caja.ver', 'route' => 'admin.tipo-caja.index'],
            ['label' => 'Modelo caja', 'permission' => 'modelo-caja.ver', 'route' => 'admin.modelo-caja.index'],
        ],
        'Proveedores' => [
            ['label' => 'Proveedores', 'permission' => 'proveedores.ver', 'route' => 'admin.proveedores.index'],
        ],
        'Compras y pedidos' => [
            ['label' => 'Pedido de articulos', 'permission' => 'pedidos-articulos.ver', 'route' => 'admin.pedidos-articulos.index'],
            ['label' => 'Crear pedido', 'permission' => 'pedidos-articulos.crear', 'route' => 'admin.pedidos-articulos.create'],
            ['label' => 'Solicitudes de repuestos', 'permission' => 'solicitudes-repuestos.ver', 'route' => 'admin.solicitudes-repuestos.index'],
            ['label' => 'Crear solicitud repuesto', 'permission' => 'solicitudes-repuestos.crear', 'route' => 'admin.solicitudes-repuestos.create'],
            ['label' => 'Orden de compra', 'permission' => 'ordenes-compra.ver', 'route' => 'admin.ordenes-compra.index'],
            ['label' => 'Crear orden de compra', 'permission' => 'ordenes-compra.crear', 'route' => 'admin.ordenes-compra.create'],
            ['label' => 'Compras', 'permission' => 'compras.ver', 'route' => 'admin.compras.index'],
            ['label' => 'Pendientes de entrega', 'permission' => 'entradas.ver', 'route' => 'admin.entradas.pendientes'],
            ['label' => 'Ingresos de articulos', 'permission' => 'entradas.ver', 'route' => 'admin.entradas.index'],
            ['label' => 'Crear ingreso', 'permission' => 'entradas.crear', 'route' => 'admin.entradas.create'],
        ],
        'Articulos' => [
            ['label' => 'Articulos', 'permission' => 'articulos.ver', 'route' => 'admin.articulos.index'],
            ['label' => 'Crear articulo', 'permission' => 'articulos.crear', 'route' => 'admin.articulos.create'],
            ['label' => 'Listados', 'permission' => 'articulos.ver', 'route' => 'admin.articulos.listado'],
            ['label' => 'Categorias', 'permission' => 'categorias.ver', 'route' => 'admin.categorias.index'],
            ['label' => 'Unid de medida', 'permission' => 'unidad-medidas.ver', 'route' => 'admin.unidad-medidas.index'],
            ['label' => 'Inventarios', 'permission' => 'inventarios.ver', 'route' => 'admin.inventarios.index'],
            ['label' => 'Transferencias', 'permission' => 'inventario-transferencias.ver', 'route' => 'admin.inventarios.transferencias.index'],
            ['label' => 'Crear transferencia', 'permission' => 'inventario-transferencias.crear', 'route' => 'admin.inventarios.transferencias.create'],
            ['label' => 'Entrega herramientas', 'permission' => 'entregas-herramientas.ver', 'route' => 'admin.entregas-herramientas.index'],
            ['label' => 'Crear entrega herramientas', 'permission' => 'entregas-herramientas.crear', 'route' => 'admin.entregas-herramientas.create'],
            ['label' => 'Entrega ropa y EPP', 'permission' => 'entregas-ropa-epp.ver', 'route' => 'admin.entregas-ropa-epp.index'],
            ['label' => 'Crear entrega ropa y EPP', 'permission' => 'entregas-ropa-epp.crear', 'route' => 'admin.entregas-ropa-epp.create'],
            ['label' => 'Reparaciones de articulos', 'permission' => 'reparaciones-articulos.ver', 'route' => 'admin.reparaciones-articulos.index'],
            ['label' => 'Registrar reparacion', 'permission' => 'reparaciones-articulos.crear', 'route' => 'admin.reparaciones-articulos.create'],
        ],
        'Depositos' => [
            ['label' => 'Depositos', 'permission' => 'depositos.ver', 'route' => 'admin.depositos.index'],
        ],
        'Configuracion > Ajustes' => [
            ['label' => 'Ajustes del sistema', 'permission' => 'ajustes.ver', 'route' => 'admin.ajustes.index'],
            ['label' => 'Dashboards', 'permission' => 'dashboards.administrar', 'route' => 'admin.dashboards.index'],
            ['label' => 'Bases', 'permission' => 'bases.ver', 'route' => 'admin.bases.index'],
            ['label' => 'Servicio asignado', 'permission' => 'servicios-asignados.ver', 'route' => 'admin.servicios-asignados.index'],
            ['label' => 'Roles', 'permission' => 'roles.ver', 'route' => 'admin.roles.index'],
            ['label' => 'Usuarios', 'permission' => 'users.ver', 'route' => 'admin.users.index'],
            ['label' => 'Empleados', 'permission' => 'empleados.ver', 'route' => 'admin.empleados.index'],
            ['label' => 'Cronogramas laborales', 'permission' => 'cronogramas.ver', 'route' => 'admin.cronogramas-laborales.index'],
            ['label' => 'Provincias', 'permission' => 'provincias.ver', 'route' => 'admin.provincias.index'],
            ['label' => 'Ciudades', 'permission' => 'ciudades.ver', 'route' => 'admin.ciudades.index'],
        ],
    ];

    public const DATATABLE_ACTION_MODULES = [
        'Ordenes de trabajo > Ordenes de trabajo' => ['ordenes-trabajo', 'ordenes de trabajo'],
        'Ordenes de trabajo > Motivos de ordenes' => ['ordenes-trabajo-motivos', 'motivos de ordenes de trabajo'],
        'Ordenes de trabajo > Gestion cubiertas' => ['gestion-cubiertas', 'gestion de cubiertas'],
        'Ordenes de trabajo > Movimiento cubiertas' => ['movimiento-cubiertas', 'movimientos de cubiertas'],
        'Ordenes de trabajo > Check List Vehicular' => ['controles-unidad', 'check list vehicular'],
        'Flota > Flota' => ['flota', 'flota'],
        'Flota > Repuestos' => ['flota-repuestos', 'repuestos de flota'],
        'Flota > Servicios por km / hs' => ['servicios-kilometraje', 'servicios por km / hs'],
        'Flota > Verificaciones tecnicas' => ['verificaciones-tecnicas', 'verificaciones tecnicas'],
        'Flota > Historial vehiculos' => ['historial-articulos-vehiculo', 'historial de vehiculos'],
        'Proveedores > Proveedores' => ['proveedores', 'proveedores'],
        'Compras y pedidos > Pedidos de articulos' => ['pedidos-articulos', 'pedidos de articulos'],
        'Compras y pedidos > Ordenes de compra' => ['ordenes-compra', 'ordenes de compra'],
        'Compras y pedidos > Compras' => ['compras', 'compras'],
        'Compras y pedidos > Ingresos y pendientes' => ['entradas', 'ingresos de articulos'],
        'Compras y pedidos > Solicitudes de repuestos' => ['solicitudes-repuestos', 'solicitudes de repuestos'],
        'Articulos > Articulos' => ['articulos', 'articulos'],
        'Articulos > Categorias' => ['categorias', 'categorias'],
        'Articulos > Unidades de medida' => ['unidad-medidas', 'unidades de medida'],
        'Articulos > Inventarios' => ['inventarios', 'inventarios'],
        'Articulos > Transferencias' => ['inventario-transferencias', 'transferencias de inventario'],
        'Articulos > Entrega de herramientas' => ['entregas-herramientas', 'entregas de herramientas'],
        'Articulos > Entrega de ropa y EPP' => ['entregas-ropa-epp', 'entregas de ropa y EPP'],
        'Articulos > Reparaciones de articulos' => ['reparaciones-articulos', 'reparaciones de articulos'],
        'Depositos > Depositos' => ['depositos', 'depositos'],
        'Configuracion > Ajustes' => ['ajustes', 'ajustes del sistema'],
        'Configuracion > Bases' => ['bases', 'bases'],
        'Configuracion > Servicio asignado' => ['servicios-asignados', 'servicios asignados'],
        'Configuracion > Roles' => ['roles', 'roles y permisos'],
        'Configuracion > Usuarios' => ['users', 'usuarios'],
        'Configuracion > Empleados' => ['empleados', 'empleados'],
        'Configuracion > Cronogramas laborales' => ['cronogramas', 'cronogramas laborales'],
        'Configuracion > Provincias' => ['provincias', 'provincias'],
        'Configuracion > Ciudades' => ['ciudades', 'ciudades'],
    ];

    public const PERMISSIONS = [
        'dashboard.ver' => 'Ver inicio',
        'bi.ver' => 'Ver BI',
        'notificaciones-operativas.ver' => 'Ver notificaciones operativas',
        'auditoria-operativa.ver' => 'Ver auditoria operativa',
        'chat.ver' => 'Ver chat interno',
        'chat.crear' => 'Enviar mensajes de chat',

        'bitacoras.ver' => 'Ver bitacora',

        'ordenes-trabajo.ver' => 'Ver ordenes de trabajo',
        'ordenes-trabajo.crear' => 'Crear ordenes de trabajo',
        'ordenes-trabajo.editar' => 'Editar ordenes de trabajo',
        'ordenes-trabajo.eliminar' => 'Eliminar ordenes de trabajo',
        'ordenes-trabajo-articulos.agregar' => 'Agregar articulos a ordenes de trabajo',
        'ordenes-trabajo-articulos.quitar' => 'Quitar articulos de ordenes de trabajo',
        'ordenes-trabajo-motivos.ver' => 'Ver motivos de ordenes de trabajo',
        'ordenes-trabajo-motivos.crear' => 'Crear motivos de ordenes de trabajo',
        'ordenes-trabajo-motivos.editar' => 'Editar motivos de ordenes de trabajo',
        'ordenes-trabajo-motivos.eliminar' => 'Eliminar motivos de ordenes de trabajo',

        'gestion-cubiertas.ver' => 'Ver gestion de cubiertas',
        'movimiento-cubiertas.ver' => 'Ver movimientos de cubiertas',
        'movimiento-cubiertas.crear' => 'Crear movimientos de cubiertas',
        'movimiento-cubiertas.editar' => 'Editar movimientos de cubiertas',

        'controles-unidad.ver' => 'Ver check list vehicular',
        'controles-unidad.crear' => 'Crear check list vehicular',
        'controles-unidad.eliminar' => 'Eliminar check list vehicular',

        'flota.ver' => 'Ver flota',
        'flota.crear' => 'Crear flota',
        'flota.editar' => 'Editar flota',
        'flota.eliminar' => 'Eliminar flota',
        'flota-servicio-asignado.editar' => 'Editar servicio asignado de flota',
        'flota-repuestos.ver' => 'Ver repuestos de flota',
        'flota-repuestos.crear' => 'Crear repuestos de flota',
        'flota-repuestos.editar' => 'Editar repuestos de flota',
        'flota-repuestos.eliminar' => 'Eliminar repuestos de flota',

        'servicios-kilometraje.ver' => 'Ver servicios por kilometraje',
        'servicios-kilometraje.crear' => 'Registrar servicios por kilometraje',
        'verificaciones-tecnicas.ver' => 'Ver verificaciones tecnicas',
        'verificaciones-tecnicas.crear' => 'Registrar verificaciones tecnicas',
        'historial-articulos-vehiculo.ver' => 'Ver historial de articulos por vehiculo',

        'proveedores.ver' => 'Ver proveedores',
        'proveedores.crear' => 'Crear proveedores',
        'proveedores.editar' => 'Editar proveedores',
        'proveedores.eliminar' => 'Eliminar proveedores',

        'pedidos-articulos.ver' => 'Ver pedidos de articulos',
        'pedidos-articulos.crear' => 'Crear pedidos de articulos',
        'pedidos-articulos.editar' => 'Editar pedidos de articulos',
        'pedidos-articulos.eliminar' => 'Eliminar pedidos de articulos',
        'solicitudes-repuestos.ver' => 'Ver solicitudes de repuestos',
        'solicitudes-repuestos.crear' => 'Crear solicitudes de repuestos',
        'solicitudes-repuestos.editar' => 'Editar solicitudes de repuestos',
        'solicitudes-repuestos.aprobar' => 'Aprobar solicitudes de repuestos',
        'solicitudes-repuestos.rechazar' => 'Rechazar solicitudes de repuestos',
        'solicitudes-repuestos.catalogar' => 'Catalogar repuestos solicitados',
        'solicitudes-repuestos.generar-pedido' => 'Generar pedido desde solicitud',
        'solicitudes-repuestos.cerrar' => 'Cerrar solicitudes de repuestos',

        'ordenes-compra.ver' => 'Ver ordenes de compra',
        'ordenes-compra.crear' => 'Crear ordenes de compra',
        'ordenes-compra.editar' => 'Editar ordenes de compra',
        'ordenes-compra.eliminar' => 'Eliminar ordenes de compra',
        'ordenes-compra.enviar-mail' => 'Enviar ordenes de compra por mail',

        'compras.ver' => 'Ver compras aprobadas',
        'compras.eliminar' => 'Eliminar compras aprobadas',

        'entradas.ver' => 'Ver ingresos de articulos',
        'entradas.crear' => 'Crear ingresos de articulos',
        'entradas.editar' => 'Editar ingresos de articulos',
        'entradas.eliminar' => 'Eliminar ingresos de articulos',

        'articulos.ver' => 'Ver articulos',
        'articulos.crear' => 'Crear articulos',
        'articulos.editar' => 'Editar articulos',
        'articulos.eliminar' => 'Eliminar articulos',
        'categorias.ver' => 'Ver categorias',
        'categorias.crear' => 'Crear categorias',
        'categorias.editar' => 'Editar categorias',
        'categorias.eliminar' => 'Eliminar categorias',
        'unidad-medidas.ver' => 'Ver unidades de medida',
        'unidad-medidas.crear' => 'Crear unidades de medida',
        'unidad-medidas.editar' => 'Editar unidades de medida',
        'unidad-medidas.eliminar' => 'Eliminar unidades de medida',
        'inventarios.ver' => 'Ver inventarios',
        'inventarios.crear' => 'Crear inventarios',
        'inventarios.editar' => 'Editar inventarios',
        'inventarios.eliminar' => 'Eliminar inventarios',
        'inventarios.etiquetas' => 'Imprimir etiquetas de inventario',
        'inventario-transferencias.ver' => 'Ver transferencias de inventario',
        'inventario-transferencias.crear' => 'Crear transferencias de inventario',
        'entregas-herramientas.ver' => 'Ver entregas de herramientas',
        'entregas-herramientas.crear' => 'Crear entregas de herramientas',
        'entregas-herramientas.editar' => 'Registrar devoluciones de herramientas',
        'entregas-ropa-epp.ver' => 'Ver entregas de ropa y EPP',
        'entregas-ropa-epp.crear' => 'Crear entregas de ropa y EPP',
        'entregas-ropa-epp.editar' => 'Registrar devoluciones de ropa y EPP',
        'reparaciones-articulos.ver' => 'Ver reparaciones de articulos',
        'reparaciones-articulos.crear' => 'Crear reparaciones de articulos',
        'reparaciones-articulos.editar' => 'Registrar devoluciones en reparaciones de articulos',
        'reparaciones-articulos.imprimir' => 'Imprimir planillas de reparaciones de articulos',
        'reparaciones-articulos.reclamar' => 'Registrar reclamos a proveedor por reparaciones de articulos',
        'categorias.administrar' => 'Administrar categorias',
        'unidad-medidas.administrar' => 'Administrar unidades de medida',
        'inventarios.administrar' => 'Administrar inventarios',

        'depositos.ver' => 'Ver depositos',
        'depositos.crear' => 'Crear depositos',
        'depositos.editar' => 'Editar depositos',
        'depositos.eliminar' => 'Eliminar depositos',
        'depositos.administrar' => 'Administrar depositos',

        'ajustes.ver' => 'Ver ajustes del sistema',
        'ajustes.crear' => 'Crear ajustes del sistema',
        'ajustes.editar' => 'Editar ajustes del sistema',
        'ajustes.backup' => 'Generar backup de base de datos',
        'dashboards.administrar' => 'Administrar dashboards por rol',
        'bases.ver' => 'Ver bases',
        'bases.crear' => 'Crear bases',
        'bases.editar' => 'Editar bases',
        'bases.eliminar' => 'Eliminar bases',
        'servicios-asignados.ver' => 'Ver servicios asignados',
        'servicios-asignados.crear' => 'Crear servicios asignados',
        'servicios-asignados.editar' => 'Editar servicios asignados',
        'servicios-asignados.eliminar' => 'Eliminar servicios asignados',
        'roles.ver' => 'Ver roles y permisos',
        'roles.crear' => 'Crear roles',
        'roles.editar' => 'Editar roles',
        'roles.eliminar' => 'Eliminar roles',
        'users.ver' => 'Ver usuarios',
        'users.crear' => 'Crear usuarios',
        'users.editar' => 'Editar usuarios',
        'users.eliminar' => 'Eliminar usuarios',
        'empleados.ver' => 'Ver empleados',
        'empleados.crear' => 'Crear empleados',
        'empleados.editar' => 'Editar empleados',
        'empleados.eliminar' => 'Eliminar empleados',
        'cronogramas.ver' => 'Ver cronogramas laborales',
        'cronogramas.crear' => 'Crear cronogramas laborales',
        'cronogramas.editar' => 'Editar cronogramas laborales',
        'cronogramas.eliminar' => 'Eliminar cronogramas laborales',
        'provincias.ver' => 'Ver provincias',
        'provincias.crear' => 'Crear provincias',
        'provincias.editar' => 'Editar provincias',
        'provincias.eliminar' => 'Eliminar provincias',
        'ciudades.ver' => 'Ver ciudades',
        'ciudades.crear' => 'Crear ciudades',
        'ciudades.editar' => 'Editar ciudades',
        'ciudades.eliminar' => 'Eliminar ciudades',
        'ajustes.administrar' => 'Administrar ajustes del sistema',
        'bases.administrar' => 'Administrar bases',
        'servicios-asignados.administrar' => 'Administrar servicios asignados',
        'roles.administrar' => 'Administrar roles y permisos',
        'users.administrar' => 'Administrar usuarios',
        'empleados.administrar' => 'Administrar empleados',
        'cronogramas.administrar' => 'Administrar cronogramas laborales',
        'administrar-cronogramas' => 'Acceso legacy a cronogramas laborales',
        'provincias.administrar' => 'Administrar provincias',
        'ciudades.administrar' => 'Administrar ciudades',

        'configuracion-intervalos-servicio.administrar' => 'Administrar intervalos de servicios',
        'configuracion-vencimientos-verificacion.administrar' => 'Administrar tipos de verificaciones',
        'configuracion-intervalos-servicio.ver' => 'Ver intervalos de servicios',
        'configuracion-intervalos-servicio.crear' => 'Crear intervalos de servicios',
        'configuracion-intervalos-servicio.editar' => 'Editar intervalos de servicios',
        'configuracion-intervalos-servicio.eliminar' => 'Eliminar intervalos de servicios',
        'configuracion-vencimientos-verificacion.ver' => 'Ver tipos de verificaciones',
        'configuracion-vencimientos-verificacion.crear' => 'Crear tipos de verificaciones',
        'configuracion-vencimientos-verificacion.editar' => 'Editar tipos de verificaciones',
        'configuracion-vencimientos-verificacion.eliminar' => 'Eliminar tipos de verificaciones',
        'titulares.ver' => 'Ver titulares',
        'titulares.crear' => 'Crear titulares',
        'titulares.editar' => 'Editar titulares',
        'titulares.eliminar' => 'Eliminar titulares',
        'marca-vehiculo.ver' => 'Ver marcas de vehiculo',
        'marca-vehiculo.crear' => 'Crear marcas de vehiculo',
        'marca-vehiculo.editar' => 'Editar marcas de vehiculo',
        'marca-vehiculo.eliminar' => 'Eliminar marcas de vehiculo',
        'cia-seguro.ver' => 'Ver companias de seguro',
        'cia-seguro.crear' => 'Crear companias de seguro',
        'cia-seguro.editar' => 'Editar companias de seguro',
        'cia-seguro.eliminar' => 'Eliminar companias de seguro',
        'tipo-vehiculo.ver' => 'Ver tipos de vehiculo',
        'tipo-vehiculo.crear' => 'Crear tipos de vehiculo',
        'tipo-vehiculo.editar' => 'Editar tipos de vehiculo',
        'tipo-vehiculo.eliminar' => 'Eliminar tipos de vehiculo',
        'marca-carroceria.ver' => 'Ver marcas de carroceria',
        'marca-carroceria.crear' => 'Crear marcas de carroceria',
        'marca-carroceria.editar' => 'Editar marcas de carroceria',
        'marca-carroceria.eliminar' => 'Eliminar marcas de carroceria',
        'tipo-motor.ver' => 'Ver tipos de motor',
        'tipo-motor.crear' => 'Crear tipos de motor',
        'tipo-motor.editar' => 'Editar tipos de motor',
        'tipo-motor.eliminar' => 'Eliminar tipos de motor',
        'modelo-motor.ver' => 'Ver modelos de motor',
        'modelo-motor.crear' => 'Crear modelos de motor',
        'modelo-motor.editar' => 'Editar modelos de motor',
        'modelo-motor.eliminar' => 'Eliminar modelos de motor',
        'tipo-caja.ver' => 'Ver tipos de caja',
        'tipo-caja.crear' => 'Crear tipos de caja',
        'tipo-caja.editar' => 'Editar tipos de caja',
        'tipo-caja.eliminar' => 'Eliminar tipos de caja',
        'modelo-caja.ver' => 'Ver modelos de caja',
        'modelo-caja.crear' => 'Crear modelos de caja',
        'modelo-caja.editar' => 'Editar modelos de caja',
        'modelo-caja.eliminar' => 'Eliminar modelos de caja',
        'administrar-articulos' => 'Acceso legacy a articulos',
        'administrar-bases' => 'Acceso legacy a bases',
        'administrar-bitacoras' => 'Acceso legacy a bitacoras',
        'administrar-categorias' => 'Acceso legacy a categorias',
        'administrar-cia-seguro' => 'Acceso legacy a companias de seguro',
        'administrar-ciudades' => 'Acceso legacy a ciudades',
        'administrar-controles-unidad' => 'Acceso legacy a check list vehicular',
        'administrar-depositos' => 'Acceso legacy a depositos',
        'administrar-empleados' => 'Acceso legacy a empleados',
        'administrar-inventarios' => 'Acceso legacy a inventarios',
        'administrar-marca-carroceria' => 'Acceso legacy a marcas de carroceria',
        'administrar-marca-vehiculo' => 'Acceso legacy a marcas de vehiculo',
        'administrar-modelo-caja' => 'Acceso legacy a modelos de caja',
        'administrar-modelo-motor' => 'Acceso legacy a modelos de motor',
        'administrar-ordenes-trabajo' => 'Acceso legacy a ordenes de trabajo',
        'administrar-proveedores' => 'Acceso legacy a proveedores',
        'administrar-provincias' => 'Acceso legacy a provincias',
        'administrar-roles' => 'Acceso legacy a roles y permisos',
        'administrar-servicios-asignados' => 'Acceso legacy a servicios asignados',
        'administrar-tipo-caja' => 'Acceso legacy a tipos de caja',
        'administrar-tipo-motor' => 'Acceso legacy a tipos de motor',
        'administrar-tipo-vehiculo' => 'Acceso legacy a tipos de vehiculo',
        'administrar-titular' => 'Acceso legacy a titulares',
        'administrar-unidad-medidas' => 'Acceso legacy a unidades de medida',
        'administrar-usuarios' => 'Acceso legacy a usuarios',
        'titulares.administrar' => 'Administrar titulares',
        'marca-vehiculo.administrar' => 'Administrar marcas de vehiculo',
        'cia-seguro.administrar' => 'Administrar companias de seguro',
        'tipo-vehiculo.administrar' => 'Administrar tipos de vehiculo',
        'marca-carroceria.administrar' => 'Administrar marcas de carroceria',
        'tipo-motor.administrar' => 'Administrar tipos de motor',
        'modelo-motor.administrar' => 'Administrar modelos de motor',
        'tipo-caja.administrar' => 'Administrar tipos de caja',
        'modelo-caja.administrar' => 'Administrar modelos de caja',
    ];

    public const GROUPS = [
        'Inicio > Dashboard' => ['dashboard.ver'],
        'Inicio > BI' => ['bi.ver'],
        'Inicio > Notificaciones' => ['notificaciones-operativas.ver'],
        'Inicio > Auditoria operativa' => ['auditoria-operativa.ver'],
        'Comunicacion > Chat interno' => ['chat.ver', 'chat.crear'],
        'Auditoria > Bitacora' => ['bitacoras.ver'],
        'Ordenes de trabajo > Ordenes de trabajo' => [
            'ordenes-trabajo.ver',
            'ordenes-trabajo.crear',
            'ordenes-trabajo.editar',
            'ordenes-trabajo.eliminar',
            'ordenes-trabajo-articulos.agregar',
            'ordenes-trabajo-articulos.quitar',
        ],
        'Ordenes de trabajo > Motivos de ordenes' => [
            'ordenes-trabajo-motivos.ver',
            'ordenes-trabajo-motivos.crear',
            'ordenes-trabajo-motivos.editar',
            'ordenes-trabajo-motivos.eliminar',
        ],
        'Ordenes de trabajo > Gestion cubiertas' => [
            'gestion-cubiertas.ver',
        ],
        'Ordenes de trabajo > Movimiento cubiertas' => [
            'movimiento-cubiertas.ver',
            'movimiento-cubiertas.crear',
            'movimiento-cubiertas.editar',
        ],
        'Ordenes de trabajo > Check List Vehicular' => [
            'controles-unidad.ver',
            'controles-unidad.crear',
            'controles-unidad.eliminar',
        ],
        'Flota > Flota' => [
            'flota.ver',
            'flota.crear',
            'flota.editar',
            'flota.eliminar',
            'flota-servicio-asignado.editar',
        ],
        'Flota > Repuestos' => [
            'flota-repuestos.ver',
            'flota-repuestos.crear',
            'flota-repuestos.editar',
            'flota-repuestos.eliminar',
        ],
        'Flota > Servicios por km / hs' => [
            'servicios-kilometraje.ver',
            'servicios-kilometraje.crear',
        ],
        'Flota > Verificaciones tecnicas' => [
            'verificaciones-tecnicas.ver',
            'verificaciones-tecnicas.crear',
        ],
        'Flota > Historial vehiculos' => [
            'historial-articulos-vehiculo.ver',
        ],
        'Flota > Tablas auxiliares > Intervalos de servicios' => [
            'configuracion-intervalos-servicio.ver',
            'configuracion-intervalos-servicio.crear',
            'configuracion-intervalos-servicio.editar',
            'configuracion-intervalos-servicio.eliminar',
        ],
        'Flota > Tablas auxiliares > Tipos de verificaciones' => [
            'configuracion-vencimientos-verificacion.ver',
            'configuracion-vencimientos-verificacion.crear',
            'configuracion-vencimientos-verificacion.editar',
            'configuracion-vencimientos-verificacion.eliminar',
        ],
        'Flota > Tablas auxiliares > Titulares' => [
            'titulares.ver',
            'titulares.crear',
            'titulares.editar',
            'titulares.eliminar',
        ],
        'Flota > Tablas auxiliares > Marcas de vehiculo' => [
            'marca-vehiculo.ver',
            'marca-vehiculo.crear',
            'marca-vehiculo.editar',
            'marca-vehiculo.eliminar',
        ],
        'Flota > Tablas auxiliares > Companias de seguro' => [
            'cia-seguro.ver',
            'cia-seguro.crear',
            'cia-seguro.editar',
            'cia-seguro.eliminar',
        ],
        'Flota > Tablas auxiliares > Tipos de vehiculo' => [
            'tipo-vehiculo.ver',
            'tipo-vehiculo.crear',
            'tipo-vehiculo.editar',
            'tipo-vehiculo.eliminar',
        ],
        'Flota > Tablas auxiliares > Marcas de carroceria' => [
            'marca-carroceria.ver',
            'marca-carroceria.crear',
            'marca-carroceria.editar',
            'marca-carroceria.eliminar',
        ],
        'Flota > Tablas auxiliares > Tipos de motor' => [
            'tipo-motor.ver',
            'tipo-motor.crear',
            'tipo-motor.editar',
            'tipo-motor.eliminar',
        ],
        'Flota > Tablas auxiliares > Modelos de motor' => [
            'modelo-motor.ver',
            'modelo-motor.crear',
            'modelo-motor.editar',
            'modelo-motor.eliminar',
        ],
        'Flota > Tablas auxiliares > Tipos de caja' => [
            'tipo-caja.ver',
            'tipo-caja.crear',
            'tipo-caja.editar',
            'tipo-caja.eliminar',
        ],
        'Flota > Tablas auxiliares > Modelos de caja' => [
            'modelo-caja.ver',
            'modelo-caja.crear',
            'modelo-caja.editar',
            'modelo-caja.eliminar',
        ],
        'Proveedores > Proveedores' => [
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'proveedores.eliminar',
        ],
        'Compras y pedidos > Pedidos de articulos' => [
            'pedidos-articulos.ver',
            'pedidos-articulos.crear',
            'pedidos-articulos.editar',
            'pedidos-articulos.eliminar',
        ],
        'Compras y pedidos > Solicitudes de repuestos' => [
            'solicitudes-repuestos.ver',
            'solicitudes-repuestos.crear',
            'solicitudes-repuestos.editar',
            'solicitudes-repuestos.aprobar',
            'solicitudes-repuestos.rechazar',
            'solicitudes-repuestos.catalogar',
            'solicitudes-repuestos.generar-pedido',
            'solicitudes-repuestos.cerrar',
        ],
        'Compras y pedidos > Ordenes de compra' => [
            'ordenes-compra.ver',
            'ordenes-compra.crear',
            'ordenes-compra.editar',
            'ordenes-compra.eliminar',
            'ordenes-compra.enviar-mail',
        ],
        'Compras y pedidos > Compras' => [
            'compras.ver',
            'compras.eliminar',
        ],
        'Compras y pedidos > Ingresos y pendientes' => [
            'entradas.ver',
            'entradas.crear',
            'entradas.editar',
            'entradas.eliminar',
        ],
        'Articulos > Articulos' => [
            'articulos.ver',
            'articulos.crear',
            'articulos.editar',
            'articulos.eliminar',
        ],
        'Articulos > Categorias' => ['categorias.ver', 'categorias.crear', 'categorias.editar', 'categorias.eliminar'],
        'Articulos > Unidades de medida' => ['unidad-medidas.ver', 'unidad-medidas.crear', 'unidad-medidas.editar', 'unidad-medidas.eliminar'],
        'Articulos > Inventarios' => ['inventarios.ver', 'inventarios.crear', 'inventarios.editar', 'inventarios.eliminar', 'inventarios.etiquetas'],
        'Articulos > Transferencias' => ['inventario-transferencias.ver', 'inventario-transferencias.crear'],
        'Articulos > Entrega de herramientas' => ['entregas-herramientas.ver', 'entregas-herramientas.crear', 'entregas-herramientas.editar'],
        'Articulos > Entrega de ropa y EPP' => ['entregas-ropa-epp.ver', 'entregas-ropa-epp.crear', 'entregas-ropa-epp.editar'],
        'Articulos > Reparaciones de articulos' => ['reparaciones-articulos.ver', 'reparaciones-articulos.crear', 'reparaciones-articulos.editar', 'reparaciones-articulos.imprimir', 'reparaciones-articulos.reclamar'],
        'Depositos > Depositos' => ['depositos.ver', 'depositos.crear', 'depositos.editar', 'depositos.eliminar'],
        'Configuracion > Ajustes' => ['ajustes.ver', 'ajustes.crear', 'ajustes.editar', 'ajustes.backup'],
        'Configuracion > Dashboards' => ['dashboards.administrar'],
        'Configuracion > Bases' => ['bases.ver', 'bases.crear', 'bases.editar', 'bases.eliminar'],
        'Configuracion > Servicio asignado' => ['servicios-asignados.ver', 'servicios-asignados.crear', 'servicios-asignados.editar', 'servicios-asignados.eliminar'],
        'Configuracion > Roles' => ['roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar'],
        'Configuracion > Usuarios' => ['users.ver', 'users.crear', 'users.editar', 'users.eliminar'],
        'Configuracion > Empleados' => ['empleados.ver', 'empleados.crear', 'empleados.editar', 'empleados.eliminar'],
        'Configuracion > Cronogramas laborales' => ['cronogramas.ver', 'cronogramas.crear', 'cronogramas.editar', 'cronogramas.eliminar'],
        'Configuracion > Provincias' => ['provincias.ver', 'provincias.crear', 'provincias.editar', 'provincias.eliminar'],
        'Configuracion > Ciudades' => ['ciudades.ver', 'ciudades.crear', 'ciudades.editar', 'ciudades.eliminar'],
        'Compatibilidad > Permisos anteriores > Ordenes de trabajo' => [
            'administrar-controles-unidad',
            'administrar-ordenes-trabajo',
        ],
        'Compatibilidad > Permisos anteriores > Articulos' => [
            'administrar-articulos',
            'administrar-categorias',
            'administrar-inventarios',
            'administrar-unidad-medidas',
            'categorias.administrar',
            'unidad-medidas.administrar',
            'inventarios.administrar',
        ],
        'Compatibilidad > Permisos anteriores > Depositos' => [
            'administrar-depositos',
            'depositos.administrar',
        ],
        'Compatibilidad > Permisos anteriores > Proveedores' => [
            'administrar-proveedores',
        ],
        'Compatibilidad > Permisos anteriores > Configuracion' => [
            'administrar-bases',
            'administrar-ciudades',
            'administrar-empleados',
            'administrar-provincias',
            'administrar-roles',
            'administrar-servicios-asignados',
            'administrar-usuarios',
            'ajustes.administrar',
            'bases.administrar',
            'servicios-asignados.administrar',
            'roles.administrar',
            'users.administrar',
            'empleados.administrar',
            'cronogramas.administrar',
            'administrar-cronogramas',
            'provincias.administrar',
            'ciudades.administrar',
        ],
        'Compatibilidad > Permisos anteriores > Flota auxiliares' => [
            'administrar-cia-seguro',
            'administrar-marca-carroceria',
            'administrar-marca-vehiculo',
            'administrar-modelo-caja',
            'administrar-modelo-motor',
            'administrar-tipo-caja',
            'administrar-tipo-motor',
            'administrar-tipo-vehiculo',
            'administrar-titular',
            'configuracion-intervalos-servicio.administrar',
            'configuracion-vencimientos-verificacion.administrar',
            'titulares.administrar',
            'marca-vehiculo.administrar',
            'cia-seguro.administrar',
            'tipo-vehiculo.administrar',
            'marca-carroceria.administrar',
            'tipo-motor.administrar',
            'modelo-motor.administrar',
            'tipo-caja.administrar',
            'modelo-caja.administrar',
        ],
        'Compatibilidad > Permisos anteriores > Sistema' => [
            'administrar-bitacoras',
        ],
    ];

    public static function permissions(): array
    {
        $permissions = self::PERMISSIONS;

        foreach (self::DATATABLE_ACTION_MODULES as [$module, $label]) {
            $permissions["{$module}.exportar"] = 'Exportar ' . $label;
            $permissions["{$module}.imprimir"] = 'Imprimir ' . $label;
        }

        return $permissions;
    }

    public static function groups(): array
    {
        $groups = self::GROUPS;

        foreach (self::DATATABLE_ACTION_MODULES as $groupName => [$module]) {
            if (! array_key_exists($groupName, $groups)) {
                continue;
            }

            foreach (["{$module}.exportar", "{$module}.imprimir"] as $permission) {
                if (! in_array($permission, $groups[$groupName], true)) {
                    $groups[$groupName][] = $permission;
                }
            }
        }

        return $groups;
    }

    public static function mobileMenu(User $user): array
    {
        $menu = [];

        foreach (self::MOBILE_MENU as $groupName => $items) {
            $allowedItems = collect($items)
                ->filter(function (array $item) use ($user): bool {
                    if (! Route::has($item['route'])) {
                        return false;
                    }

                    if (isset($item['hide_if_can']) && ! $user->isSuperUsuario() && $user->can($item['hide_if_can'])) {
                        return false;
                    }

                    return $item['permission'] === null
                        || $user->isSuperUsuario()
                        || $user->can($item['permission']);
                })
                ->map(fn (array $item): array => [
                    'label' => $item['label'],
                    'permission' => $item['permission'],
                    'url' => route($item['route']),
                ])
                ->values()
                ->all();

            if ($allowedItems !== []) {
                $menu[] = [
                    'group' => $groupName,
                    'items' => $allowedItems,
                ];
            }
        }

        return $menu;
    }

    public static function routePermission(?string $routeName): ?string
    {
        if (! $routeName || ! Str::startsWith($routeName, 'admin.')) {
            return null;
        }

        return match (true) {
            in_array($routeName, ['admin.index', 'home'], true) => null,
            Str::startsWith($routeName, 'admin.saved-filters.') => null,
            Str::startsWith($routeName, 'admin.global-search.') => 'dashboard.ver',
            Str::startsWith($routeName, 'admin.notificaciones-operativas.') => 'notificaciones-operativas.ver',
            Str::startsWith($routeName, 'admin.auditoria-operativa.') => 'auditoria-operativa.ver',
            Str::startsWith($routeName, 'admin.bi.') => 'bi.ver',
            Str::startsWith($routeName, 'admin.chat.') => Str::endsWith($routeName, '.store') ? 'chat.crear' : 'chat.ver',
            Str::startsWith($routeName, 'admin.bitacoras.') => 'bitacoras.ver',

            $routeName === 'admin.ordenes-trabajo.articulos' => 'ordenes-trabajo.ver',
            $routeName === 'admin.ordenes-trabajo.articulos.store' => 'ordenes-trabajo-articulos.agregar',
            $routeName === 'admin.ordenes-trabajo.articulos.kit-servicio' => 'ordenes-trabajo-articulos.agregar',
            $routeName === 'admin.ordenes-trabajo.articulos.destroy' => 'ordenes-trabajo-articulos.quitar',
            $routeName === 'admin.ordenes-trabajo.registrar-servicio-kilometraje' => 'servicios-kilometraje.crear',
            Str::startsWith($routeName, 'admin.ordenes-trabajo-motivos.') => self::crudPermission($routeName, 'ordenes-trabajo-motivos'),
            Str::startsWith($routeName, 'admin.flota.servicio-asignado.') => 'flota-servicio-asignado.editar',
            Str::startsWith($routeName, 'admin.flota.repuestos.') => self::crudPermission($routeName, 'flota-repuestos'),
            Str::startsWith($routeName, 'admin.movimiento-cubiertas.') => self::crudPermission($routeName, 'movimiento-cubiertas'),
            Str::startsWith($routeName, 'admin.servicios-kilometraje.') => Str::endsWith($routeName, '.registrar') ? 'servicios-kilometraje.crear' : 'servicios-kilometraje.ver',
            Str::startsWith($routeName, 'admin.verificaciones-tecnicas.') => Str::endsWith($routeName, '.registrar') ? 'verificaciones-tecnicas.crear' : 'verificaciones-tecnicas.ver',
            Str::startsWith($routeName, 'admin.historial-articulos-vehiculo.') => 'historial-articulos-vehiculo.ver',
            Str::startsWith($routeName, 'admin.gestion-cubiertas.') => 'gestion-cubiertas.ver',

            Str::startsWith($routeName, 'admin.pedidos-articulos.detalles.') => self::crudPermission($routeName, 'pedidos-articulos'),
            $routeName === 'admin.solicitudes-repuestos.aprobar' => 'solicitudes-repuestos.aprobar',
            $routeName === 'admin.solicitudes-repuestos.rechazar' => 'solicitudes-repuestos.rechazar',
            in_array($routeName, ['admin.solicitudes-repuestos.asociar-articulo', 'admin.solicitudes-repuestos.crear-articulo'], true) => 'solicitudes-repuestos.catalogar',
            $routeName === 'admin.solicitudes-repuestos.generar-pedido' => 'solicitudes-repuestos.generar-pedido',
            $routeName === 'admin.solicitudes-repuestos.estado' => 'solicitudes-repuestos.cerrar',
            $routeName === 'admin.solicitudes-repuestos.bulk' => 'solicitudes-repuestos.ver',
            Str::startsWith($routeName, 'admin.solicitudes-repuestos.') => self::crudPermission($routeName, 'solicitudes-repuestos'),
            Str::startsWith($routeName, 'admin.ordenes-compra.detalles.') => self::crudPermission($routeName, 'ordenes-compra'),
            Str::startsWith($routeName, 'admin.ordenes-compra.pagos.') => 'ordenes-compra.editar',
            $routeName === 'admin.ordenes-compra.mail' => 'ordenes-compra.enviar-mail',
            $routeName === 'admin.entradas.pendientes.store' => 'entradas.crear',
            Str::startsWith($routeName, 'admin.inventarios.transferencias.') => self::crudPermission($routeName, 'inventario-transferencias'),
            $routeName === 'admin.entregas-herramientas.devolver' => 'entregas-herramientas.editar',
            $routeName === 'admin.entregas-herramientas.planilla' => 'entregas-herramientas.ver',
            $routeName === 'admin.entregas-ropa-epp.devolver' => 'entregas-ropa-epp.editar',
            $routeName === 'admin.entregas-ropa-epp.planilla' => 'entregas-ropa-epp.ver',
            $routeName === 'admin.reparaciones-articulos.devolver' => 'reparaciones-articulos.editar',
            $routeName === 'admin.reparaciones-articulos.reclamos.store' => 'reparaciones-articulos.reclamar',
            $routeName === 'admin.reparaciones-articulos.planilla' => 'reparaciones-articulos.imprimir',
            $routeName === 'admin.reparaciones-articulos.bulk' => 'reparaciones-articulos.editar',
            $routeName === 'admin.inventarios.etiqueta' => 'inventarios.etiquetas',
            $routeName === 'admin.ajustes.backup' => 'ajustes.backup',
            Str::startsWith($routeName, 'admin.dashboards.') => 'dashboards.administrar',
            Str::startsWith($routeName, 'admin.entregas-herramientas.') => self::crudPermission($routeName, 'entregas-herramientas'),
            Str::startsWith($routeName, 'admin.entregas-ropa-epp.') => self::crudPermission($routeName, 'entregas-ropa-epp'),
            Str::startsWith($routeName, 'admin.reparaciones-articulos.') => self::crudPermission($routeName, 'reparaciones-articulos'),
            Str::startsWith($routeName, 'admin.configuracion-intervalos-servicio.') => self::crudPermission($routeName, 'configuracion-intervalos-servicio'),
            Str::startsWith($routeName, 'admin.configuracion-vencimientos-verificacion.') => self::crudPermission($routeName, 'configuracion-vencimientos-verificacion'),
            Str::startsWith($routeName, 'admin.unidad-medidas.') => self::crudPermission($routeName, 'unidad-medidas'),
            Str::startsWith($routeName, 'admin.inventarios.') => self::crudPermission($routeName, 'inventarios'),
            Str::startsWith($routeName, 'admin.depositos.') => self::crudPermission($routeName, 'depositos'),
            Str::startsWith($routeName, 'admin.ajustes.') => self::crudPermission($routeName, 'ajustes'),
            Str::startsWith($routeName, 'admin.bases.') => self::crudPermission($routeName, 'bases'),
            Str::startsWith($routeName, 'admin.servicios-asignados.') => self::crudPermission($routeName, 'servicios-asignados'),
            Str::startsWith($routeName, 'admin.roles.') => self::crudPermission($routeName, 'roles'),
            Str::startsWith($routeName, 'admin.users.') => self::crudPermission($routeName, 'users'),
            Str::startsWith($routeName, 'admin.empleados.') => self::crudPermission($routeName, 'empleados'),
            Str::startsWith($routeName, 'admin.cronogramas-laborales.') => self::crudPermission($routeName, 'cronogramas'),
            Str::startsWith($routeName, 'admin.provincias.') => self::crudPermission($routeName, 'provincias'),
            Str::startsWith($routeName, 'admin.ciudades.') => self::crudPermission($routeName, 'ciudades'),
            Str::startsWith($routeName, 'admin.categorias.') => self::crudPermission($routeName, 'categorias'),
            Str::startsWith($routeName, 'admin.titulares.') => self::crudPermission($routeName, 'titulares'),
            Str::startsWith($routeName, 'admin.marca-vehiculo.') => self::crudPermission($routeName, 'marca-vehiculo'),
            Str::startsWith($routeName, 'admin.cia-seguro.') => self::crudPermission($routeName, 'cia-seguro'),
            Str::startsWith($routeName, 'admin.tipo-vehiculo.') => self::crudPermission($routeName, 'tipo-vehiculo'),
            Str::startsWith($routeName, 'admin.marca-carroceria.') => self::crudPermission($routeName, 'marca-carroceria'),
            Str::startsWith($routeName, 'admin.tipo-motor.') => self::crudPermission($routeName, 'tipo-motor'),
            Str::startsWith($routeName, 'admin.modelo-motor.') => self::crudPermission($routeName, 'modelo-motor'),
            Str::startsWith($routeName, 'admin.tipo-caja.') => self::crudPermission($routeName, 'tipo-caja'),
            Str::startsWith($routeName, 'admin.modelo-caja.') => self::crudPermission($routeName, 'modelo-caja'),

            default => self::crudPermission($routeName, Str::between($routeName, 'admin.', '.')),
        };
    }

    private static function crudPermission(string $routeName, string $module): ?string
    {
        $action = Str::afterLast($routeName, '.');

        return match ($action) {
            'index', 'show' => "{$module}.ver",
            'create', 'store', 'additem', 'addItem', 'storeDetalle', 'generar-sugeridos' => "{$module}.crear",
            'edit', 'update', 'updateitem', 'updateItem', 'updateDetalle' => "{$module}.editar",
            'destroy', 'removeitem', 'removeItem', 'clearitem', 'clearItems', 'destroyDetalle' => "{$module}.eliminar",
            default => "{$module}.ver",
        };
    }
}
