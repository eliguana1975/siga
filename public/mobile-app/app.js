(function () {
  const root = document.getElementById('app');
  const apiBase = root.dataset.apiBase;
  const swUrl = root.dataset.swUrl;
  const mobileLoginUrl = root.dataset.mobileLoginUrl;
  const mobileLogoutUrl = root.dataset.mobileLogoutUrl;
  const QUEUE_KEY = 'siga_mobile_pending_queue';
  let deferredInstallPrompt = null;
  const state = {
    token: localStorage.getItem('siga_mobile_token') || '',
    csrfToken: root.dataset.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
    user: null,
    syncing: false,
    permissions: [],
    mobileMenu: [],
    chat: {
      users: [],
      conversations: [],
      selectedUserId: null
    },
    catalogs: {
      checklists: null,
      solicitudes: null
    }
  };

  const $ = selector => document.querySelector(selector);
  const $$ = selector => Array.from(document.querySelectorAll(selector));

  function headers(json = true) {
    const value = {
      Accept: 'application/json'
    };

    if (state.token) {
      value.Authorization = `Bearer ${state.token}`;
    }

    if (json) {
      value['Content-Type'] = 'application/json';
    }

    return value;
  }

  async function api(path, options = {}) {
    const response = await fetch(`${apiBase}${path}`, {
      ...options,
      headers: {
        ...headers(!(options.body instanceof FormData)),
        ...(options.headers || {})
      }
    });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
      const message = formatApiError(payload);
      throw Object.assign(new Error(message), { payload, status: response.status });
    }

    return payload;
  }

  async function webRequest(url, options = {}) {
    if (!url || url === 'undefined') {
      throw new Error('No se encontro la ruta de autenticacion movil. Actualiza la app y vuelve a intentar.');
    }

    const isUrlEncoded = options.body instanceof URLSearchParams;
    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers: {
        Accept: 'application/json',
        'Content-Type': isUrlEncoded ? 'application/x-www-form-urlencoded;charset=UTF-8' : 'application/json',
        'X-CSRF-TOKEN': state.csrfToken,
        ...(options.headers || {})
      }
    });
    const payload = await response.json().catch(() => ({}));

    if (payload.csrf_token) {
      state.csrfToken = payload.csrf_token;
      root.dataset.csrfToken = payload.csrf_token;
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) csrfMeta.content = payload.csrf_token;
    }

    if (!response.ok) {
      const message = formatApiError(payload);
      throw Object.assign(new Error(message), { payload, status: response.status });
    }

    return payload;
  }

  function setView(name) {
    $('[data-view="login"]').classList.toggle('hidden', name !== 'login');
    $('[data-view="app"]').classList.toggle('hidden', name !== 'app');
  }

  function message(id, text, error = false) {
    const node = document.getElementById(id);
    if (!node) return;
    node.textContent = text || '';
    node.classList.toggle('danger', error);
  }

  function isStandaloneMode() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }

  function updateInstallPanel() {
    const panel = $('#installPanel');
    const button = $('#installAppButton');
    const help = $('#installHelp');

    if (!panel || !button || !help) {
      return;
    }

    if (isStandaloneMode()) {
      panel.classList.add('hidden');
      return;
    }

    panel.classList.remove('hidden');

    if (deferredInstallPrompt) {
      button.disabled = false;
      button.textContent = 'Instalar app';
      help.textContent = 'Toca instalar para agregar SIGA a la pantalla de inicio.';
      return;
    }

    button.disabled = false;
    button.textContent = 'Como instalar';
    help.textContent = 'En Android usa el menu del navegador si no aparece el boton. En iPhone: Compartir > Agregar a inicio.';
  }

  async function promptInstallApp() {
    if (!deferredInstallPrompt) {
      window.alert('Para instalar: en Android abre el menu del navegador y toca Instalar app. En iPhone: Compartir > Agregar a inicio.');
      return;
    }

    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice.catch(() => null);
    deferredInstallPrompt = null;
    updateInstallPanel();
  }

  function formatApiError(payload) {
    const base = payload.message || 'No se pudo completar la operacion.';
    const errors = payload.errors || {};
    const details = Object.values(errors)
      .flat()
      .filter(Boolean);

    if (!details.length) {
      return base;
    }

    return `${base} ${details.slice(0, 3).join(' ')}`;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function pageData(payload) {
    return payload.data || payload.items || [];
  }

  function can(...permissions) {
    return Boolean(state.user?.is_super_usuario) || permissions.some(permission => state.permissions.includes(permission));
  }

  function userRoles() {
    return (state.user?.roles || []).map(role => String(role).toUpperCase());
  }

  function hasRole(role) {
    return userRoles().includes(role);
  }

  function hasOnlyOperationalRole(role) {
    const roles = userRoles().filter(value => value !== 'SUPERUSUARIO');
    return roles.length === 1 && roles[0] === role;
  }

  function preferredTab(access) {
    if (hasOnlyOperationalRole('CHOFER') && access.checklists) {
      return 'checklists';
    }

    if (hasOnlyOperationalRole('MECANICO')) {
      return access.ordenes ? 'ordenes' : (access.reparaciones ? 'reparaciones' : (access.solicitudes ? 'solicitudes' : 'checklists'));
    }

    if ((hasRole('JEFE DE TALLER') || hasRole('JEFE_TALLER') || hasRole('SUPERVISOR')) && access.ordenes) {
      return 'ordenes';
    }

    return ['dashboard', 'chat', 'ordenes', 'checklists', 'solicitudes', 'reparaciones', 'modulos']
      .find(tab => access[tab]);
  }

  function profileDescription() {
    if (state.user?.is_super_usuario) {
      return 'Perfil administrador: acceso completo a los modulos moviles.';
    }

    if (hasOnlyOperationalRole('CHOFER')) {
      return 'Perfil chofer: carga y consulta de checklists de unidad.';
    }

    if (hasOnlyOperationalRole('MECANICO')) {
      return 'Perfil mecanico: ordenes de trabajo, reparaciones y solicitudes de repuestos.';
    }

    if (hasRole('JEFE DE TALLER') || hasRole('JEFE_TALLER')) {
      return 'Perfil jefe de taller: seguimiento y actualizacion de ordenes de trabajo.';
    }

    if (hasRole('SUPERVISOR')) {
      return 'Perfil supervisor: seguimiento operativo de taller, flota y ordenes de trabajo.';
    }

    return 'Acceso segun los permisos asignados en SIGA.';
  }

  function configureAccess() {
    const access = {
      dashboard: state.user?.is_super_usuario || hasRole('SUPERVISOR') || (can('dashboard.ver') && !hasOnlyOperationalRole('CHOFER') && !hasOnlyOperationalRole('MECANICO')),
      chat: can('chat.ver', 'chat.crear'),
      ordenes: can('ordenes-trabajo.ver', 'ordenes-trabajo.editar'),
      checklists: can('controles-unidad.ver', 'controles-unidad.crear'),
      solicitudes: can('solicitudes-repuestos.ver', 'solicitudes-repuestos.crear'),
      reparaciones: can('reparaciones-articulos.ver', 'reparaciones-articulos.editar')
    };
    access.modulos = state.mobileMenu.length > 0;

    Object.entries(access).forEach(([tab, allowed]) => {
      $$(`[data-tab="${tab}"], [data-tab-jump="${tab}"], #tab-${tab}`).forEach(node => {
        node.classList.toggle('hidden', !allowed);
      });
    });

    $$('[data-requires]').forEach(node => {
      const required = node.dataset.requires.split(',').map(value => value.trim()).filter(Boolean);
      node.classList.toggle('hidden', !required.some(permission => can(permission)));
    });

    const profileNode = $('#profileDescription');
    if (profileNode) {
      profileNode.textContent = profileDescription();
    }

    renderMobileModules();

    const firstAllowed = preferredTab(access);
    switchTab(firstAllowed || 'dashboard');

    return access;
  }

  function readQueue() {
    try {
      return JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]');
    } catch (_) {
      return [];
    }
  }

  function writeQueue(items) {
    localStorage.setItem(QUEUE_KEY, JSON.stringify(items));
    updatePendingMetric();
  }

  function updatePendingMetric() {
    const node = $('#metricPendientes');
    if (node) {
      node.textContent = readQueue().length;
    }
  }

  async function queueRequest(label, path, options) {
    const queue = readQueue();
    const queued = {
      id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
      label,
      path,
      method: options.method || 'POST',
      headers: options.headers || {},
      body: options.body,
      bodyType: options.body instanceof FormData ? 'form' : 'json',
      createdAt: new Date().toISOString()
    };

    if (queued.bodyType === 'form') {
      const entries = [];
      for (const [key, value] of options.body.entries()) {
        if (value instanceof File) {
          entries.push({ key, file: true, name: value.name, type: value.type, value: await fileToDataUrl(value) });
        } else {
          entries.push({ key, value });
        }
      }
      queued.body = entries;
    }

    queue.push(queued);
    writeQueue(queue);
  }

  function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  function dataUrlToFile(dataUrl, name, type) {
    const [header, content] = dataUrl.split(',');
    const mime = type || (header.match(/data:(.*);base64/) || [])[1] || 'application/octet-stream';
    const binary = atob(content || '');
    const bytes = new Uint8Array(binary.length);
    for (let index = 0; index < binary.length; index += 1) {
      bytes[index] = binary.charCodeAt(index);
    }
    return new File([bytes], name || 'archivo', { type: mime });
  }

  function rebuildQueuedBody(item) {
    if (item.bodyType === 'form') {
      const form = new FormData();
      item.body.forEach(entry => {
        form.append(entry.key, entry.file ? dataUrlToFile(entry.value, entry.name, entry.type) : entry.value);
      });
      return form;
    }

    return item.body;
  }

  async function syncPendingQueue() {
    if (state.syncing || !state.token || !navigator.onLine) return;
    const queue = readQueue();
    if (!queue.length) {
      updatePendingMetric();
      return;
    }

    state.syncing = true;
    const remaining = [];
    for (const item of queue) {
      try {
        await api(item.path, {
          method: item.method,
          body: rebuildQueuedBody(item),
          headers: item.headers || {}
        });
      } catch (error) {
        remaining.push(item);
      }
    }
    state.syncing = false;
    writeQueue(remaining);

    if (remaining.length !== queue.length) {
      await loadAll();
    }
  }

  function switchTab(tab) {
    $$('.tabs button').forEach(button => button.classList.toggle('active', button.dataset.tab === tab));
    $$('.tab-page').forEach(page => page.classList.toggle('active', page.id === `tab-${tab}`));
  }

  function resetMobileSession() {
    localStorage.removeItem('siga_mobile_token');
    state.token = '';
    state.user = null;
    state.permissions = [];
    state.mobileMenu = [];
    message('loginMessage', '');
    setView('login');
  }

  async function enterMobileApp(userPayload = null) {
    if (userPayload) {
      state.user = userPayload;
    } else {
      const mePayload = await api('/me');
      state.user = mePayload.user;
    }

    const permissionsPayload = await api('/me/permisos');
    state.permissions = permissionsPayload.permissions || [];
    state.mobileMenu = permissionsPayload.mobile_menu || [];

    const userName = $('#userName');
    const userRole = $('#userRole');

    if (userName) {
      userName.textContent = state.user?.name || 'SIGA';
    }

    if (userRole) {
      const roles = userRoles();
      userRole.textContent = roles.length ? roles.join(', ') : 'Mobile';
    }

    setView('app');
    await loadAll();
  }

  async function boot() {
    if (!state.token) {
      setView('login');
      return;
    }

    try {
      await enterMobileApp();
    } catch (error) {
      resetMobileSession();
    }
  }

  async function loadAll() {
    const access = configureAccess();
    const loaders = [];

    if (access.ordenes) {
      loaders.push(loadOrdenes());
    }

    if (access.chat) {
      loaders.push(loadChat());
    }

    if (access.checklists) {
      loaders.push(loadChecklistCatalogs(), loadChecklists());
    }

    if (access.solicitudes) {
      loaders.push(loadSolicitudCatalogs(), loadSolicitudes());
    }

    if (access.reparaciones) {
      loaders.push(loadReparaciones());
    }

    await Promise.allSettled(loaders);
  }

  function renderMobileModules() {
    const target = $('#mobileModules');
    if (!target) return;

    target.innerHTML = state.mobileMenu.map(group => `<article class="module-group">
      <h3>${escapeHtml(group.group)}</h3>
      <div class="module-links">
        ${(group.items || []).filter(item => item.url && item.url !== 'undefined').map(item => `<a href="${escapeHtml(item.url)}" class="module-link">
          <strong>${escapeHtml(item.label)}</strong>
          <small>${escapeHtml(item.permission || 'Acceso general')}</small>
        </a>`).join('')}
      </div>
    </article>`).join('') || emptyCard('No tenes vistas web habilitadas para este usuario.');
  }

  async function loadOrdenes() {
    const estado = $('#ordenesEstado')?.value || '';
    const search = $('#ordenesSearch')?.value || '';
    const params = new URLSearchParams({ per_page: '12' });
    if (estado) params.set('estado', estado);
    if (search) params.set('search', search);

    const data = await api(`/ordenes-trabajo?${params.toString()}`);
    const rows = pageData(data);
    $('#metricOrdenes').textContent = data.total ?? rows.length;
    $('#ordenesList').innerHTML = rows.map(renderOrdenCard).join('') || emptyCard('No hay ordenes de trabajo para mostrar.');
  }

  async function loadChat() {
    const data = await api('/chat');
    state.chat.users = data.users || [];
    state.chat.conversations = data.conversations || [];
    $('#metricChat').textContent = data.unread_count || 0;
    renderChatLists();

    if (state.chat.selectedUserId) {
      await openChatUser(state.chat.selectedUserId, false);
    } else {
      renderChatEmpty();
    }
  }

  function renderChatLists() {
    const search = ($('#chatUserSearch')?.value || '').trim().toLowerCase();
    const conversations = state.chat.conversations.filter(item => item.user);
    const users = state.chat.users.filter(user => {
      const text = `${user.name || ''} ${user.email || ''}`.toLowerCase();
      return !search || text.includes(search);
    });

    $('#chatConversations').innerHTML = conversations.length
      ? `<strong class="chat-list-title">Conversaciones</strong>${conversations.map(conversation => chatPersonButton(conversation.user, conversation)).join('')}`
      : '';

    $('#chatUsers').innerHTML = `<strong class="chat-list-title">Usuarios</strong>${users.map(user => chatPersonButton(user)).join('') || '<small>No hay usuarios para mostrar.</small>'}`;
  }

  function chatPersonButton(user, conversation = null) {
    const active = Number(state.chat.selectedUserId) === Number(user.id);
    const unread = Number(conversation?.unread_count || 0);
    const latest = conversation?.last_message
      ? `${conversation.last_message.own ? 'Tu: ' : ''}${conversation.last_message.text || ''}`
      : user.email || '';

    return `<button type="button" class="chat-person ${active ? 'active' : ''}" data-chat-user="${escapeHtml(user.id)}">
      <span>
        <strong>${escapeHtml(user.name || 'Usuario')}</strong>
        <small>${escapeHtml(latest)}</small>
      </span>
      ${unread > 0 ? `<b>${unread}</b>` : ''}
    </button>`;
  }

  async function openChatUser(userId, refreshLists = true) {
    const data = await api(`/chat/users/${userId}`);
    state.chat.selectedUserId = Number(userId);
    $('#chatReceptorId').value = userId;
    $('#chatActiveName').textContent = data.user?.name || 'Usuario';
    $('#chatActiveEmail').textContent = data.user?.email || '';
    $('#chatForm').classList.toggle('hidden', !can('chat.crear'));
    renderChatMessages(data.messages || []);
    $('#metricChat').textContent = data.unread_count || 0;

    if (refreshLists) {
      await loadChat();
    } else {
      renderChatLists();
    }
  }

  function renderChatMessages(messages) {
    const target = $('#chatMessages');
    target.innerHTML = messages.map(message => `<div class="chat-message ${message.own ? 'own' : ''}">
      <div>
        <strong>${escapeHtml(message.own ? 'Tu' : (message.emisor?.name || 'Usuario'))}</strong>
        <p>${escapeHtml(message.mensaje || '')}</p>
        <small>${escapeHtml(message.created_at || '')}</small>
      </div>
    </div>`).join('') || '<small class="chat-empty">No hay mensajes en esta conversacion.</small>';
    target.scrollTop = target.scrollHeight;
  }

  function renderChatEmpty() {
    $('#chatActiveName').textContent = 'Selecciona un usuario';
    $('#chatActiveEmail').textContent = '';
    $('#chatMessages').innerHTML = '<small class="chat-empty">Elegí una conversación o un usuario para empezar.</small>';
    $('#chatForm').classList.add('hidden');
  }

  function renderOrdenCard(orden) {
    const unidad = orden.flota ? `${orden.flota.nro_interno || 'Unidad'} ${orden.flota.dominio || ''}`.trim() : 'Sin unidad';
    const motivos = (orden.motivos || []).map(motivo => motivo.nombre).join(', ');
    const actualizado = orden.actualizado_por?.name
      ? `Actualizo ${orden.actualizado_por.name} - ${orden.updated_at || ''}`
      : 'Sin actualizacion registrada';
    const canEditOrden = can('ordenes-trabajo.editar');
    const mechanicMode = hasOnlyOperationalRole('MECANICO');
    const supervisorMode = hasRole('SUPERVISOR') || hasRole('JEFE DE TALLER') || hasRole('JEFE_TALLER') || state.user?.is_super_usuario;
    const estadoOptions = [
      ['pendiente', 'Pendiente'],
      ['en_proceso', 'En proceso'],
      ['completada', 'Completada'],
      ['cancelada', 'Cancelada']
    ].map(([value, label]) => `<option value="${value}" ${orden.estado === value ? 'selected' : ''}>${label}</option>`).join('');
    const quickActions = canEditOrden ? `<form class="orden-form dense-form" data-orden-id="${orden.id}">
      <label>Estado
        <select name="estado">${estadoOptions}</select>
      </label>
      ${supervisorMode ? `<label>Vehiculo parado
        <select name="vehiculo_parado">
          <option value="0" ${orden.vehiculo_parado ? '' : 'selected'}>No</option>
          <option value="1" ${orden.vehiculo_parado ? 'selected' : ''}>Si</option>
        </select>
      </label>` : ''}
      <label>${mechanicMode ? 'Trabajo realizado / novedad' : 'Observaciones'}
        <textarea name="observaciones" rows="2">${escapeHtml(orden.observaciones || '')}</textarea>
      </label>
      <button type="submit">Guardar cambios</button>
      <p class="form-message" data-orden-message="${orden.id}"></p>
    </form>` : '';

    return `<article class="list-card orden-card">
      <div class="card-title-row">
        <strong>OT #${escapeHtml(orden.id)} - ${escapeHtml(orden.titulo || 'Orden de trabajo')}</strong>
        <span class="status ${escapeHtml(orden.estado || '')}">${escapeHtml(orden.estado_label || orden.estado || '')}</span>
      </div>
      <small>${escapeHtml(unidad)}${orden.kilometraje ? ` - ${escapeHtml(orden.kilometraje)} km` : ''}</small>
      <div class="meta">
        <span class="pill">${escapeHtml(orden.prioridad_label || orden.prioridad || 'Prioridad')}</span>
        ${motivos ? `<span class="pill">${escapeHtml(motivos)}</span>` : ''}
        ${orden.vehiculo_parado ? '<span class="pill danger-pill">Vehiculo parado</span>' : ''}
      </div>
      <small>${escapeHtml(actualizado)}</small>
      ${quickActions}
    </article>`;
  }

  async function loadChecklistCatalogs() {
    const data = await api('/checklists/catalogos');
    state.catalogs.checklists = data;
    fillSelect('#checklistFlota', data.flotas, item => `${item.nro_interno} - ${item.dominio || 'sin dominio'}`);
    fillSelect('#checklistServicio', data.servicios_asignados, item => item.nombre);
    renderChecklistDynamic(data);
  }

  async function loadSolicitudCatalogs() {
    const data = await api('/solicitudes-repuestos/catalogos');
    state.catalogs.solicitudes = data;
    fillSelect('#solicitudFlota', data.flotas || [], item => `${item.nro_interno} - ${item.dominio || 'sin dominio'}`, true);
    fillObjectSelect('#solicitudPrioridad', data.prioridades || {});
  }

  function fillSelect(selector, items, label, keepFirst = false) {
    const select = $(selector);
    const first = keepFirst ? select.querySelector('option')?.outerHTML || '' : '';
    select.innerHTML = first + (items || []).map(item => `<option value="${item.id}">${escapeHtml(label(item))}</option>`).join('');
  }

  function fillObjectSelect(selector, object) {
    const select = $(selector);
    select.innerHTML = Object.entries(object).map(([key, label]) => `<option value="${key}">${escapeHtml(label)}</option>`).join('');
  }

  function renderChecklistDynamic(data) {
    const target = $('#checklistDynamic');
    const partes = data.partes || {};
    const controles = data.control_unidad_items || {};
    const estadosParte = Array.isArray(data.estados_parte) ? data.estados_parte : Object.keys(data.estados_parte || {});
    const estadosControl = Array.isArray(data.estados_control) ? data.estados_control : Object.keys(data.estados_control || {});
    let html = '';

    Object.entries(partes).forEach(([parteKey, parte]) => {
      html += checklistTable(parte.titulo || parte.label || parteKey, estadosParte, Object.entries(parte.items || {}).map(([itemKey, label]) => ({
        name: `partes.${parteKey}.${itemKey}`,
        label
      })));
    });

    html += checklistTable('Control vehicular', estadosControl, Object.entries(controles).map(([itemKey, label]) => ({
      name: `control_unidad.${itemKey}`,
      label
    })));

    target.innerHTML = html;
  }

  function checklistTable(title, options, rows) {
    const labels = {
      cumple: 'Cumple',
      no_cumple: 'No cumple',
      na: 'N/A',
      hecho: 'Hecho',
      sin_hacer: 'Sin hacer'
    };

    return `<div class="check-group">
      <strong>${escapeHtml(title)}</strong>
      <div class="check-table" style="--check-cols:${options.length}">
        <div class="check-row check-head">
          <span></span>
          ${options.map(option => `<span>${escapeHtml(labels[option] || option)}</span>`).join('')}
        </div>
        ${rows.map(row => `<div class="check-row">
          <span>${escapeHtml(row.label)}</span>
          ${options.map(option => `<label class="check-radio" title="${escapeHtml(labels[option] || option)}">
            <input type="radio" name="${escapeHtml(row.name)}" value="${escapeHtml(option)}" required>
          </label>`).join('')}
        </div>`).join('')}
      </div>
    </div>`;
  }

  function legacySelectHtml(data) {
    let html = '';
    Object.entries(data.partes || {}).forEach(([parteKey, parte]) => {
      html += `<div class="field-group"><strong>${escapeHtml(parte.titulo || parte.label || parteKey)}</strong>`;
      Object.entries(parte.items || {}).forEach(([itemKey, label]) => {
        html += selectHtml(`partes.${parteKey}.${itemKey}`, label, data.estados_parte || []);
      });
      html += '</div>';
    });

    html += '<div class="field-group"><strong>Control unidad</strong>';
    Object.entries(data.control_unidad_items || {}).forEach(([itemKey, label]) => {
      html += selectHtml(`control_unidad.${itemKey}`, label, data.estados_control || []);
    });
    html += '</div>';
    return html;
  }

  function selectHtml(name, label, options) {
    return `<label>${escapeHtml(label)}
      <select name="${escapeHtml(name)}" required>
        ${options.map(option => `<option value="${option}">${escapeHtml(option)}</option>`).join('')}
      </select>
    </label>`;
  }

  async function loadChecklists() {
    const data = await api('/checklists?per_page=8');
    const rows = pageData(data);
    $('#metricChecklists').textContent = data.total ?? rows.length;
    $('#checklistsList').innerHTML = rows.map(item => card(
      `Checklist #${item.id}`,
      `${item.interno || item.flota?.nro_interno || 'Unidad'} · ${item.kilometraje_actual || 0} km`,
      [item.created_at || '', item.servicio_asignado || item.servicio_asignado?.nombre || '']
    )).join('') || emptyCard('No hay checklists recientes.');
  }

  async function loadSolicitudes() {
    const data = await api('/solicitudes-repuestos?per_page=8');
    const rows = pageData(data);
    $('#metricSolicitudes').textContent = data.total ?? rows.length;
    $('#solicitudesList').innerHTML = rows.map(item => card(
      `Solicitud #${item.id}`,
      item.descripcion_repuesto || item.descripcion || 'Repuesto',
      [item.estado_label || item.estado, item.prioridad_label || item.prioridad]
    )).join('') || emptyCard('No hay solicitudes recientes.');
  }

  async function loadReparaciones() {
    const data = await api('/reparaciones-articulos?per_page=8');
    const rows = pageData(data);
    $('#metricReparaciones').textContent = data.total ?? rows.length;
    $('#reparacionesList').innerHTML = rows.map(item => {
      const detalles = (item.detalles || []).filter(detalle => Number(detalle.cantidad_pendiente || 0) > 0);
      const buttons = detalles.map(detalle => `<button type="button" data-return="${item.id}:${detalle.id}:${detalle.cantidad_pendiente}">Devolver ${escapeHtml(detalle.nombre_articulo || detalle.articulo?.nombre || 'articulo')}</button>`).join('');
      return card(
        item.numero_orden || `Reparacion #${item.id}`,
        item.proveedor?.nombre || item.proveedor || 'Sin proveedor',
        [item.estado, `${detalles.length} pendientes`],
        buttons
      );
    }).join('') || emptyCard('No hay reparaciones recientes.');
  }

  function card(title, subtitle, pills = [], actions = '') {
    return `<article class="list-card">
      <strong>${escapeHtml(title)}</strong>
      <small>${escapeHtml(subtitle)}</small>
      <div class="meta">${pills.filter(Boolean).map(pill => `<span class="pill">${escapeHtml(pill)}</span>`).join('')}</div>
      ${actions ? `<div class="dense-form" style="margin-top:10px">${actions}</div>` : ''}
    </article>`;
  }

  function emptyCard(text) {
    return `<article class="list-card"><small>${escapeHtml(text)}</small></article>`;
  }

  function nestedAssign(target, path, value) {
    const parts = path.split('.');
    let cursor = target;
    parts.forEach((part, index) => {
      if (index === parts.length - 1) {
        cursor[part] = value;
        return;
      }
      cursor[part] = cursor[part] || {};
      cursor = cursor[part];
    });
  }

  $('#loginForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget;
    message('loginMessage', 'Ingresando...');
    const data = Object.fromEntries(new FormData(form).entries());

    try {
      const payload = await webRequest(mobileLoginUrl, {
        method: 'POST',
        body: new URLSearchParams({ ...data, device_name: 'siga-pwa' })
      });
      state.token = payload.access_token;
      localStorage.setItem('siga_mobile_token', state.token);
      form.reset();
      await enterMobileApp(payload.user);
    } catch (error) {
      message('loginMessage', error.message, true);
    }
  });

  $('#logoutButton').addEventListener('click', async () => {
    try {
      await api('/auth/logout', { method: 'POST', body: JSON.stringify({}) });
    } catch (_) {}
    try {
      await webRequest(mobileLogoutUrl, { method: 'POST', body: JSON.stringify({}) });
    } catch (_) {}
    localStorage.removeItem('siga_mobile_token');
    resetMobileSession();
  });

  $('#installAppButton')?.addEventListener('click', promptInstallApp);

  $$('.tabs button, [data-tab-jump]').forEach(button => {
    button.addEventListener('click', () => switchTab(button.dataset.tab || button.dataset.tabJump));
  });

  $('#refreshChecklists').addEventListener('click', loadChecklists);
  $('#refreshChat').addEventListener('click', loadChat);
  $('#refreshOrdenes').addEventListener('click', loadOrdenes);
  $('#refreshSolicitudes').addEventListener('click', loadSolicitudes);
  $('#refreshReparaciones').addEventListener('click', loadReparaciones);
  $('#refreshModules').addEventListener('click', async () => {
    try {
      const permissionsPayload = await api('/me/permisos');
      state.permissions = permissionsPayload.permissions || [];
      state.mobileMenu = permissionsPayload.mobile_menu || [];
      configureAccess();
    } catch (error) {
      window.alert(error.message);
    }
  });

  $('#ordenesEstado').addEventListener('change', loadOrdenes);
  $('#ordenesSearch').addEventListener('input', debounce(loadOrdenes, 350));
  $('#chatUserSearch').addEventListener('input', renderChatLists);

  $('#tab-chat').addEventListener('click', event => {
    const button = event.target.closest('[data-chat-user]');
    if (!button) return;
    openChatUser(button.dataset.chatUser).catch(error => window.alert(error.message));
  });

  $('#chatForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget;
    const payload = Object.fromEntries(new FormData(form).entries());
    message('chatMessageStatus', 'Enviando...');

    try {
      await api('/chat/messages', {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      $('#chatMessageInput').value = '';
      message('chatMessageStatus', 'Mensaje enviado.');
      await openChatUser(payload.receptor_id);
    } catch (error) {
      message('chatMessageStatus', error.message, true);
    }
  });

  $('#checklistForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget;
    message('checklistMessage', 'Guardando...');
    const payload = {};
    new FormData(form).forEach((value, key) => nestedAssign(payload, key, value));

    try {
      await api('/checklists', { method: 'POST', body: JSON.stringify(payload) });
      form.reset();
      message('checklistMessage', 'Checklist guardado.');
      await loadChecklists();
    } catch (error) {
      if (!navigator.onLine) {
        await queueRequest('Checklist', '/checklists', { method: 'POST', body: JSON.stringify(payload) });
        form.reset();
        message('checklistMessage', 'Sin conexion: checklist guardado como pendiente.');
      } else {
        message('checklistMessage', error.message, true);
      }
    }
  });

  $('#solicitudForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget;
    message('solicitudMessage', 'Enviando...');
    const formData = new FormData(form);

    try {
      await api('/solicitudes-repuestos', { method: 'POST', body: formData, headers: { Accept: 'application/json' } });
      form.reset();
      message('solicitudMessage', 'Solicitud enviada.');
      await loadSolicitudes();
    } catch (error) {
      if (!navigator.onLine) {
        await queueRequest('Solicitud de repuesto', '/solicitudes-repuestos', { method: 'POST', body: formData, headers: { Accept: 'application/json' } });
        form.reset();
        message('solicitudMessage', 'Sin conexion: solicitud guardada como pendiente.');
      } else {
        message('solicitudMessage', error.message, true);
      }
    }
  });

  $('#reparacionesList').addEventListener('click', async event => {
    const button = event.target.closest('[data-return]');
    if (!button) return;
    const [reparacionId, detalleId, pendiente] = button.dataset.return.split(':');
    const cantidad = window.prompt(`Cantidad a devolver (pendiente ${pendiente})`, pendiente);
    if (!cantidad) return;

    try {
      await api(`/reparaciones-articulos/${reparacionId}/detalles/${detalleId}/devolver`, {
        method: 'POST',
        body: JSON.stringify({
          cantidad_devuelta: Number(cantidad),
          fecha_devolucion: new Date().toISOString().slice(0, 10),
          observaciones: 'Registrado desde SIGA Mobile'
        })
      });
      await loadReparaciones();
    } catch (error) {
      if (!navigator.onLine) {
        await queueRequest('Devolucion reparacion', `/reparaciones-articulos/${reparacionId}/detalles/${detalleId}/devolver`, {
          method: 'POST',
          body: JSON.stringify({
            cantidad_devuelta: Number(cantidad),
            fecha_devolucion: new Date().toISOString().slice(0, 10),
            observaciones: 'Registrado desde SIGA Mobile'
          })
        });
        window.alert('Sin conexion: devolucion guardada como pendiente.');
      } else {
        window.alert(error.message);
      }
    }
  });

  $('#ordenesList').addEventListener('submit', async event => {
    const form = event.target.closest('.orden-form');
    if (!form) return;
    event.preventDefault();

    const ordenId = form.dataset.ordenId;
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.vehiculo_parado = payload.vehiculo_parado === '1';
    const msg = form.querySelector(`[data-orden-message="${ordenId}"]`);
    if (msg) msg.textContent = 'Guardando...';

    try {
      await api(`/ordenes-trabajo/${ordenId}`, {
        method: 'PATCH',
        body: JSON.stringify(payload)
      });
      if (msg) msg.textContent = 'Orden actualizada.';
      await loadOrdenes();
    } catch (error) {
      if (msg) {
        msg.textContent = error.message;
        msg.classList.add('danger');
      } else {
        window.alert(error.message);
      }
    }
  });

  function debounce(callback, delay) {
    let timer = null;
    return function (...args) {
      window.clearTimeout(timer);
      timer = window.setTimeout(() => callback.apply(this, args), delay);
    };
  }

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(swUrl).catch(() => {});
  }

  window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstallPrompt = event;
    updateInstallPanel();
  });
  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    updateInstallPanel();
  });
  window.addEventListener('online', syncPendingQueue);
  updateInstallPanel();
  updatePendingMetric();
  boot();
})();
