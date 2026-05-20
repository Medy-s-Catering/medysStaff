<?php
session_start();
// Clear any stale server-side session so the login form always works
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – Medy's Catering Staff Portal</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .login-divider { display:flex;align-items:center;gap:0.75rem;margin:1.25rem 0; }
    .login-divider span { font-size:0.78rem;color:#aaa;white-space:nowrap; }
    .login-divider::before,.login-divider::after { content:'';flex:1;height:1px;background:#e5e7eb; }
    .pw-toggle { position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#aaa;cursor:pointer;font-size:1rem; }
    .pw-toggle:hover { color:var(--mc-red); }
    .role-tab { flex:1;padding:0.55rem;text-align:center;border-radius:8px;cursor:pointer;font-size:0.88rem;font-weight:600;transition:all 0.2s;color:#aaa; }
    .role-tab.active { background:var(--mc-red);color:#fff;box-shadow:0 3px 10px rgba(192,57,43,0.3); }
  </style>
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <div class="login-logo-icon"><i class="bi bi-award-fill"></i></div>
        <h2>Medy's Catering</h2>
        <p>Staff Management Portal</p>
      </div>

      <div style="display:flex;gap:4px;background:#f3f4f6;border-radius:10px;padding:4px;margin-bottom:1.5rem;">
        <div class="role-tab active" onclick="selectRole('admin',this)">Admin</div>
        <div class="role-tab" onclick="selectRole('staff',this)">Staff</div>
      </div>

      <form id="loginForm" onsubmit="handleLogin(event)">
        <div class="mb-3">
          <label class="mc-label">Username</label>
          <input type="text" id="username" class="mc-input" placeholder="Enter your username" required />
        </div>
        <div class="mb-1 position-relative">
          <label class="mc-label">Password</label>
          <div style="position:relative;">
            <input type="password" id="password" class="mc-input" placeholder="Enter your password" style="padding-right:2.5rem;" required />
            <button type="button" class="pw-toggle" onclick="togglePw()"><i class="bi bi-eye-fill" id="pwIcon"></i></button>
          </div>
        </div>
        <div id="loginError" class="alert alert-danger py-2 d-none" style="font-size:0.85rem;"></div>
        <button type="submit" class="btn-mc-primary w-100 justify-content-center" style="padding:0.75rem;margin-top:20px;">
          <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
      </form>
    </div>
  </div>

  <script>
    let selectedRole = 'admin';

    function selectRole(role, el) {
      selectedRole = role;
      document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
      el.classList.add('active');
      document.getElementById('loginError').classList.add('d-none');
    }

    function togglePw() {
      const pw   = document.getElementById('password');
      const icon = document.getElementById('pwIcon');
      const show = pw.type == 'password';
      pw.type        = show ? 'text' : 'password';
      icon.className = show ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    }

    async function handleLogin(e) {
      e.preventDefault();
      const u   = document.getElementById('username').value.trim();
      const p   = document.getElementById('password').value;
      const err = document.getElementById('loginError');
      const btn = document.querySelector('[type="submit"]');

      if (btn) btn.disabled = true;
      err.classList.add('d-none');

      try {
        const res = await fetch('/medysStaff/api/auth_login.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ username: u, password: p }),
        });

        const data = await res.json();

        if (!res.ok) {
          err.textContent = data.message || 'Invalid username or password.';
          err.classList.remove('d-none');
          document.getElementById('password').value = '';
          return;
        }

        if (data.user.role != selectedRole) {
          err.textContent = `This account is not registered as ${selectedRole}.`;
          err.classList.remove('d-none');
          document.getElementById('password').value = '';
          return;
        }

        sessionStorage.clear();
        sessionStorage.setItem('mc_token', data.token);
        sessionStorage.setItem('mc_user', JSON.stringify(data.user));
        window.location.href = 'dashboard.php';
      } catch (error) {
        err.textContent = 'Network error. Please try again.';
        err.classList.remove('d-none');
      } finally {
        if (btn) btn.disabled = false;
      }
    }

    (function checkExistingSession() {
      if (sessionStorage.getItem('mc_user')) {
        window.location.replace('dashboard.php');
      }
    })();
  </script>
</body>
</html>
