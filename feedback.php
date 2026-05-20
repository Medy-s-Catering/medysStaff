<?php
session_start();
if (!isset($_SESSION['mc_user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feedback – Medy's Catering</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .mc-feedback-card{background:#fff;border:1.5px solid var(--mc-border);border-radius:var(--mc-radius);padding:1.4rem;transition:var(--mc-transition)}
    .mc-feedback-card:hover{border-color:var(--mc-red-light);box-shadow:var(--mc-shadow)}
    .mc-feedback-card.unread{border-left:4px solid var(--mc-red)}
    .mc-rating-bar{height:8px;background:var(--mc-bg);border-radius:4px;overflow:hidden}
    .mc-rating-bar-fill{height:100%;background:var(--mc-gold);border-radius:4px;transition:width 0.5s ease}
    .mc-add-feedback-stars{display:flex;gap:0.3rem}
    .mc-star-btn{background:none;border:none;font-size:1.8rem;cursor:pointer;color:#ddd;transition:color 0.15s;line-height:1}
    .mc-star-btn.active,.mc-star-btn:hover{color:var(--mc-gold)}
  </style>
</head>
<body>
  <div class="mc-layout">
    <div id="sidebarMount"></div>
    <div class="mc-main">
      <div id="topbarMount"></div>
      <div class="mc-content">

        <div class="mc-section-hdr mb-4">
          <div><h2 style="font-size:1.4rem;font-family:'Playfair Display',serif;">Client Feedback</h2><p style="font-size:0.82rem;color:var(--mc-gray);margin:0;">Collect and review feedback from completed events</p></div>
          <button class="btn-mc-primary" onclick="openModal('addFeedbackModal')"><i class="bi bi-plus-lg"></i> Add Feedback</button>
        </div>

        <div class="row g-3 mb-4" id="feedbackStats"></div>

        <div class="row g-4 mb-4">
          <div class="col-lg-5">
            <div class="mc-card h-100"><h6 style="font-weight:700;margin-bottom:1rem;">Rating Distribution</h6><div id="ratingBars"></div></div>
          </div>
          <div class="col-lg-7">
            <div class="mc-card h-100">
              <div class="mc-section-hdr"><h6 style="font-weight:700;margin:0;">Filter Feedback</h6></div>
              <div class="row g-2">
                <div class="col-md-5"><input type="text" id="fbSearch" class="mc-input" placeholder="Search client or event..." oninput="renderFeedback()" /></div>
                <div class="col-md-3"><select id="fbRating" class="mc-input" onchange="renderFeedback()"><option value="">All Ratings</option><option value="5">5 Stars</option><option value="4">4 Stars</option><option value="3">3 Stars</option><option value="2">2 Stars</option><option value="1">1 Star</option></select></div>
                <div class="col-md-4"><select id="fbStatus" class="mc-input" onchange="renderFeedback()"><option value="">All</option><option value="new">Unread</option><option value="read">Read</option></select></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3" id="feedbackGrid"></div>

      </div>
    </div>
  </div>

  <div class="mc-modal-overlay" id="addFeedbackModal">
    <div class="mc-modal">
      <div class="mc-modal-header">
        <h5 style="font-family:'Playfair Display',serif;margin:0;">Record Client Feedback</h5>
        <button class="btn-mc-icon" onclick="closeModal('addFeedbackModal')"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="mc-modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="mc-label">Client Name *</label><input type="text" id="ffClient" class="mc-input" placeholder="e.g. Santos Family" /></div>
          <div class="col-md-6"><label class="mc-label">Email</label><input type="email" id="ffEmail" class="mc-input" placeholder="client@email.com" /></div>
          <div class="col-md-6"><label class="mc-label">Event Type *</label><select id="ffEventType" class="mc-input"><option value="">Select event type...</option><option value="Corporate Event">Corporate Event</option><option value="Wedding / Reception">Wedding / Reception</option><option value="Birthday / Debut">Birthday / Debut</option><option value="School Activity">School Activity</option><option value="Family Reunion">Family Reunion</option><option value="Other / General">Other / General</option></select></div>
          <div class="col-md-6"><label class="mc-label">Client Booked With Us?</label><select id="ffHasBooked" class="mc-input"><option value="yes">Yes, booked an event</option><option value="no">No, experienced service another way</option><option value="inquired">Inquired / contacted the team</option></select></div>
          <div class="col-md-6"><label class="mc-label">Date Submitted</label><input type="date" id="ffDate" class="mc-input" /></div>
          <div class="col-12"><label class="mc-label">Star Rating *</label><div class="mc-add-feedback-stars" id="starInput"><button class="mc-star-btn" data-val="1" onclick="setRating(1)">★</button><button class="mc-star-btn" data-val="2" onclick="setRating(2)">★</button><button class="mc-star-btn" data-val="3" onclick="setRating(3)">★</button><button class="mc-star-btn" data-val="4" onclick="setRating(4)">★</button><button class="mc-star-btn" data-val="5" onclick="setRating(5)">★</button></div><input type="hidden" id="ffRating" value="0" /></div>
          <div class="col-12"><label class="mc-label">Comments *</label><textarea id="ffComments" class="mc-input" rows="4" placeholder="Write the client's feedback here..."></textarea></div>
          <div class="col-12"><label class="mc-label">What They Liked <span style="font-weight:400;color:var(--mc-gray);">(optional)</span></label><input type="text" id="ffLikedTags" class="mc-input" placeholder="e.g. Food Quality, Service, Coordination" /></div>
        </div>
      </div>
      <div class="mc-modal-footer">
        <button class="btn-mc-ghost" onclick="closeModal('addFeedbackModal')">Cancel</button>
        <button class="btn-mc-primary" onclick="saveFeedback()"><i class="bi bi-check-lg"></i> Save Feedback</button>
      </div>
    </div>
  </div>

  <script src="assets/app.js"></script>
  <script>
    function renderStats() {
      const total = MC_DATA.feedback.length;
      const avg       = total ? (MC_DATA.feedback.reduce((a,f) => a + (f.star_rating || 0), 0) / total).toFixed(1) : 0;
      const fiveStars = MC_DATA.feedback.filter(f => f.star_rating == 5).length;
      const unread    = MC_DATA.feedback.filter(f => f.status == 'new').length;

      document.getElementById('feedbackStats').innerHTML = [
        { icon:'bi-chat-heart-fill', color:'red',   num:total,       lbl:'Total Feedback',  sub:'From all events' },
        { icon:'bi-star-fill',       color:'gold',  num:avg + '★',   lbl:'Average Rating',  sub:'Overall score' },
        { icon:'bi-hand-thumbs-up-fill', color:'green', num:fiveStars, lbl:'5-Star Reviews', sub:`${Math.round(fiveStars/total*100)||0}% of total` },
        { icon:'bi-bell-fill',       color:'blue',  num:unread,      lbl:'Unread Feedback', sub:'Needs attention' },
      ].map(s => `<div class="col-6 col-xl-3"><div class="mc-stat-card"><div class="mc-stat-icon ${s.color}"><i class="bi ${s.icon}"></i></div><div><div class="mc-stat-num">${s.num}</div><div class="mc-stat-lbl">${s.lbl}</div><div class="mc-stat-sub">${s.sub}</div></div></div></div>`).join('');

      document.getElementById('ratingBars').innerHTML = [5,4,3,2,1].map(n => {
        const count = MC_DATA.feedback.filter(f => f.star_rating == n).length;
        const pct   = total ? Math.round(count/total*100) : 0;
        return `<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.6rem;"><span style="font-size:0.82rem;font-weight:600;width:14px;">${n}</span><i class="bi bi-star-fill" style="color:var(--mc-gold);font-size:0.75rem;"></i><div class="mc-rating-bar" style="flex:1;"><div class="mc-rating-bar-fill" style="width:${pct}%;"></div></div><span style="font-size:0.78rem;color:var(--mc-gray);width:28px;text-align:right;">${count}</span></div>`;
      }).join('');
    }

    function renderFeedback() {
      const s  = document.getElementById('fbSearch').value.toLowerCase();
      const rt = document.getElementById('fbRating').value;
      const st = document.getElementById('fbStatus').value;
      const filtered = MC_DATA.feedback.filter(f =>
        (!s  || f.client.toLowerCase().includes(s) || (f.event_type||'').toLowerCase().includes(s)) &&
        (!rt || f.star_rating == parseInt(rt)) &&
        (!st || f.status == st));

      document.getElementById('feedbackGrid').innerHTML = filtered.length
        ? filtered.map(f => `
        <div class="col-md-6 col-xl-4">
          <div class="mc-feedback-card ${f.status == 'new' ? 'unread' : ''}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.6rem;">
              <div>
                <div style="font-weight:700;font-size:0.95rem;">${f.client}</div>
                <div style="font-size:0.78rem;color:var(--mc-gray);">${f.event_type||'—'} · ${fmtDate(f.date_submitted)}</div>
                ${f.email ? `<div style="font-size:0.75rem;color:var(--mc-gray);">${f.email}</div>` : ''}
              </div>
              <div style="display:flex;gap:0.3rem;flex-wrap:wrap;justify-content:flex-end;">
                ${f.status == 'new' ? '<span class="mc-badge mc-badge-pending">New</span>' : ''}
                ${f.has_booked == 'yes' ? '<span class="mc-badge mc-badge-confirmed" style="font-size:0.7rem;">Booked</span>' : ''}
                <button class="btn-mc-icon" onclick="markRead(${f.id})" title="Mark as read"><i class="bi bi-check2-all"></i></button>
                <button class="btn-mc-icon danger" onclick="deleteFeedback(${f.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
              </div>
            </div>
            <div class="mc-stars" style="font-size:1.05rem;letter-spacing:2px;">${starRating(f.star_rating)}</div>
            <p style="font-size:0.87rem;color:#444;margin-top:0.6rem;margin-bottom:0;line-height:1.7;">"${f.comments}"</p>
            ${f.liked_tags ? `<div style="margin-top:0.5rem;font-size:0.75rem;color:var(--mc-gray);"><i class="bi bi-hand-thumbs-up" style="color:var(--mc-gold);"></i> ${f.liked_tags}</div>` : ''}
          </div>
        </div>`).join('')
        : `<div class="col-12"><div class="mc-empty mc-card"><div class="mc-empty-icon"><i class="bi bi-chat-x"></i></div><p>No feedback found.</p></div></div>`;
    }

    async function markRead(id) {
      try {
        await apiRequest('/feedback/' + id, { method:'PATCH', body:JSON.stringify({ status:'read' }) });
        await loadPageData(); renderFeedback(); renderStats(); showToast('Marked as read.');
      } catch (e) { showToast(e.message || 'Failed to update.', 'error'); }
    }

    async function deleteFeedback(id) {
      if (!confirm('Delete this feedback?')) return;
      try {
        await apiRequest('/feedback/' + id, { method:'DELETE' });
        await loadPageData(); renderFeedback(); renderStats(); showToast('Feedback deleted.');
      } catch (e) { showToast(e.message || 'Failed to delete.', 'error'); }
    }

    let selectedRating = 0;
    function setRating(val) {
      selectedRating = val;
      document.getElementById('ffRating').value = val;
      document.querySelectorAll('.mc-star-btn').forEach((b, i) => b.classList.toggle('active', i < val));
    }

    async function saveFeedback() {
      const client     = document.getElementById('ffClient').value.trim();
      const event_type = document.getElementById('ffEventType').value;
      const comments   = document.getElementById('ffComments').value.trim();
      const star_rating = parseInt(document.getElementById('ffRating').value);
      const date_submitted = document.getElementById('ffDate').value || new Date().toISOString().split('T')[0];
      if (!client || !event_type || !comments || !star_rating) { showToast('Please fill in all required fields.', 'error'); return; }
      try {
        await apiRequest('/feedback', { method:'POST', body:JSON.stringify({ client_name:client, email:document.getElementById('ffEmail').value.trim(), event_type, has_booked:document.getElementById('ffHasBooked').value, star_rating, comments, liked_tags:document.getElementById('ffLikedTags').value.trim(), date_submitted }) });
        await loadPageData(); closeModal('addFeedbackModal');
        ['ffClient','ffEmail','ffComments','ffDate','ffLikedTags'].forEach(id => document.getElementById(id).value='');
        document.getElementById('ffEventType').value=''; document.getElementById('ffHasBooked').value='yes'; setRating(0);
        renderFeedback(); renderStats(); showToast('Feedback recorded!');
      } catch (e) { showToast(e.message || 'Failed to save feedback.', 'error'); }
    }

    document.addEventListener('DOMContentLoaded', async () => {
      await loadPageData();
      document.getElementById('sidebarMount').outerHTML = renderSidebar('feedback');
      document.getElementById('topbarMount').outerHTML = renderTopbar('Client Feedback', "Medy's Catering › Feedback");
      initSidebar();
      renderStats();
      renderFeedback();
    });
  </script>
</body>
</html>
