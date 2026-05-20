<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Accounts – Medy's Catering</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <div class="mc-layout">
    <div id="sidebarMount"></div>
    <div class="mc-main">
      <div id="topbarMount"></div>
      <div class="mc-content">

        <div class="alert d-none" id="adminGuard" style="background:#fef2f2;color:#991b1b;border:1.5px solid #fca5a5;border-radius:10px;font-size:0.88rem;">
          <i class="bi bi-lock-fill me-2"></i><strong>Access Restricted.</strong> This page is for administrators only.
        </div>

        <div class="mc-section-hdr mb-4">
          <div><h2 style="font-size:1.4rem;font-family:'Playfair Display',serif;">Staff Accounts</h2><p style="font-size:0.82rem;color:var(--mc-gray);margin:0;">Manage system user accounts and roles</p></div>
          <button class="btn-mc-primary" onclick="openAddModal()"><i class="bi bi-person-plus-fill"></i> Add Staff</button>
        </div>

        <div class="row g-3" id="staffGrid"></div>

      </div>
    </div>
  </div>

  <!-- ADD/EDIT ACCOUNT MODAL -->
  <div class="mc-modal-overlay" id="addAccountModal">
    <div class="mc-modal">
      <div class="mc-modal-header">
        <h5 style="font-family:'Playfair Display',serif;margin:0;" id="accModalTitle">Add Staff Account</h5>
        <button class="btn-mc-icon" onclick="closeModal('addAccountModal')"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="mc-modal-body">
        <div class="row g-3">
          <input type="hidden" id="accEditId" />
          <div class="col-md-6"><label class="mc-label">Full Name *</label><input type="text" id="accName" class="mc-input" placeholder="Full name" /></div>
          <div class="col-md-6"><label class="mc-label">Username *</label><input type="text" id="accUsername" class="mc-input" placeholder="Login username" /></div>
          <div class="col-md-6"><label class="mc-label">Email</label><input type="email" id="accEmail" class="mc-input" placeholder="email@example.com" /></div>
          <div class="col-md-6"><label class="mc-label">Role *</label><select id="accRole" class="mc-input"><option value="staff">Staff</option><option value="admin">Admin</option></select></div>
          <div class="col-md-6"><label class="mc-label" id="accPasswordLabel">Password *</label><input type="password" id="accPassword" class="mc-input" placeholder="Set password" /></div>
          <div class="col-md-6"><label class="mc-label">Status</label><select id="accStatus" class="mc-input"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
      </div>
      <div class="mc-modal-footer">
        <button class="btn-mc-ghost" onclick="closeModal('addAccountModal')">Cancel</button>
        <button class="btn-mc-primary" onclick="saveAccount()"><i class="bi bi-check-lg"></i> Save Account</button>
      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>
    let MC_STAFF = [];

    function normalizeStaff(u) {
      return { id:u.id, name:u.full_name, username:u.username, email:u.email||'', role:u.role, status:u.status };
    }

    function initials(name) {
      return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
    }

    function renderStaff() {
      const grid = document.getElementById('staffGrid');
      if (!MC_STAFF.length) { grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--mc-gray);font-size:0.9rem;">No staff accounts found.</div>'; return; }
      grid.innerHTML = MC_STAFF.map(s => `
        <div class="col-md-6 col-lg-4 col-xl-3">
          <div class="mc-card text-center" style="border:${s.status=='inactive'?'1.5px dashed #e5e7eb':''};opacity:${s.status=='inactive'?'0.7':''}">
            <div style="width:64px;height:64px;border-radius:50%;background:${s.role=='admin'?'var(--mc-red)':'var(--mc-blue)'};display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;color:#fff;margin:0 auto 1rem;font-family:'Playfair Display',serif;">${initials(s.name)}</div>
            <div style="font-weight:700;font-size:1rem;margin-bottom:0.2rem;">${s.name}</div>
            <div style="font-size:0.8rem;color:var(--mc-gray);margin-bottom:0.6rem;">@${s.username}</div>
            <div style="font-size:0.78rem;color:var(--mc-gray);margin-bottom:0.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${s.email}">${s.email||'—'}</div>
            <div style="display:flex;gap:0.4rem;justify-content:center;margin-bottom:0.9rem;">
              <span class="mc-badge ${s.role=='admin'?'mc-badge-admin':'mc-badge-staff'}">${s.role=='admin'?'Admin':'Staff'}</span>
              <span class="mc-badge ${s.status=='active'?'mc-badge-confirmed':'mc-badge-cancelled'}">${s.status}</span>
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:center;">
              <button class="btn-mc-ghost" onclick="editAccount(${s.id})"><i class="bi bi-pencil-fill"></i> Edit</button>
              ${s.id != MC_DATA.currentUser.id ? `<button class="btn-mc-icon danger" onclick="deleteAccount(${s.id})" title="Remove"><i class="bi bi-trash-fill"></i></button>` : ''}
            </div>
          </div>
        </div>`).join('');
    }

    async function loadAccounts() {
      const grid = document.getElementById('staffGrid');
      grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--mc-gray);font-size:0.9rem;"><span class="spinner-border spinner-border-sm me-2"></span>Loading accounts…</div>';
      try {
        const data = await apiRequest('/accounts');
        MC_STAFF = (data || []).map(normalizeStaff);
        renderStaff();
      } catch (e) {
        grid.innerHTML = `<div class="col-12 text-center py-5" style="color:#dc2626;font-size:0.9rem;"><i class="bi bi-exclamation-circle me-2"></i>${e.message || 'Failed to load accounts.'}</div>`;
      }
    }

    function openAddModal() {
      document.getElementById('accModalTitle').textContent = 'Add Staff Account';
      document.getElementById('accEditId').value = '';
      ['accName','accUsername','accEmail','accPassword'].forEach(id => document.getElementById(id).value = '');
      document.getElementById('accRole').value = 'staff';
      document.getElementById('accStatus').value = 'active';
      document.getElementById('accPasswordLabel').textContent = 'Password *';
      openModal('addAccountModal');
    }

    function editAccount(id) {
      const s = MC_STAFF.find(x => x.id == id);
      if (!s) return;
      document.getElementById('accModalTitle').textContent = 'Edit Staff Account';
      document.getElementById('accEditId').value = id;
      document.getElementById('accName').value = s.name;
      document.getElementById('accUsername').value = s.username;
      document.getElementById('accEmail').value = s.email;
      document.getElementById('accRole').value = s.role;
      document.getElementById('accStatus').value = s.status;
      document.getElementById('accPassword').value = '';
      document.getElementById('accPasswordLabel').textContent = 'Password (leave blank to keep)';
      openModal('addAccountModal');
    }

    async function saveAccount() {
      const id        = document.getElementById('accEditId').value;
      const full_name = document.getElementById('accName').value.trim();
      const username  = document.getElementById('accUsername').value.trim();
      const password  = document.getElementById('accPassword').value;
      if (!full_name || !username) { showToast('Name and username are required.', 'error'); return; }
      if (!id && !password) { showToast('Password is required for new accounts.', 'error'); return; }
      const payload = { full_name, username, email:document.getElementById('accEmail').value.trim()||null, role:document.getElementById('accRole').value, status:document.getElementById('accStatus').value };
      if (password) payload.password = password;
      try {
        if (id) { await apiRequest('/accounts/' + id, { method:'PUT', body:JSON.stringify(payload) }); showToast('Account updated!'); }
        else    { await apiRequest('/accounts', { method:'POST', body:JSON.stringify(payload) }); showToast('Staff account created!'); }
        closeModal('addAccountModal');
        await loadAccounts();
      } catch (e) { showToast(e.message || 'Failed to save account.', 'error'); }
    }

    async function deleteAccount(id) {
      if (!confirm('Remove this staff account?')) return;
      try { await apiRequest('/accounts/' + id, { method:'DELETE' }); showToast('Account removed.'); await loadAccounts(); }
      catch (e) { showToast(e.message || 'Failed to delete account.', 'error'); }
    }

    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('sidebarMount').outerHTML = renderSidebar('accounts');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Staff Accounts', "Medy's Catering › Accounts");
      initSidebar();
      if (MC_DATA.currentUser.role != 'admin') {
        document.getElementById('adminGuard').classList.remove('d-none');
        return;
      }
      await loadAccounts();
    });
  </script>
</body>
</html>
