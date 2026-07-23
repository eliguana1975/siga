<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#151521">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGA Mobile</title>
    <link rel="manifest" href="{{ asset('mobile-app/manifest.webmanifest') }}">
    <link rel="stylesheet" href="{{ asset('mobile-app/app.css') }}?v={{ filemtime(public_path('mobile-app/app.css')) }}">
</head>
<body>
    <main id="app" class="mobile-shell" data-api-base="{{ url('/api/v1') }}" data-sw-url="{{ asset('mobile-sw.js') }}"
        data-mobile-login-url="{{ route('mobile.auth.login') }}" data-mobile-logout-url="{{ route('mobile.auth.logout') }}"
        data-csrf-token="{{ csrf_token() }}">
        <section class="login-screen" data-view="login">
            <div class="brand-block">
                <span class="brand-mark">SF</span>
                <div>
                    <h1>SIGA Fleet</h1>
                    <p>Operaciones de taller y flota desde el celular.</p>
                </div>
            </div>
            <div class="install-panel panel" id="installPanel">
                <div>
                    <strong>Instalar en el telefono</strong>
                    <p id="installHelp">Usa SIGA como app desde la pantalla de inicio.</p>
                </div>
                <button type="button" id="installAppButton">Instalar app</button>
            </div>
            <form id="loginForm" class="panel">
                <label>Email
                    <input type="email" name="email" autocomplete="email" required>
                </label>
                <label>Clave
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button type="submit">Ingresar</button>
                <p class="form-message" id="loginMessage"></p>
            </form>
        </section>

        <section class="app-screen hidden" data-view="app">
            <header class="mobile-header">
                <div>
                    <strong id="userName">SIGA</strong>
                    <span id="userRole">Mobile</span>
                </div>
                <button type="button" id="logoutButton">Salir</button>
            </header>

            <nav class="tabs" aria-label="Modulos">
                <button type="button" data-tab="dashboard" class="active">Inicio</button>
                <button type="button" data-tab="chat">Chat</button>
                <button type="button" data-tab="ordenes">Ordenes</button>
                <button type="button" data-tab="checklists">Checklist</button>
                <button type="button" data-tab="solicitudes">Repuestos</button>
                <button type="button" data-tab="reparaciones">Reparaciones</button>
                <button type="button" data-tab="modulos">Modulos</button>
            </nav>

            <p class="profile-description" id="profileDescription"></p>

            <section class="tab-page active" id="tab-dashboard">
                <div class="metric-grid">
                    <article><span id="metricChat">0</span><small>Mensajes</small></article>
                    <article><span id="metricOrdenes">0</span><small>Ordenes</small></article>
                    <article><span id="metricChecklists">0</span><small>Checklists</small></article>
                    <article><span id="metricSolicitudes">0</span><small>Solicitudes</small></article>
                    <article><span id="metricReparaciones">0</span><small>Reparaciones</small></article>
                    <article><span id="metricPendientes">0</span><small>Pendientes</small></article>
                </div>
                <div class="panel">
                    <h2>Acciones rapidas</h2>
                    <button type="button" data-tab-jump="chat" data-requires="chat.ver">Abrir chat</button>
                    <button type="button" data-tab-jump="ordenes" data-requires="ordenes-trabajo.ver">Ver ordenes de trabajo</button>
                    <button type="button" data-tab-jump="checklists" data-requires="controles-unidad.crear">Nuevo checklist</button>
                    <button type="button" data-tab-jump="solicitudes" data-requires="solicitudes-repuestos.crear">Solicitar repuesto</button>
                </div>
            </section>

            <section class="tab-page" id="tab-chat">
                <div class="section-head">
                    <h2>Chat interno</h2>
                    <button type="button" id="refreshChat">Actualizar</button>
                </div>
                <div class="chat-layout">
                    <div class="panel dense-form">
                        <label>Buscar usuario
                            <input type="search" id="chatUserSearch" placeholder="Nombre o correo">
                        </label>
                        <div id="chatConversations" class="chat-list"></div>
                        <div id="chatUsers" class="chat-list"></div>
                    </div>
                    <div class="panel chat-panel">
                        <div class="chat-active-head">
                            <strong id="chatActiveName">Selecciona un usuario</strong>
                            <small id="chatActiveEmail"></small>
                        </div>
                        <div id="chatMessages" class="chat-thread-mobile"></div>
                        <form id="chatForm" class="chat-compose hidden">
                            <input type="hidden" name="receptor_id" id="chatReceptorId">
                            <textarea name="mensaje" id="chatMessageInput" rows="2" maxlength="2000" placeholder="Escribir mensaje" required></textarea>
                            <button type="submit">Enviar</button>
                            <p class="form-message" id="chatMessageStatus"></p>
                        </form>
                    </div>
                </div>
            </section>

            <section class="tab-page" id="tab-ordenes">
                <div class="section-head">
                    <h2>Ordenes de trabajo</h2>
                    <button type="button" id="refreshOrdenes">Actualizar</button>
                </div>
                <div class="panel dense-form">
                    <label>Filtro
                        <select id="ordenesEstado">
                            <option value="">Todas</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="completada">Completadas</option>
                            <option value="cancelada">Canceladas</option>
                        </select>
                    </label>
                    <label>Buscar
                        <input type="search" id="ordenesSearch" placeholder="Interno, dominio, titulo o numero">
                    </label>
                </div>
                <div id="ordenesList" class="list"></div>
            </section>

            <section class="tab-page" id="tab-checklists">
                <div class="section-head">
                    <h2>Checklist de unidad</h2>
                    <button type="button" id="refreshChecklists">Actualizar</button>
                </div>
                <form id="checklistForm" class="panel dense-form">
                    <label>Unidad
                        <select name="flota_id" id="checklistFlota" required></select>
                    </label>
                    <label>Servicio
                        <select name="servicio_asignado_id" id="checklistServicio" required></select>
                    </label>
                    <label>Kilometraje
                        <input type="number" name="kilometraje_actual" min="0" required>
                    </label>
                    <label>Observaciones
                        <textarea name="observaciones_generales" rows="2" required></textarea>
                    </label>
                    <div id="checklistDynamic" class="dynamic-fields"></div>
                    <button type="submit">Guardar checklist</button>
                    <p class="form-message" id="checklistMessage"></p>
                </form>
                <div id="checklistsList" class="list"></div>
            </section>

            <section class="tab-page" id="tab-solicitudes">
                <div class="section-head">
                    <h2>Solicitudes de repuestos</h2>
                    <button type="button" id="refreshSolicitudes">Actualizar</button>
                </div>
                <form id="solicitudForm" class="panel dense-form">
                    <label>Unidad
                        <select name="flota_id" id="solicitudFlota">
                            <option value="">Sin unidad</option>
                        </select>
                    </label>
                    <label>Prioridad
                        <select name="prioridad" id="solicitudPrioridad" required></select>
                    </label>
                    <label>Cantidad
                        <input type="number" name="cantidad" min="1" value="1" required>
                    </label>
                    <label>Descripcion
                        <input type="text" name="descripcion_repuesto" required>
                    </label>
                    <label>Codigo
                        <input type="text" name="codigo_repuesto">
                    </label>
                    <label>Foto repuesto
                        <input type="file" name="foto_repuesto" accept="image/*" capture="environment">
                    </label>
                    <button type="submit">Enviar solicitud</button>
                    <p class="form-message" id="solicitudMessage"></p>
                </form>
                <div id="solicitudesList" class="list"></div>
            </section>

            <section class="tab-page" id="tab-reparaciones">
                <div class="section-head">
                    <h2>Reparaciones</h2>
                    <button type="button" id="refreshReparaciones">Actualizar</button>
                </div>
                <div id="reparacionesList" class="list"></div>
            </section>

            <section class="tab-page" id="tab-modulos">
                <div class="section-head">
                    <h2>Vistas del sistema</h2>
                    <button type="button" id="refreshModules">Actualizar</button>
                </div>
                <div id="mobileModules" class="module-list"></div>
            </section>
        </section>
    </main>
    <script src="{{ asset('mobile-app/app.js') }}?v={{ filemtime(public_path('mobile-app/app.js')) }}" defer></script>
</body>
</html>
