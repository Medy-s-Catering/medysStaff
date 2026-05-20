<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard – Medy's Catering</title>
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

        <div class="mc-card mb-4" style="background:linear-gradient(135deg,var(--mc-red-dark) 0%,#6d1b12 100%);border:none;color:#fff;position:relative;overflow:hidden;">
          <div style="position:absolute;right:-20px;top:-20px;font-size:8rem;opacity:0.07;"><i class="bi bi-award-fill"></i></div>
          <div class="row align-items-center">
            <div class="col">
              <p style="font-size:0.78rem;opacity:0.7;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:0.3rem;">Welcome back</p>
              <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:0.5rem;" id="welcomeName">Good morning!</h2>
              <p style="opacity:0.8;font-size:0.88rem;margin:0;" id="todayDate"></p>
            </div>
            <div class="col-auto d-none d-md-block">
              <a href="bookings.php" class="btn-mc-outline" style="border-color:rgba(255,255,255,0.6);color:#fff;"><i class="bi bi-plus-lg"></i> New Booking</a>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-4" id="statCards"></div>

        <div class="row g-4">
          <div class="col-lg-7">
            <div class="mc-card h-100">
              <div class="mc-section-hdr">
                <div class="mc-section-title">Upcoming Events</div>
                <a href="schedule.php" class="btn-mc-ghost"><i class="bi bi-calendar3"></i> Full Schedule</a>
              </div>
              <div class="mc-table-wrap">
                <table class="mc-table" id="upcomingTable">
                  <thead><tr><th>Client</th><th>Event</th><th>Date</th><th>Guests</th><th>Status</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-5 d-flex flex-column gap-4">
            <div class="mc-card">
              <div class="mc-section-hdr"><div class="mc-section-title">Pending Bookings</div><a href="bookings.php" class="btn-mc-ghost">View All</a></div>
              <div id="pendingList"></div>
            </div>
            <div class="mc-card">
              <div class="mc-section-hdr"><div class="mc-section-title">Recent Feedback</div><a href="feedback.php" class="btn-mc-ghost">View All</a></div>
              <div id="feedbackList"></div>
            </div>
          </div>
        </div>

        <div class="mc-card mt-4">
          <div class="mc-section-hdr">
            <div class="mc-section-title">Monthly Bookings Overview</div>
            <span id="chartYear" style="font-size:0.78rem;color:var(--mc-gray);"></span>
          </div>
          <div class="mc-chart-area" id="bookingsChart"></div>
          <div style="display:flex;justify-content:space-around;margin-top:0.5rem;" id="chartLabels"></div>
        </div>

      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      await loadPageData();
      document.getElementById('sidebarMount').outerHTML = renderSidebar('dashboard');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Dashboard', 'Medy\'s Catering › Dashboard');
      initSidebar();

      const hour = new Date().getHours();
      const greet = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
      document.getElementById('welcomeName').textContent = `${greet}, ${MC_DATA.currentUser.name}!`;
      document.getElementById('todayDate').textContent = new Date().toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

      const confirmed = MC_DATA.bookings.filter(b => b.status == 'confirmed').length;
      const pending   = MC_DATA.bookings.filter(b => b.status == 'pending').length;
      const completed = MC_DATA.bookings.filter(b => b.status == 'completed').length;
      const newFb     = MC_DATA.feedback.filter(f => f.status == 'new').length;
      const avgRating = MC_DATA.feedback.length ? (MC_DATA.feedback.reduce((a, f) => a + (f.star_rating || 0), 0) / MC_DATA.feedback.length).toFixed(1) : '—';

      document.getElementById('statCards').innerHTML = [
        { icon:'bi-calendar-check-fill', color:'red',   num:MC_DATA.bookings.length, lbl:'Total Bookings',   sub:`${confirmed} confirmed` },
        { icon:'bi-clock-history',        color:'gold',  num:pending,                 lbl:'Pending Approval', sub:'Needs action' },
        { icon:'bi-check2-all',           color:'green', num:completed,               lbl:'Completed Events', sub:'This year' },
        { icon:'bi-star-fill',            color:'blue',  num:avgRating,               lbl:'Average Rating',   sub:`${newFb} new feedback` },
      ].map(s => `<div class="col-6 col-xl-3"><div class="mc-stat-card"><div class="mc-stat-icon ${s.color}"><i class="bi ${s.icon}"></i></div><div><div class="mc-stat-num">${s.num}</div><div class="mc-stat-lbl">${s.lbl}</div><div class="mc-stat-sub">${s.sub}</div></div></div></div>`).join('');

      const upcoming = [...MC_DATA.bookings].filter(b => b.status == 'confirmed' || b.status == 'pending').sort((a, b) => new Date(a.date) - new Date(b.date)).slice(0, 5);
      document.querySelector('#upcomingTable tbody').innerHTML = upcoming.length
        ? upcoming.map(b => `<tr><td><strong>${b.client}</strong></td><td style="color:var(--mc-gray);font-size:0.85rem;">${b.event}</td><td style="white-space:nowrap;">${fmtDate(b.date)}</td><td>${b.guests}</td><td>${statusBadge(b.status)}</td></tr>`).join('')
        : `<tr><td colspan="5"><div class="mc-empty"><div class="mc-empty-icon"><i class="bi bi-calendar-x"></i></div><p>No upcoming events</p></div></td></tr>`;

      const pendingItems = MC_DATA.bookings.filter(b => b.status == 'pending');
      document.getElementById('pendingList').innerHTML = pendingItems.length
        ? pendingItems.map(b => `<div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid var(--mc-border);"><div style="width:38px;height:38px;border-radius:10px;background:var(--mc-red-pale);display:flex;align-items:center;justify-content:center;color:var(--mc-red);flex-shrink:0;"><i class="bi bi-person-fill"></i></div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:0.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${b.client}</div><div style="font-size:0.78rem;color:var(--mc-gray);">${b.event} · ${fmtDate(b.date)}</div></div><a href="bookings.php" class="btn-mc-ghost" style="font-size:0.75rem;">Review</a></div>`).join('')
        : `<div class="mc-empty"><div class="mc-empty-icon"><i class="bi bi-check-circle"></i></div><p>No pending bookings</p></div>`;

      document.getElementById('feedbackList').innerHTML = MC_DATA.feedback.slice(0, 3).map(f => `<div style="padding:0.75rem 0;border-bottom:1px solid var(--mc-border);"><div style="display:flex;justify-content:space-between;margin-bottom:0.2rem;"><strong style="font-size:0.87rem;">${f.client}</strong><span class="mc-stars" style="font-size:0.8rem;">${starRating(f.star_rating)}</span></div><p style="font-size:0.8rem;color:var(--mc-gray);margin:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${f.comments}</p>${f.status == 'new' ? '<span class="mc-badge mc-badge-pending" style="margin-top:0.3rem;display:inline-flex;">New</span>' : ''}</div>`).join('');

      const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const curYear = new Date().getFullYear();
      document.getElementById('chartYear').textContent = curYear;
      const vals = months.map((_, i) => MC_DATA.bookings.filter(b => { const d = new Date(b.date + 'T00:00:00'); return d.getFullYear() === curYear && d.getMonth() === i; }).length);
      const max = Math.max(...vals, 1);
      document.getElementById('bookingsChart').innerHTML = vals.map((v, i) => `<div class="mc-bar-wrap"><div class="mc-bar-val">${v || ''}</div><div class="mc-bar" style="height:${(v / max * 160)}px;" title="${months[i]}: ${v} bookings"></div></div>`).join('');
      document.getElementById('chartLabels').innerHTML = months.map(m => `<div style="flex:1;text-align:center;font-size:0.65rem;color:var(--mc-gray);font-weight:600;">${m}</div>`).join('');
    });
  </script>
</body>
</html>
