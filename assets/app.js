const API = '/medysStaff/api';

/* ============================================================
   MEDY'S CATERING – STAFF SYSTEM GLOBAL SCRIPTS
   app.js
   ============================================================ */

(function authGuard() {
  const publicPages = ['login.php', 'index.php', ''];
  const currentPage = window.location.pathname.split('/').pop();
  if (publicPages.includes(currentPage)) return;
  if (!sessionStorage.getItem('mc_user')) {
    window.location.replace('login.php');
  }
})();

function _getSessionUser() {
  const raw = sessionStorage.getItem('mc_user');
  if (!raw) return { name: 'Guest', role: 'staff', initials: 'G' };
  try { return JSON.parse(raw); }
  catch (e) { return { name: 'Guest', role: 'staff', initials: 'G' }; }
}

const MC_DATA = {
  currentUser: _getSessionUser(),
  bookings: [],
  feedback: [],
};

/* ===================== API HELPERS ===================== */

async function apiRequest(endpoint, options = {}) {
  const res = await fetch(API + endpoint, {
    ...options,
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(options.headers || {}),
    },
  });

  if (res.status === 401) {
    sessionStorage.clear();
    window.location.href = 'login.php';
    return null;
  }

  if (res.status === 204) return null;

  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || 'Request failed');
  }

  return res.json();
}

function normalizeBooking(b) {
  return {
    id: b.id,
    client_id: b.client_id || '',
    client: b.client_name,
    event: b.event_type,
    date: b.event_date,
    time: b.event_time ? b.event_time.substring(0, 5) : '',
    guests: b.guest_count,
    package: b.package,
    venue: b.venue,
    email: b.email || '',
    phone: b.phone || '',
    alt_phone: b.alt_phone || '',
    duration: b.duration || '',
    decoration: b.decoration || 'no',
    theme: b.theme || '',
    special_requests: b.special_requests || '',
    referral: b.referral || '',
    status: b.status,
  };
}

function normalizeFeedback(f) {
  return {
    id: f.id,
    client: f.client_name,
    event_type: f.event_type || '',
    date_submitted: f.date_submitted,
    star_rating: f.star_rating,
    comments: f.comments,
    email: f.email || '',
    has_booked: f.has_booked || 'yes',
    liked_tags: f.liked_tags || '',
    status: f.status,
  };
}

async function loadPageData() {
  try {
    const [bookings, feedback] = await Promise.all([
      apiRequest('/bookings'),
      apiRequest('/feedback'),
    ]);
    MC_DATA.bookings = (bookings || []).map(normalizeBooking);
    MC_DATA.feedback = (feedback || []).map(normalizeFeedback);
  } catch (e) {
    console.error('Failed to load data:', e);
  }
}

/* ===================== SIDEBAR ===================== */

function initSidebar() {
  const toggle = document.querySelector('.mc-sidebar-toggle');
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

function setActiveNav() {
  const page = window.location.pathname.split('/').pop() || 'dashboard.php';
  document.querySelectorAll('.mc-nav-item[data-page]').forEach(item => {
    item.classList.toggle('active', item.dataset.page === page);
  });
}

/* ===================== TOAST ===================== */

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
      <a href="dashboard.php" class="mc-nav-item ${activePage === 'dashboard' ? 'active' : ''}" data-page="dashboard.php">
        <span class="mc-nav-icon"><i class="bi bi-grid-fill"></i></span> Dashboard
      </a>
      <a href="bookings.php" class="mc-nav-item ${activePage === 'bookings' ? 'active' : ''}" data-page="bookings.php">
        <span class="mc-nav-icon"><i class="bi bi-calendar-check-fill"></i></span> Bookings
        ${pendingCount ? `<span class="mc-nav-badge">${pendingCount}</span>` : ''}
      </a>
      <a href="schedule.php" class="mc-nav-item ${activePage === 'schedule' ? 'active' : ''}" data-page="schedule.php">
        <span class="mc-nav-icon"><i class="bi bi-calendar3"></i></span> Event Schedule
      </a>
      <a href="feedback.php" class="mc-nav-item ${activePage === 'feedback' ? 'active' : ''}" data-page="feedback.php">
        <span class="mc-nav-icon"><i class="bi bi-chat-square-heart-fill"></i></span> Feedback
        ${newFeedback ? `<span class="mc-nav-badge">${newFeedback}</span>` : ''}
      </a>
      <a href="reports.php" class="mc-nav-item ${activePage === 'reports' ? 'active' : ''}" data-page="reports.php">
        <span class="mc-nav-icon"><i class="bi bi-bar-chart-fill"></i></span> Reports
      </a>
    </nav>

    ${isAdmin ? `
    <nav class="mc-nav-section" style="margin-top:0.5rem;">
      <div class="mc-nav-label">Admin Only</div>
      <a href="accounts.php" class="mc-nav-item ${activePage === 'accounts' ? 'active' : ''}" data-page="accounts.php">
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

async function handleLogout() {
  try {
    await apiRequest('/auth/logout', { method: 'POST' });
  } catch (e) {
    // ignore — logout anyway
  }
  sessionStorage.clear();
  window.location.href = 'login.php';
}

function renderTopbar(title, breadcrumb) {
  const newFeedback = MC_DATA.feedback.filter(f => f.status === 'new').length;
  const user = MC_DATA.currentUser;

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
      <button class="mc-topbar-btn" title="Feedback Notifications" onclick="window.location='feedback.php'">
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
