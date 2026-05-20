<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports – Medy's Catering</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .mc-report-row{display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--mc-border);font-size:0.88rem}
    .mc-report-row:last-child{border-bottom:none}
    .mc-pkg-bar{height:10px;border-radius:5px;background:var(--mc-red)}

    /* Inline bar fills — also need print-color-adjust */
    [style*="background:var(--mc-green)"],
    [style*="background:var(--mc-gold)"],
    [style*="background:var(--mc-blue)"],
    [style*="background:var(--mc-red)"],
    [style*="background:#dc2626"] {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    @media print {
      /* Hide controls, show print title */
      .mc-section-hdr .d-flex { display: none !important; }
      #printTitle { display: block !important; }

      /* Status breakdown inline bars */
      .mc-report-row div[style*="height:7px"] div,
      .mc-report-row div[style*="height:7px"] {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      /* Ensure stat cards show color icons */
      .mc-stat-icon.red   { background: #fee2e2 !important; color: #dc2626 !important; }
      .mc-stat-icon.green { background: #dcfce7 !important; color: #16a34a !important; }
      .mc-stat-icon.gold  { background: #fef9c3 !important; color: #ca8a04 !important; }
      .mc-stat-icon.blue  { background: #dbeafe !important; color: #2563eb !important; }
      .mc-stat-icon {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
    }
  </style>
</head>
<body>
  <div class="mc-layout">
    <div id="sidebarMount"></div>
    <div class="mc-main">
      <div id="topbarMount"></div>
      <div class="mc-content">

        <!-- Print-only title -->
        <div id="printTitle" style="display:none;margin-bottom:1.5rem;">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:0.25rem;">Medy's Catering – Reports &amp; Analytics</h2>
          <p style="color:#666;font-size:0.85rem;margin:0;">Year: <strong id="printYear"></strong> &nbsp;|&nbsp; Printed: <strong id="printDate"></strong></p>
          <hr style="margin-top:0.75rem;" />
        </div>

        <div class="mc-section-hdr mb-4">
          <div><h2 style="font-size:1.4rem;font-family:'Playfair Display',serif;">Reports &amp; Analytics</h2><p style="font-size:0.82rem;color:var(--mc-gray);margin:0;">Overview of event and booking performance</p></div>
          <div class="d-flex gap-2">
            <select class="mc-input" style="width:auto;" id="reportYear" onchange="renderAll()"><option value="2025">2025</option><option value="2024">2024</option></select>
            <button class="btn-mc-ghost" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print</button>
          </div>
        </div>

        <div class="row g-3 mb-4" id="reportStats"></div>

        <div class="row g-4 mb-4">
          <div class="col-lg-7">
            <div class="mc-card h-100">
              <h6 id="chartHeading" style="font-weight:700;margin-bottom:1.2rem;">Monthly Bookings</h6>
              <div class="mc-chart-area" id="reportChart" style="height:200px;"></div>
              <div style="display:flex;justify-content:space-around;margin-top:0.5rem;" id="reportChartLabels"></div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="mc-card h-100"><h6 style="font-weight:700;margin-bottom:1rem;">Booking Status Breakdown</h6><div id="statusBreakdown"></div></div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6"><div class="mc-card h-100"><h6 style="font-weight:700;margin-bottom:1rem;">Events by Type</h6><div id="eventTypeList"></div></div></div>
          <div class="col-md-6"><div class="mc-card h-100"><h6 style="font-weight:700;margin-bottom:1rem;">Package Popularity</h6><div id="packageList"></div></div></div>
        </div>

        <div class="mc-card">
          <div class="mc-section-hdr">
            <h6 style="font-weight:700;margin:0;">Full Booking Log</h6>
            <span style="font-size:0.8rem;color:var(--mc-gray);" id="logCount"></span>
          </div>
          <div class="mc-table-wrap">
            <table class="mc-table">
              <thead><tr><th>ID</th><th>Client</th><th>Event</th><th>Date</th><th>Guests</th><th>Package</th><th>Status</th></tr></thead>
              <tbody id="reportLogTbody"></tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>
    const EMPTY_ROW = '<div style="color:var(--mc-gray);font-size:0.85rem;text-align:center;padding:1rem 0;">No data for this year.</div>';

    function populateYearFilter() {
      const years = [...new Set(MC_DATA.bookings.map(b => new Date(b.date+'T00:00:00').getFullYear()))].sort((a,b) => b-a);
      const cur = new Date().getFullYear();
      if (!years.includes(cur)) years.unshift(cur);
      document.getElementById('reportYear').innerHTML = years.map(y => `<option value="${y}">${y}</option>`).join('');
    }

    function renderAll() {
      const year = parseInt(document.getElementById('reportYear').value);

      // Update print title fields
      const py = document.getElementById('printYear');
      const pd = document.getElementById('printDate');
      if (py) py.textContent = year;
      if (pd) pd.textContent = new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });
      const bookings = MC_DATA.bookings.filter(b => new Date(b.date+'T00:00:00').getFullYear() === year);
      const fb = MC_DATA.feedback;
      const total = bookings.length;
      const completed = bookings.filter(b => b.status == 'completed').length;
      const avgRating = fb.length ? (fb.reduce((a,f) => a + (f.star_rating||0), 0) / fb.length).toFixed(1) : '—';
      const totalGuests = bookings.reduce((a,b) => a + (b.guests||0), 0);

      document.getElementById('reportStats').innerHTML = [
        { icon:'bi-calendar-event-fill', color:'red',   num:total,           lbl:'Total Bookings' },
        { icon:'bi-check-circle-fill',   color:'green', num:completed,       lbl:'Completed Events' },
        { icon:'bi-people-fill',         color:'blue',  num:totalGuests,     lbl:'Total Guests Served' },
        { icon:'bi-star-fill',           color:'gold',  num:avgRating+'★',   lbl:'Avg. Feedback Rating' },
      ].map(s => `<div class="col-6 col-xl-3"><div class="mc-stat-card"><div class="mc-stat-icon ${s.color}"><i class="bi ${s.icon}"></i></div><div><div class="mc-stat-num">${s.num}</div><div class="mc-stat-lbl">${s.lbl}</div></div></div></div>`).join('');

      document.getElementById('chartHeading').textContent = `Monthly Bookings (${year})`;
      const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const vals = months.map((_,i) => bookings.filter(b => new Date(b.date+'T00:00:00').getMonth() === i).length);
      const max = Math.max(...vals, 1);
      document.getElementById('reportChart').innerHTML = vals.map((v,i) => `<div class="mc-bar-wrap"><div class="mc-bar-val">${v||''}</div><div class="mc-bar" style="height:${(v/max)*160}px;" title="${months[i]}: ${v}"></div></div>`).join('');
      document.getElementById('reportChartLabels').innerHTML = months.map(m => `<div style="flex:1;text-align:center;font-size:0.65rem;color:var(--mc-gray);font-weight:600;">${m}</div>`).join('');

      const statuses = ['confirmed','pending','completed','cancelled'];
      const colors = { confirmed:'var(--mc-green)', pending:'var(--mc-gold)', completed:'var(--mc-blue)', cancelled:'#dc2626' };
      document.getElementById('statusBreakdown').innerHTML = statuses.map(s => {
        const count = bookings.filter(b => b.status == s).length;
        const pct = total ? Math.round(count/total*100) : 0;
        return `<div class="mc-report-row"><div style="display:flex;align-items:center;gap:0.6rem;"><span style="width:10px;height:10px;border-radius:3px;background:${colors[s]};display:inline-block;"></span><span style="text-transform:capitalize;">${s}</span></div><div style="display:flex;align-items:center;gap:0.75rem;"><div style="width:80px;height:7px;background:var(--mc-bg);border-radius:4px;overflow:hidden;"><div style="width:${pct}%;height:100%;background:${colors[s]};border-radius:4px;"></div></div><strong style="font-variant-numeric:tabular-nums;width:20px;text-align:right;">${count}</strong><span style="color:var(--mc-gray);font-size:0.78rem;width:35px;">${pct}%</span></div></div>`;
      }).join('');

      const typeMap = {};
      bookings.forEach(b => { typeMap[b.event] = (typeMap[b.event]||0) + 1; });
      const typeSorted = Object.entries(typeMap).sort((a,b) => b[1]-a[1]);
      document.getElementById('eventTypeList').innerHTML = typeSorted.length
        ? typeSorted.map(([type,count]) => { const pct = total ? Math.round(count/total*100) : 0; return `<div class="mc-report-row"><span>${type}</span><div style="display:flex;align-items:center;gap:0.5rem;"><div style="width:60px;height:7px;background:var(--mc-bg);border-radius:4px;overflow:hidden;"><div style="width:${pct}%;height:100%;background:var(--mc-red);border-radius:4px;"></div></div><strong>${count}</strong></div></div>`; }).join('')
        : EMPTY_ROW;

      const pkgMap = {};
      bookings.forEach(b => { pkgMap[b.package] = (pkgMap[b.package]||0) + 1; });
      const pkgColors = { Basic:'var(--mc-blue)', Standard:'var(--mc-gold)', Premium:'var(--mc-red)', Custom:'var(--mc-green)' };
      const pkgSorted = Object.entries(pkgMap).sort((a,b) => b[1]-a[1]);
      document.getElementById('packageList').innerHTML = pkgSorted.length
        ? pkgSorted.map(([pkg,count]) => { const pct = total ? Math.round(count/total*100) : 0; return `<div class="mc-report-row"><div style="display:flex;align-items:center;gap:0.5rem;"><span style="width:10px;height:10px;border-radius:3px;background:${pkgColors[pkg]||'#aaa'};display:inline-block;"></span>${pkg} Package</div><div style="display:flex;align-items:center;gap:0.5rem;"><div style="width:70px;height:7px;background:var(--mc-bg);border-radius:4px;overflow:hidden;"><div style="width:${pct}%;height:100%;background:${pkgColors[pkg]||'#aaa'};border-radius:4px;"></div></div><strong>${count}</strong><span style="color:var(--mc-gray);font-size:0.78rem;">(${pct}%)</span></div></div>`; }).join('')
        : EMPTY_ROW;

      document.getElementById('logCount').textContent = `${total} records`;
      document.getElementById('reportLogTbody').innerHTML = [...bookings].sort((a,b) => new Date(b.date)-new Date(a.date)).map(b => `<tr><td><code style="background:var(--mc-bg);padding:2px 6px;border-radius:5px;font-size:0.78rem;">${b.id}</code></td><td><strong>${b.client}</strong></td><td style="font-size:0.85rem;color:var(--mc-gray);">${b.event}</td><td>${fmtDate(b.date)}</td><td>${b.guests}</td><td>${b.package}</td><td>${statusBadge(b.status)}</td></tr>`).join('') || `<tr><td colspan="7"><div class="mc-empty"><div class="mc-empty-icon"><i class="bi bi-calendar-x"></i></div><p>No bookings for ${year}.</p></div></td></tr>`;
    }

    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('sidebarMount').outerHTML = renderSidebar('reports');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Reports', "Medy's Catering › Reports");
      initSidebar();
      await loadPageData();
      populateYearFilter();
      renderAll();
    });
  </script>
</body>
</html>
