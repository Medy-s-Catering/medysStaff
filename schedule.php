<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Schedule – Medy's Catering</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .mc-view-toggle button{border:1.5px solid var(--mc-border);background:#fff;border-radius:8px;padding:0.4rem 0.9rem;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.2s;color:var(--mc-gray)}
    .mc-view-toggle button.active{background:var(--mc-red);color:#fff;border-color:var(--mc-red)}
    .mc-legend-dot{width:10px;height:10px;border-radius:3px;display:inline-block;flex-shrink:0}
    .mc-upcoming-item{display:flex;gap:0.9rem;padding:0.9rem 0;border-bottom:1px solid var(--mc-border);align-items:flex-start}
    .mc-date-badge{background:var(--mc-red);color:#fff;border-radius:10px;padding:0.4rem 0.6rem;text-align:center;min-width:44px;flex-shrink:0}
    .mc-date-badge .day{font-size:1.2rem;font-weight:700;line-height:1;font-family:'Playfair Display',serif}
    .mc-date-badge .month{font-size:0.6rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;opacity:0.85}
  </style>
</head>
<body>
  <div class="mc-layout">
    <div id="sidebarMount"></div>
    <div class="mc-main">
      <div id="topbarMount"></div>
      <div class="mc-content">

        <div class="mc-section-hdr mb-4">
          <div><h2 style="font-size:1.4rem;font-family:'Playfair Display',serif;">Event Schedule</h2><p style="font-size:0.82rem;color:var(--mc-gray);margin:0;">View and manage upcoming event calendar</p></div>
          <div class="d-flex gap-2 flex-wrap">
            <div class="mc-view-toggle d-flex gap-1">
              <button class="active" onclick="switchView('calendar',this)"><i class="bi bi-calendar3"></i> Calendar</button>
              <button onclick="switchView('list',this)"><i class="bi bi-list-ul"></i> List</button>
            </div>
            <button class="btn-mc-primary" onclick="openModal('addBookingModal')"><i class="bi bi-plus-lg"></i> New Event</button>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="mc-card" id="calendarView">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
                <button class="btn-mc-icon" onclick="changeMonth(-1)" style="width:36px;height:36px;border:1.5px solid var(--mc-border);border-radius:9px;font-size:1rem;cursor:pointer;background:#fff;" title="Previous"><i class="bi bi-chevron-left"></i></button>
                <h5 style="font-family:'Playfair Display',serif;margin:0;" id="calMonthLabel"></h5>
                <button class="btn-mc-icon" onclick="changeMonth(1)" style="width:36px;height:36px;border:1.5px solid var(--mc-border);border-radius:9px;font-size:1rem;cursor:pointer;background:#fff;" title="Next"><i class="bi bi-chevron-right"></i></button>
              </div>
              <div class="mc-calendar-grid" id="calDayHeaders"></div>
              <div class="mc-calendar-grid mt-1" id="calGrid"></div>
            </div>
            <div class="mc-card" id="listView" style="display:none;">
              <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.2rem;">All Upcoming Events</h5>
              <div id="listViewBody"></div>
            </div>
          </div>

          <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="mc-card" id="dayDetailCard">
              <h6 style="font-weight:700;margin-bottom:0.75rem;" id="dayDetailTitle">Select a date</h6>
              <div id="dayDetailBody"><div class="mc-empty" style="padding:1.5rem 0;"><div class="mc-empty-icon"><i class="bi bi-calendar-event"></i></div><p>Click on a date to see events</p></div></div>
            </div>
            <div class="mc-card"><h6 style="font-weight:700;margin-bottom:0.9rem;">This Month Summary</h6><div id="monthSummary"></div></div>
            <div class="mc-card">
              <h6 style="font-weight:700;margin-bottom:0.75rem;">Event Types</h6>
              <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.85rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;"><span class="mc-legend-dot" style="background:var(--mc-red);"></span>Wedding / Reception</div>
                <div style="display:flex;align-items:center;gap:0.5rem;"><span class="mc-legend-dot" style="background:var(--mc-blue);"></span>Corporate Event</div>
                <div style="display:flex;align-items:center;gap:0.5rem;"><span class="mc-legend-dot" style="background:var(--mc-gold);"></span>Birthday / Debut &amp; Reunion</div>
                <div style="display:flex;align-items:center;gap:0.5rem;"><span class="mc-legend-dot" style="background:var(--mc-green);"></span>School Activity / Other</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="mc-modal-overlay" id="addBookingModal">
    <div class="mc-modal">
      <div class="mc-modal-header">
        <h5 style="font-family:'Playfair Display',serif;margin:0;">Add Event to Schedule</h5>
        <button class="btn-mc-icon" onclick="closeModal('addBookingModal')"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="mc-modal-body">
        <p style="font-size:0.85rem;color:var(--mc-gray);">For full booking details, go to the <a href="bookings.php" style="color:var(--mc-red);">Bookings page</a>.</p>
        <div class="row g-3">
          <div class="col-12"><label class="mc-label">Client Name</label><input class="mc-input" id="qClient" placeholder="Client name" /></div>
          <div class="col-md-6"><label class="mc-label">Event Type</label><select class="mc-input" id="qType"><option>Corporate Event</option><option>Wedding / Reception</option><option>Birthday / Debut</option><option>School Activity</option><option>Family Reunion</option><option>Other</option></select></div>
          <div class="col-md-6"><label class="mc-label">Date</label><input type="date" class="mc-input" id="qDate" /></div>
          <div class="col-md-6"><label class="mc-label">Guests</label><input type="number" class="mc-input" id="qGuests" placeholder="100" /></div>
          <div class="col-md-6"><label class="mc-label">Package</label><select class="mc-input" id="qPackage"><option>Basic</option><option>Standard</option><option>Premium</option><option>Custom</option></select></div>
          <div class="col-md-6"><label class="mc-label">Event Time</label><input type="time" class="mc-input" id="qTime" /></div>
          <div class="col-12"><label class="mc-label">Venue</label><input class="mc-input" id="qVenue" placeholder="Event venue address" /></div>
        </div>
      </div>
      <div class="mc-modal-footer">
        <button class="btn-mc-ghost" onclick="closeModal('addBookingModal')">Cancel</button>
        <button class="btn-mc-primary" onclick="quickAddEvent()"><i class="bi bi-check-lg"></i> Add Event</button>
      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>
    let currentMonth = new Date().getMonth(), currentYear = new Date().getFullYear();
    const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function eventColor(type) {
      if (!type) return 'var(--mc-red)';
      const t = type.toLowerCase();
      if (t.includes('wedding') || t.includes('reception')) return 'var(--mc-red)';
      if (t.includes('corporate') || t.includes('seminar') || t.includes('company') || t.includes('anniversary')) return 'var(--mc-blue)';
      if (t.includes('birthday') || t.includes('debut') || t.includes('reunion') || t.includes('social')) return 'var(--mc-gold)';
      return 'var(--mc-green)';
    }

    function renderCalendar() {
      document.getElementById('calMonthLabel').textContent = `${MONTHS[currentMonth]} ${currentYear}`;
      document.getElementById('calDayHeaders').innerHTML = DAYS.map(d => `<div class="mc-cal-day-header">${d}</div>`).join('');
      const first = new Date(currentYear, currentMonth, 1).getDay();
      const days  = new Date(currentYear, currentMonth + 1, 0).getDate();
      const prevDays = new Date(currentYear, currentMonth, 0).getDate();
      const today = new Date();
      let cells = '';
      for (let i = first - 1; i >= 0; i--) cells += `<div class="mc-cal-cell other-month"><div class="mc-cal-date">${prevDays - i}</div></div>`;
      for (let d = 1; d <= days; d++) {
        const dateStr = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isToday = today.getDate() == d && today.getMonth() == currentMonth && today.getFullYear() == currentYear;
        const events  = MC_DATA.bookings.filter(b => b.date == dateStr && b.status != 'cancelled');
        const evHTML  = events.map(e => `<div class="mc-cal-event" style="background:${eventColor(e.event)};" title="${e.client}: ${e.event}" onclick="showDayDetail('${dateStr}')">${e.client}</div>`).join('');
        cells += `<div class="mc-cal-cell${isToday ? ' today' : ''}" onclick="showDayDetail('${dateStr}')"><div class="mc-cal-date">${d}</div>${evHTML}</div>`;
      }
      const total = first + days, rem = total % 7 ? 7 - (total % 7) : 0;
      for (let i = 1; i <= rem; i++) cells += `<div class="mc-cal-cell other-month"><div class="mc-cal-date">${i}</div></div>`;
      document.getElementById('calGrid').innerHTML = cells;
      renderMonthSummary();
    }

    function changeMonth(dir) {
      currentMonth += dir;
      if (currentMonth > 11) { currentMonth = 0; currentYear++; }
      if (currentMonth < 0)  { currentMonth = 11; currentYear--; }
      renderCalendar();
    }

    function showDayDetail(dateStr) {
      const events = MC_DATA.bookings.filter(b => b.date == dateStr);
      const d = new Date(dateStr + 'T00:00:00');
      document.getElementById('dayDetailTitle').textContent = d.toLocaleDateString('en-PH', { weekday:'long', month:'long', day:'numeric' });
      document.getElementById('dayDetailBody').innerHTML = events.length
        ? events.map(e => `<div style="border-left:3px solid ${eventColor(e.event)};padding:0.6rem 0.8rem;border-radius:0 8px 8px 0;background:var(--mc-bg);margin-bottom:0.5rem;"><div style="font-weight:700;font-size:0.9rem;">${e.client}</div><div style="font-size:0.8rem;color:var(--mc-gray);">${e.event}</div><div style="font-size:0.78rem;margin-top:0.3rem;display:flex;gap:0.5rem;flex-wrap:wrap;"><span>${e.time ? e.time + ' ·' : ''} ${e.guests} guests</span><span>·</span><span>${e.package}</span><span>·</span>${statusBadge(e.status)}</div></div>`).join('')
        : `<div class="mc-empty" style="padding:1.5rem 0;"><div class="mc-empty-icon"><i class="bi bi-calendar-check"></i></div><p>No events this day</p></div>`;
    }

    function renderMonthSummary() {
      const monthStr = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}`;
      const events = MC_DATA.bookings.filter(b => b.date.startsWith(monthStr));
      document.getElementById('monthSummary').innerHTML = `<div style="display:flex;flex-direction:column;gap:0.6rem;font-size:0.88rem;"><div style="display:flex;justify-content:space-between;"><span>Total Events</span><strong>${events.length}</strong></div><div style="display:flex;justify-content:space-between;"><span>Confirmed</span><strong style="color:var(--mc-green);">${events.filter(b=>b.status=='confirmed').length}</strong></div><div style="display:flex;justify-content:space-between;"><span>Pending</span><strong style="color:var(--mc-gold);">${events.filter(b=>b.status=='pending').length}</strong></div><div style="display:flex;justify-content:space-between;"><span>Total Guests</span><strong>${events.reduce((a,b)=>a+b.guests,0)}</strong></div></div>`;
    }

    function renderListView() {
      const sorted = [...MC_DATA.bookings].filter(b => b.status != 'cancelled').sort((a,b) => new Date(a.date) - new Date(b.date));
      document.getElementById('listViewBody').innerHTML = sorted.length
        ? sorted.map(b => { const d = new Date(b.date+'T00:00:00'); return `<div class="mc-upcoming-item"><div class="mc-date-badge"><div class="day">${d.getDate()}</div><div class="month">${MONTHS[d.getMonth()].slice(0,3)}</div></div><div style="flex:1;min-width:0;"><div style="font-weight:700;">${b.client}</div><div style="font-size:0.82rem;color:var(--mc-gray);">${b.event} · ${b.guests} guests · ${b.package}${b.time?' · '+b.time:''}</div><div style="font-size:0.78rem;color:var(--mc-gray);margin-top:0.2rem;"><i class="bi bi-geo-alt" style="color:var(--mc-red);"></i> ${b.venue}</div></div>${statusBadge(b.status)}</div>`; }).join('')
        : '<div class="mc-empty"><div class="mc-empty-icon"><i class="bi bi-calendar-x"></i></div><p>No events found</p></div>';
    }

    function switchView(view, btn) {
      document.querySelectorAll('.mc-view-toggle button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('calendarView').style.display = view == 'calendar' ? '' : 'none';
      document.getElementById('listView').style.display = view == 'list' ? '' : 'none';
      if (view == 'list') renderListView();
    }

    async function quickAddEvent() {
      const client = document.getElementById('qClient').value.trim();
      const date   = document.getElementById('qDate').value;
      if (!client || !date) { showToast('Client name and date are required.', 'error'); return; }
      const payload = { client_name:client, event_type:document.getElementById('qType').value, event_date:date, event_time:document.getElementById('qTime').value||null, guest_count:parseInt(document.getElementById('qGuests').value)||0, package:document.getElementById('qPackage').value, venue:document.getElementById('qVenue').value.trim()||'—', status:'confirmed' };
      try {
        await apiRequest('/bookings', { method:'POST', body:JSON.stringify(payload) });
        await loadPageData(); closeModal('addBookingModal'); renderCalendar(); showToast('Event added to schedule!');
      } catch (e) { showToast(e.message || 'Failed to add event.', 'error'); }
    }

    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('sidebarMount').outerHTML = renderSidebar('schedule');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Event Schedule', "Medy's Catering › Schedule");
      initSidebar();
      await loadPageData();
      renderCalendar();
    });
  </script>
</body>
</html>
