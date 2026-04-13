/* ============================================================
   MEDY'S CATERING – STAFF SYSTEM GLOBAL SCRIPTS
   app.js
   ============================================================ */

/* ===================== AUTH GUARD =====================
   Runs immediately on every page except index.html & login.html.
   If no session is found, the user is redirected to login.
   ======================================================= */
(function authGuard() {
  const publicPages = ['login.html', 'index.html', ''];
  const currentPage = window.location.pathname.split('/').pop();
  if (publicPages.includes(currentPage)) return; 

  const sessionUser = sessionStorage.getItem('mc_user');
  if (!sessionUser) {
    window.location.replace('login.html');
  }
})();

function _getSessionUser() {
  const raw = sessionStorage.getItem('mc_user');
  if (!raw) return { name: 'Guest', role: 'staff', initials: 'G' };
  try { return JSON.parse(raw); }
  catch (e) { return { name: 'Guest', role: 'staff', initials: 'G' }; }
}

/* ===================== MOCK DATA =====================
   NOTE FOR BACKEND DATABASE:
   Replace all arrays below with actual fetch() calls to your backend API.
   
   WHAT TO DO WHEN ADDING A REAL DATABASE:
   1. REMOVE the entire MC_DATA object below (bookings & feedback arrays).
   2. REMOVE the currentUser hardcoding — it is already read from sessionStorage above.
   3. REPLACE each data reference with API calls. Examples:
        const bookings = await fetch('/api/bookings', {
          headers: { 'Authorization': 'Bearer ' + sessionStorage.getItem('mc_token') }
        }).then(r => r.json());
   4. REMOVE the Mock Auth block in login.html and replace with:
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password, role })
        });
        const data = await res.json();
        if (data.token) {
          sessionStorage.setItem('mc_token', data.token);
          sessionStorage.setItem('mc_user', JSON.stringify(data.user));
          window.location.href = 'dashboard.html';
        }
   5. REMOVE the demo credentials block in login.html (marked with <!-- REMOVE this demo block in production -->).
   6. REPLACE the MC_STAFF array in accounts.html with: fetch('/api/accounts')
   ===================================================== */

const MC_DATA = {
  // currentUser is always loaded from sessionStorage — never hardcoded
  currentUser: _getSessionUser(),

  bookings: [
    /* DATABASE NOTE: Replace this array with fetch('/api/bookings') */
    { id: 'BK-001', client: 'Santos Family',  event: 'Wedding Reception',  date: '2025-07-12', guests: 150, package: 'Premium',  status: 'confirmed', venue: 'Grand Ballroom, Lipa City' },
    { id: 'BK-002', client: 'ABC Corporation',event: 'Corporate Seminar',   date: '2025-07-18', guests: 80,  package: 'Standard', status: 'confirmed', venue: 'Hotel Miramar, Batangas' },
    { id: 'BK-003', client: 'Reyes Family',   event: 'Birthday Party',      date: '2025-07-22', guests: 60,  package: 'Basic',    status: 'pending',   venue: 'Reyes Residence, Lipa' },
    { id: 'BK-004', client: 'PUP',            event: 'Graduation Ceremony', date: '2025-07-25', guests: 200, package: 'Standard', status: 'confirmed', venue: 'PUP Gymnasium' },
    { id: 'BK-005', client: 'Cruz Family',    event: 'Debut Celebration',   date: '2025-08-03', guests: 100, package: 'Premium',  status: 'pending',   venue: 'Fiesta Garden, Lipa' },
    { id: 'BK-006', client: 'Dela Cruz Co.',  event: 'Company Anniversary', date: '2025-08-10', guests: 120, package: 'Premium',  status: 'confirmed', venue: 'Event Hall, Batangas City' },
    { id: 'BK-007', client: 'Garcia Family',  event: 'Family Reunion',      date: '2025-06-30', guests: 75,  package: 'Standard', status: 'completed', venue: 'Garcia Farm, Lipa' },
    { id: 'BK-008', client: 'Lima Family',    event: 'Birthday Party',      date: '2025-06-15', guests: 50,  package: 'Basic',    status: 'cancelled', venue: 'Lim Residence' },
  ],

  feedback: [
    /* DATABASE NOTE: Replace this array with fetch('/api/feedback') */
    { id: 1, client: 'Santos Family',   event: 'Wedding Reception',  date: '2025-07-13', rating: 5, comment: 'Everything was perfect! The food was amazing and the staff were very professional.', status: 'new' },
    { id: 2, client: 'ABC Corporation', event: 'Corporate Seminar',  date: '2025-07-19', rating: 4, comment: 'Great service and timely setup. Food was delicious. Would recommend!',               status: 'read' },
    { id: 3, client: 'Garcia Family',   event: 'Family Reunion',     date: '2025-07-01', rating: 5, comment: "Medy's Catering never disappoints. Will definitely book again.",                    status: 'read' },
    { id: 4, client: 'PUP',             event: 'Graduation Ceremony',date: '2025-07-26', rating: 4, comment: 'The coordination was smooth and the food was well-received by everyone.',           status: 'new' },
  ]
};

function initSidebar() {
  const toggle  = document.querySelector('.mc-sidebar-toggle');
  const sidebar = document.querySelector('.mc-sidebar');
  const overlay = document.querySelector('.mc-sidebar-overlay');

  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }
}

/* ===================== ACTIVE NAV ===================== */
function setActiveNav() {
  const page = window.location.pathname.split('/').pop() || 'dashboard.html';
  document.querySelectorAll('.mc-nav-item[data-page]').forEach(item => {
    item.classList.toggle('active', item.dataset.page === page);
  });
}

/* ===================== TOAST NOTIFICATION ===================== */
function showToast(msg, type = 'success') {
  let container = document.querySelector('.mc-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'mc-toast-container';
    document.body.appendChild(container);
  }

  const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
  const toast = document.createElement('div');
  toast.className = `mc-toast ${type}`;
  toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i> ${msg}`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(40px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('open');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('open');
}

function statusBadge(status) {
  const map = {
    confirmed: ['mc-badge-confirmed', 'bi-check-circle-fill', 'Confirmed'],
    pending:   ['mc-badge-pending',   'bi-clock-fill',        'Pending'],
    cancelled: ['mc-badge-cancelled', 'bi-x-circle-fill',     'Cancelled'],
    completed: ['mc-badge-completed', 'bi-check2-all',        'Completed'],
  };
  const [cls, icon, label] = map[status] || ['mc-badge-pending', 'bi-circle', 'Unknown'];
  return `<span class="mc-badge ${cls}"><i class="bi ${icon}"></i>${label}</span>`;
}

function starRating(n) {
  return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function fmtDate(str) {
  const d = new Date(str);
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function renderSidebar(activePage) {
  const isAdmin      = MC_DATA.currentUser.role === 'admin';
  const pendingCount = MC_DATA.bookings.filter(b => b.status === 'pending').length;
  const newFeedback  = MC_DATA.feedback.filter(f => f.status === 'new').length;

  return `
  <aside class="mc-sidebar">
    <div class="mc-sidebar-brand">
      <div class="mc-sidebar-brand-icon"><i class="bi bi-award-fill"></i></div>
      <div>
        <div class="mc-sidebar-brand-text">Medy's Catering</div>
        <div class="mc-sidebar-brand-sub">Staff Portal</div>
      </div>
    </div>

    <nav class="mc-nav-section">
      <div class="mc-nav-label">Main</div>
      <a href="dashboard.html" class="mc-nav-item ${activePage === 'dashboard' ? 'active' : ''}" data-page="dashboard.html">
        <span class="mc-nav-icon"><i class="bi bi-grid-fill"></i></span> Dashboard
      </a>
      <a href="bookings.html" class="mc-nav-item ${activePage === 'bookings' ? 'active' : ''}" data-page="bookings.html">
        <span class="mc-nav-icon"><i class="bi bi-calendar-check-fill"></i></span> Bookings
        ${pendingCount ? `<span class="mc-nav-badge">${pendingCount}</span>` : ''}
      </a>
      <a href="schedule.html" class="mc-nav-item ${activePage === 'schedule' ? 'active' : ''}" data-page="schedule.html">
        <span class="mc-nav-icon"><i class="bi bi-calendar3"></i></span> Event Schedule
      </a>
      <a href="feedback.html" class="mc-nav-item ${activePage === 'feedback' ? 'active' : ''}" data-page="feedback.html">
        <span class="mc-nav-icon"><i class="bi bi-chat-square-heart-fill"></i></span> Feedback
        ${newFeedback ? `<span class="mc-nav-badge">${newFeedback}</span>` : ''}
      </a>
      <a href="reports.html" class="mc-nav-item ${activePage === 'reports' ? 'active' : ''}" data-page="reports.html">
        <span class="mc-nav-icon"><i class="bi bi-bar-chart-fill"></i></span> Reports
      </a>
    </nav>

    ${isAdmin ? `
    <nav class="mc-nav-section" style="margin-top:0.5rem;">
      <div class="mc-nav-label">Admin Only</div>
      <a href="accounts.html" class="mc-nav-item ${activePage === 'accounts' ? 'active' : ''}" data-page="accounts.html">
        <span class="mc-nav-icon"><i class="bi bi-people-fill"></i></span> Staff Accounts
      </a>
    </nav>` : ''}

    <div class="mc-sidebar-footer">
      <div class="mc-user-pill" onclick="handleLogout()">
        <div class="mc-avatar">${MC_DATA.currentUser.initials}</div>
        <div>
          <div class="mc-user-name">${MC_DATA.currentUser.name}</div>
          <div class="mc-user-role">${MC_DATA.currentUser.role === 'admin' ? 'Administrator' : 'Staff'} · Logout</div>
        </div>
        <i class="bi bi-box-arrow-right ms-auto" style="color:rgba(255,255,255,0.4);font-size:0.9rem;"></i>
      </div>
    </div>
  </aside>
  <div class="mc-sidebar-overlay"></div>`;
}

/* ===================== LOGOUT =====================
   Clears the session and redirects to login.
   DATABASE NOTE: If using JWT tokens, also call POST /api/auth/logout
   to invalidate the token on the server side before clearing sessionStorage.
   ===================================================== */
function handleLogout() {
  /* DATABASE NOTE (for production):
     await fetch('/api/auth/logout', {
       method: 'POST',
       headers: { 'Authorization': 'Bearer ' + sessionStorage.getItem('mc_token') }
     });
  */
  sessionStorage.clear(); // KEEP THIS — clears all session data including user and token
  window.location.href = 'login.html';
}

function renderTopbar(title, breadcrumb) {
  const newFeedback = MC_DATA.feedback.filter(f => f.status === 'new').length;
  const user        = MC_DATA.currentUser;

  return `
  <header class="mc-topbar">
    <div class="mc-topbar-left">
      <button class="mc-sidebar-toggle"><i class="bi bi-list"></i></button>
      <div>
        <div class="mc-page-title">${title}</div>
        <div class="mc-page-breadcrumb">${breadcrumb}</div>
      </div>
    </div>
    <div class="mc-topbar-right">
      <span class="d-none d-md-flex align-items-center gap-2 me-2" style="font-size:0.82rem;color:var(--mc-gray);">
        <span class="mc-badge ${user.role === 'admin' ? 'mc-badge-admin' : 'mc-badge-staff'}">${user.role === 'admin' ? 'Admin' : 'Staff'}</span>
        <span>${user.name}</span>
      </span>
      <button class="mc-topbar-btn" title="Feedback Notifications" onclick="window.location='feedback.html'">
        <i class="bi bi-bell-fill"></i>
        ${newFeedback ? '<span class="mc-topbar-notif-dot"></span>' : ''}
      </button>
      <button class="mc-topbar-btn" title="Logout" onclick="handleLogout()">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </div>
  </header>`;
}

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  setActiveNav();

  document.querySelectorAll('.mc-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });
});
