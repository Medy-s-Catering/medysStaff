<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bookings – Medy's Catering</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .mc-status-flow { display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap; }
    .mc-flow-step {
      display:flex; align-items:center; gap:0.25rem;
      font-size:0.72rem; font-weight:700; padding:0.25rem 0.6rem;
      border-radius:20px; border:1.5px solid;
      white-space:nowrap;
    }
    .mc-flow-step.done  { background:#f0fdf4; border-color:#86efac; color:#16a34a; }
    .mc-flow-step.active{ background:#fff7ed; border-color:#fdba74; color:#ea580c; }
    .mc-flow-step.next  { background:#f8fafc; border-color:#cbd5e1; color:#94a3b8; }
    .mc-flow-arrow { color:#94a3b8; font-size:0.65rem; }

    .mc-action-btn {
      display:inline-flex; align-items:center; gap:0.3rem;
      font-size:0.73rem; font-weight:700; padding:0.28rem 0.7rem;
      border-radius:20px; border:1.5px solid; cursor:pointer;
      transition:all 0.18s; background:#fff;
      white-space:nowrap;
    }
    .mc-action-btn:hover { opacity:0.85; transform:translateY(-1px); }
    .mc-action-btn.confirm  { border-color:#16a34a; color:#16a34a; }
    .mc-action-btn.confirm:hover  { background:#f0fdf4; }
    .mc-action-btn.complete { border-color:#2563eb; color:#2563eb; }
    .mc-action-btn.complete:hover { background:#eff6ff; }
    .mc-action-btn.cancel   { border-color:#dc2626; color:#dc2626; }
    .mc-action-btn.cancel:hover   { background:#fef2f2; }
    .mc-action-btn.reopen   { border-color:#d97706; color:#d97706; }
    .mc-action-btn.reopen:hover   { background:#fffbeb; }

    .mc-status-info {
      display:flex; align-items:center; gap:0.5rem;
      background:var(--mc-bg); border-radius:8px; padding:0.6rem 0.9rem;
      font-size:0.82rem; color:var(--mc-gray);
    }
  </style>
</head>
<body>
  <div class="mc-layout">
    <div id="sidebarMount"></div>
    <div class="mc-main">
      <div id="topbarMount"></div>
      <div class="mc-content">

        <div class="mc-section-hdr mb-4">
          <div>
            <h2 style="font-size:1.4rem;font-family:'Playfair Display',serif;">Bookings</h2>
            <p style="font-size:0.82rem;color:var(--mc-gray);margin:0;">Manage all client event bookings</p>
          </div>
          <button class="btn-mc-primary" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> New Booking</button>
        </div>

        <!-- Status workflow legend -->
        <div class="mc-card mb-3" style="padding:0.85rem 1.2rem;">
          <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;font-size:0.78rem;color:var(--mc-gray);">
            <i class="bi bi-info-circle" style="color:var(--mc-red);"></i>
            <strong>Status Workflow:</strong>
            <span class="mc-flow-step done"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>Pending</span>
            <span class="mc-flow-arrow">→</span>
            <span class="mc-flow-step done"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>Confirmed</span>
            <span class="mc-flow-arrow">→</span>
            <span class="mc-flow-step done"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>Completed</span>
            <span style="margin-left:0.5rem; color:#94a3b8;">|</span>
            <span class="mc-flow-step next"><i class="bi bi-x-circle" style="font-size:0.7rem;"></i>Cancel (pending / confirmed only)</span>
            <span style="margin-left:0.5rem; color:#94a3b8;">|</span>
            <span class="mc-flow-step next"><i class="bi bi-arrow-counterclockwise" style="font-size:0.7rem;"></i>Reopen cancelled</span>
            <span style="margin-left:0.5rem; color:#94a3b8;">|</span>
            <span class="mc-flow-step" style="background:#f1f5f9;border-color:#cbd5e1;color:#64748b;"><i class="bi bi-lock-fill" style="font-size:0.7rem;"></i>Completed = locked</span>
          </div>
        </div>

        <div class="mc-card mb-4">
          <div class="row g-2 align-items-center">
            <div class="col-md-4">
              <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute;left:0.8rem;top:50%;transform:translateY(-50%);color:#aaa;"></i>
                <input type="text" id="searchInput" class="mc-input" placeholder="Search client, event..." style="padding-left:2.3rem;" oninput="filterTable()" />
              </div>
            </div>
            <div class="col-6 col-md-2">
              <select id="statusFilter" class="mc-input" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select id="packageFilter" class="mc-input" onchange="filterTable()">
                <option value="">All Packages</option>
                <option value="Basic">Basic</option>
                <option value="Standard">Standard</option>
                <option value="Premium">Premium</option>
              </select>
            </div>
            <div class="col-md-2">
              <input type="date" id="dateFilter" class="mc-input" onchange="filterTable()" title="Filter by date" />
            </div>
            <div class="col-md-2">
              <button class="btn-mc-ghost w-100" onclick="clearFilters()"><i class="bi bi-x-circle"></i> Clear</button>
            </div>
          </div>
        </div>

        <div class="mc-card">
          <div class="mc-table-wrap">
            <table class="mc-table" id="bookingsTable">
              <thead>
                <tr>
                  <th>Client</th>
                  <th>Event</th>
                  <th>Date</th>
                  <th>Guests</th>
                  <th>Package</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="bookingsTbody"></tbody>
            </table>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding-top:1rem;font-size:0.82rem;color:var(--mc-gray);">
            <span id="tableCount"></span>
            <span>Showing all records</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ADD / EDIT BOOKING MODAL -->
  <div class="mc-modal-overlay" id="addBookingModal">
    <div class="mc-modal">
      <div class="mc-modal-header">
        <h5 style="font-family:'Playfair Display',serif;margin:0;" id="modalTitle">New Booking</h5>
        <button class="btn-mc-icon" onclick="closeModal('addBookingModal')"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="mc-modal-body">
        <form id="bookingForm">
          <input type="hidden" id="editId" />
          <input type="hidden" id="currentStatus" value="pending" />

          <!-- Status indicator (edit mode only) -->
          <div id="statusIndicator" class="d-none mb-3">
            <div class="mc-status-info">
              <i class="bi bi-info-circle-fill" style="color:var(--mc-red);"></i>
              <span>Current status: <strong id="statusLabel"></strong></span>
              <span style="color:#94a3b8;">·</span>
              <span id="statusHint" style="font-style:italic;"></span>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="mc-label">Client Name *</label>
              <input type="text" id="fClient" class="mc-input" placeholder="Full name" required />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Event Type *</label>
              <select id="fEvent" class="mc-input" required>
                <option value="">Select type...</option>
                <option>Corporate Event</option>
                <option>Wedding / Reception</option>
                <option>Birthday / Debut</option>
                <option>School Activity</option>
                <option>Family Reunion</option>
                <option>Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="mc-label">Event Date *</label>
              <input type="date" id="fDate" class="mc-input" required />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Event Time</label>
              <input type="time" id="fTime" class="mc-input" />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Number of Guests *</label>
              <input type="number" id="fGuests" class="mc-input" placeholder="e.g. 100" min="1" required />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Package *</label>
              <select id="fPackage" class="mc-input" required>
                <option value="">Select package...</option>
                <option>Basic</option>
                <option>Standard</option>
                <option>Premium</option>
                <option>Custom</option>
              </select>
            </div>
            <div class="col-12">
              <label class="mc-label">Venue / Location *</label>
              <input type="text" id="fVenue" class="mc-input" placeholder="Full address of event venue" required />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Client Email</label>
              <input type="email" id="fEmail" class="mc-input" placeholder="client@email.com" />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Client Phone</label>
              <input type="tel" id="fPhone" class="mc-input" placeholder="09XX XXX XXXX" />
            </div>
            <div class="col-md-6">
              <label class="mc-label">Event Duration</label>
              <select id="fDuration" class="mc-input">
                <option value="">Select duration...</option>
                <option>2 hours</option>
                <option>4 hours</option>
                <option>6 hours</option>
                <option>8 hours</option>
                <option>Full day</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="mc-label">Venue Decoration</label>
              <select id="fDecoration" class="mc-input">
                <option value="no">No</option>
                <option value="yes">Yes – include in package</option>
                <option value="discuss">Discuss with team</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="mc-label">Theme / Color Preference</label>
              <input type="text" id="fTheme" class="mc-input" placeholder="e.g. Red &amp; Gold" />
            </div>
            <div class="col-12">
              <label class="mc-label">Special Requests / Dietary Restrictions</label>
              <textarea id="fSpecialRequests" class="mc-input" rows="2" placeholder="Allergies, special dishes, additional requests..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="mc-modal-footer">
        <button class="btn-mc-ghost" onclick="closeModal('addBookingModal')">Cancel</button>
        <button class="btn-mc-primary" id="saveBtn" onclick="saveBooking()"><i class="bi bi-check-lg"></i> Save Booking</button>
      </div>
    </div>
  </div>

  <!-- VIEW BOOKING MODAL -->
  <div class="mc-modal-overlay" id="viewModal">
    <div class="mc-modal">
      <div class="mc-modal-header">
        <h5 style="font-family:'Playfair Display',serif;margin:0;">Booking Details</h5>
        <button class="btn-mc-icon" onclick="closeModal('viewModal')"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="mc-modal-body" id="viewModalBody"></div>
      <div class="mc-modal-footer">
        <button class="btn-mc-ghost" onclick="closeModal('viewModal')">Close</button>
      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>

    // Status workflow configuration
    const STATUS_FLOW = {
      pending:   { next: ['confirmed'], cancel: true,  reopen: false },
      confirmed: { next: ['completed'], cancel: true,  reopen: false },
      completed: { next: [],            cancel: false, reopen: false },
      cancelled: { next: [],            cancel: false, reopen: true  },
    };

    const STATUS_LABELS = {
      pending:   'Pending',
      confirmed: 'Confirmed',
      completed: 'Completed',
      cancelled: 'Cancelled',
    };

    const STATUS_HINTS = {
      pending:   'Can be confirmed or cancelled.',
      confirmed: 'Can be marked completed or cancelled.',
      completed: 'This booking is completed and locked.',
      cancelled: 'This booking was cancelled. It can be reopened.',
    };

    const NEXT_ICONS = {
      confirmed: 'bi-check-circle-fill',
      completed: 'bi-check2-all',
    };

    document.addEventListener('DOMContentLoaded', async () => {
      await loadPageData();
      document.getElementById('sidebarMount').outerHTML = renderSidebar('bookings');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Bookings', "Medy's Catering › Bookings");
      initSidebar();
      renderTable(MC_DATA.bookings);
    });

    // ── RENDER TABLE ────────────────────────────────────────────────────────
    function renderTable(data) {
      document.getElementById('tableCount').textContent = `${data.length} booking(s)`;
      document.getElementById('bookingsTbody').innerHTML = data.length
        ? data.map(b => {
          const locked = b.status == 'completed';
          return `
          <tr style="${locked ? 'opacity:0.75;' : ''}">
            <td>
              <div style="font-weight:600;">${b.client}</div>
              <div style="font-size:0.75rem;color:var(--mc-gray);">ID: ${b.client_id || '—'}</div>
            </td>
            <td style="color:var(--mc-gray);font-size:0.87rem;">${b.event}</td>
            <td style="white-space:nowrap;">${fmtDate(b.date)}<br><span style="font-size:0.75rem;color:var(--mc-gray);">${b.time || ''}</span></td>
            <td>${b.guests}</td>
            <td>${b.package}</td>
            <td>
              ${statusBadge(b.status)}
              ${locked ? '<div style="font-size:0.7rem;color:#64748b;margin-top:0.2rem;"><i class="bi bi-lock-fill"></i> Locked</div>' : ''}
            </td>
            <td style="white-space:nowrap;">
              ${renderStatusActions(b)}
              <span style="display:inline-flex;gap:0.2rem;margin-top:0.3rem;">
                <button class="btn-mc-icon" onclick="viewBooking('${b.id}')" title="View details"><i class="bi bi-eye-fill"></i></button>
                ${!locked ? `<button class="btn-mc-icon" onclick="editBooking('${b.id}')" title="Edit booking"><i class="bi bi-pencil-fill"></i></button>` : ''}
                <button class="btn-mc-icon danger" onclick="deleteBooking('${b.id}')" title="Delete"><i class="bi bi-trash-fill"></i></button>
              </span>
            </td>
          </tr>`;}).join('')
        : `<tr><td colspan="7"><div class="mc-empty"><div class="mc-empty-icon"><i class="bi bi-calendar-x"></i></div><p>No bookings found.</p></div></td></tr>`;
    }

    // ── STATUS ACTION BUTTONS ────────────────────────────────────────────────
    function renderStatusActions(b) {
      const flow = STATUS_FLOW[b.status] || {};
      const btns = [];

      // Next-step button (Confirm / Complete)
      if (flow.next && flow.next.length) {
        const next = flow.next[0];
        const label = STATUS_LABELS[next];
        const icon  = NEXT_ICONS[next] || 'bi-arrow-right-circle';
        const cls   = next == 'confirmed' ? 'confirm' : 'complete';
        btns.push(`<button class="mc-action-btn ${cls}" onclick="changeStatus(${b.id},'${next}')"><i class="bi ${icon}"></i>${label}</button>`);
      }

      // Cancel button
      if (flow.cancel) {
        btns.push(`<button class="mc-action-btn cancel" onclick="changeStatus(${b.id},'cancelled')"><i class="bi bi-x-circle"></i>Cancel</button>`);
      }

      // Reopen button
      if (flow.reopen) {
        btns.push(`<button class="mc-action-btn reopen" onclick="changeStatus(${b.id},'pending')"><i class="bi bi-arrow-counterclockwise"></i>Reopen</button>`);
      }

      return btns.length
        ? `<div class="mc-status-flow mb-1">${btns.join('')}</div>`
        : '';
    }

    // ── QUICK STATUS CHANGE ──────────────────────────────────────────────────
    async function changeStatus(id, newStatus) {
      const b = MC_DATA.bookings.find(x => x.id == id);
      if (!b) return;

      const confirmMsg = {
        confirmed: `Confirm booking for ${b.client}?`,
        completed: `Mark booking for ${b.client} as COMPLETED? This cannot be undone.`,
        cancelled: `Cancel booking for ${b.client}?`,
        pending:   `Reopen cancelled booking for ${b.client}?`,
      };

      if (!confirm(confirmMsg[newStatus] || `Change status to ${newStatus}?`)) return;

      try {
        await apiRequest('/bookings/' + id, {
          method: 'PUT',
          body: JSON.stringify({
            client_name:      b.client,
            email:            b.email,
            phone:            b.phone,
            event_type:       b.event,
            event_date:       b.date,
            event_time:       b.time || null,
            guest_count:      b.guests,
            package:          b.package,
            venue:            b.venue,
            status:           newStatus,
            duration:         b.duration || null,
            decoration:       b.decoration || 'no',
            theme:            b.theme || null,
            special_requests: b.special_requests || null,
          }),
        });
        await loadPageData();
        renderTable(applyFilters());
        showToast(`Status updated to ${STATUS_LABELS[newStatus]}!`);
      } catch (e) {
        showToast(e.message || 'Failed to update status.', 'error');
      }
    }

    // ── FILTERS ──────────────────────────────────────────────────────────────
    function applyFilters() {
      const s  = document.getElementById('searchInput').value.toLowerCase();
      const st = document.getElementById('statusFilter').value;
      const pk = document.getElementById('packageFilter').value;
      const dt = document.getElementById('dateFilter').value;
      return MC_DATA.bookings.filter(b =>
        (!s  || b.client.toLowerCase().includes(s) || b.event.toLowerCase().includes(s)) &&
        (!st || b.status  == st) &&
        (!pk || b.package == pk) &&
        (!dt || b.date    == dt));
    }

    function filterTable()  { renderTable(applyFilters()); }

    function clearFilters() {
      ['searchInput','statusFilter','packageFilter','dateFilter'].forEach(id => document.getElementById(id).value = '');
      renderTable(MC_DATA.bookings);
    }

    // ── VIEW MODAL ───────────────────────────────────────────────────────────
    function viewBooking(id) {
      const b = MC_DATA.bookings.find(x => x.id == id);
      if (!b) return;
      const fields = [
        ['Client ID', b.client_id || '—'], ['Client', b.client], ['Event Type', b.event],
        ['Date', fmtDate(b.date)], ['Time', b.time || '—'], ['Duration', b.duration || '—'],
        ['Guests', b.guests], ['Package', b.package], ['Venue', b.venue],
        ['Email', b.email || '—'], ['Phone', b.phone || '—'],
        ['Decoration', b.decoration || '—'], ['Theme', b.theme || '—'],
        ['Special Requests', b.special_requests || '—'],
      ];
      document.getElementById('viewModalBody').innerHTML = `
        <div style="display:grid;gap:1rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <code style="background:var(--mc-bg);padding:4px 10px;border-radius:6px;">#${b.id}</code>
            ${statusBadge(b.status)}
          </div>
          <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">${renderStatusActions(b)}</div>
          ${fields.map(([l,v]) => `
            <div style="border-bottom:1px solid var(--mc-border);padding-bottom:0.75rem;">
              <div style="font-size:0.75rem;color:var(--mc-gray);font-weight:600;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:0.2rem;">${l}</div>
              <div style="font-weight:500;">${v}</div>
            </div>`).join('')}
        </div>`;
      openModal('viewModal');
    }

    // ── ADD / EDIT MODAL ─────────────────────────────────────────────────────
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'New Booking';
      document.getElementById('editId').value = '';
      document.getElementById('currentStatus').value = 'pending';
      document.getElementById('bookingForm').reset();
      document.getElementById('statusIndicator').classList.add('d-none');
      document.getElementById('saveBtn').disabled = false;
      openModal('addBookingModal');
    }

    function editBooking(id) {
      const b = MC_DATA.bookings.find(x => x.id == id);
      if (!b) return;

      document.getElementById('modalTitle').textContent = 'Edit Booking';
      document.getElementById('editId').value = id;
      document.getElementById('currentStatus').value = b.status;

      // Populate fields
      document.getElementById('fClient').value          = b.client;
      document.getElementById('fEvent').value           = b.event;
      document.getElementById('fDate').value            = b.date;
      document.getElementById('fTime').value            = b.time || '';
      document.getElementById('fGuests').value          = b.guests;
      document.getElementById('fPackage').value         = b.package;
      document.getElementById('fVenue').value           = b.venue;
      document.getElementById('fEmail').value           = b.email || '';
      document.getElementById('fPhone').value           = b.phone || '';
      document.getElementById('fDuration').value        = b.duration || '';
      document.getElementById('fDecoration').value      = b.decoration || 'no';
      document.getElementById('fTheme').value           = b.theme || '';
      document.getElementById('fSpecialRequests').value = b.special_requests || '';

      // Show status indicator
      document.getElementById('statusIndicator').classList.remove('d-none');
      document.getElementById('statusLabel').textContent = STATUS_LABELS[b.status] || b.status;
      document.getElementById('statusHint').textContent  = STATUS_HINTS[b.status]  || '';

      // Disable save on completed (use status buttons instead)
      const isLocked = b.status == 'completed';
      document.getElementById('saveBtn').disabled = isLocked;
      if (isLocked) {
        document.getElementById('saveBtn').title = 'Completed bookings cannot be edited.';
      }

      openModal('addBookingModal');
    }

    // ── SAVE ─────────────────────────────────────────────────────────────────
    async function saveBooking() {
      const id     = document.getElementById('editId').value;
      const status = document.getElementById('currentStatus').value;

      const data = {
        client_name:      document.getElementById('fClient').value.trim(),
        event_type:       document.getElementById('fEvent').value,
        event_date:       document.getElementById('fDate').value,
        event_time:       document.getElementById('fTime').value || null,
        guest_count:      parseInt(document.getElementById('fGuests').value) || 0,
        package:          document.getElementById('fPackage').value,
        venue:            document.getElementById('fVenue').value.trim(),
        email:            document.getElementById('fEmail').value.trim(),
        phone:            document.getElementById('fPhone').value.trim(),
        duration:         document.getElementById('fDuration').value || null,
        decoration:       document.getElementById('fDecoration').value,
        theme:            document.getElementById('fTheme').value.trim() || null,
        special_requests: document.getElementById('fSpecialRequests').value.trim() || null,
        status:           status,
      };

      if (!data.client_name || !data.event_type || !data.event_date || !data.package || !data.venue) {
        showToast('Please fill in all required fields.', 'error');
        return;
      }

      try {
        if (id) {
          await apiRequest('/bookings/' + id, { method: 'PUT', body: JSON.stringify(data) });
          showToast('Booking updated!');
        } else {
          await apiRequest('/bookings', { method: 'POST', body: JSON.stringify(data) });
          showToast('New booking added!');
        }
        await loadPageData();
        closeModal('addBookingModal');
        renderTable(applyFilters());
      } catch (e) {
        showToast(e.message || 'Failed to save booking.', 'error');
      }
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    async function deleteBooking(id) {
      if (!confirm('Delete this booking? This cannot be undone.')) return;
      try {
        await apiRequest('/bookings/' + id, { method: 'DELETE' });
        await loadPageData();
        renderTable(applyFilters());
        showToast('Booking deleted.');
      } catch (e) {
        showToast(e.message || 'Failed to delete booking.', 'error');
      }
    }
  </script>
</body>
</html>
