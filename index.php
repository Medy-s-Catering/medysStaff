<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Medy's Catering – Staff Portal</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#c0392b;--red-dark:#96281b;--red-pale:#fff0ee;--gold:#f59e0b;--dark:#1c1c1c;--gray:#6b7280;--border:#e8d8d5;--bg:#fdf5f4}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--dark);overflow-x:hidden}
    a{text-decoration:none;color:inherit}
    .lp-nav{position:fixed;top:0;left:0;right:0;z-index:1000;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;transition:all 0.3s ease}
    .lp-nav.scrolled{background:rgba(150,40,27,0.97);backdrop-filter:blur(10px);box-shadow:0 2px 20px rgba(0,0,0,0.2);padding:0.7rem 2rem}
    .lp-brand{display:flex;align-items:center;gap:0.7rem;color:#fff}
    .lp-brand-icon{width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
    .lp-brand-text{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;line-height:1.1}
    .lp-brand-sub{font-size:0.65rem;opacity:0.65;font-family:'DM Sans',sans-serif;font-weight:400;letter-spacing:0.08em;text-transform:uppercase}
    .lp-nav-links{display:flex;align-items:center;gap:0.5rem}
    .lp-nav-link{color:rgba(255,255,255,0.8);font-size:0.88rem;font-weight:600;padding:0.45rem 0.9rem;border-radius:8px;transition:all 0.2s}
    .lp-nav-link:hover{color:#fff;background:rgba(255,255,255,0.15)}
    .lp-btn-login{background:#fff;color:var(--red)!important;font-weight:700;padding:0.5rem 1.4rem;border-radius:50px;font-size:0.88rem;transition:all 0.2s;display:inline-flex;align-items:center;gap:0.4rem}
    .lp-btn-login:hover{background:var(--gold);color:#fff!important;transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,0,0,0.2)}
    .lp-hero{min-height:100vh;background:linear-gradient(135deg,var(--red-dark) 0%,#6d1b12 55%,#1c1c1c 100%);display:flex;align-items:center;position:relative;overflow:hidden}
    .lp-hero-circle{position:absolute;right:-120px;top:50%;transform:translateY(-50%);width:600px;height:600px;border-radius:50%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);pointer-events:none}
    .lp-hero-content{position:relative;z-index:1;color:#fff;padding-top:6rem;padding-bottom:4rem}
    .lp-hero-pre{display:inline-flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:50px;padding:0.35rem 1rem;font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold);margin-bottom:1.5rem}
    .lp-hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.4rem,5.5vw,4rem);font-weight:900;line-height:1.15;margin-bottom:1.2rem}
    .lp-hero-title span{color:var(--gold)}
    .lp-hero-desc{font-size:1.05rem;opacity:0.82;line-height:1.8;max-width:520px;margin-bottom:2.2rem;font-weight:300}
    .lp-hero-btns{display:flex;flex-wrap:wrap;gap:0.9rem}
    .lp-btn-primary{background:#fff;color:var(--red);font-weight:700;font-size:0.95rem;padding:0.85rem 2rem;border-radius:50px;display:inline-flex;align-items:center;gap:0.5rem;transition:all 0.25s;box-shadow:0 4px 20px rgba(0,0,0,0.15)}
    .lp-btn-primary:hover{background:var(--gold);color:#fff;transform:translateY(-2px)}
    .lp-btn-ghost{background:transparent;color:rgba(255,255,255,0.85);font-weight:600;font-size:0.95rem;padding:0.85rem 2rem;border-radius:50px;border:1.5px solid rgba(255,255,255,0.4);display:inline-flex;align-items:center;gap:0.5rem;transition:all 0.25s}
    .lp-btn-ghost:hover{background:rgba(255,255,255,0.12);color:#fff}
    .lp-hero-img-card{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:1.5rem;backdrop-filter:blur(12px);position:relative}
    .lp-hero-img-card img{width:100%;border-radius:12px;aspect-ratio:4/3;object-fit:cover;display:block}
    .lp-hero-badge{position:absolute;bottom:-16px;left:50%;transform:translateX(-50%);background:var(--gold);color:#fff;font-weight:700;font-size:0.8rem;padding:0.5rem 1.2rem;border-radius:50px;white-space:nowrap;box-shadow:0 4px 14px rgba(245,158,11,0.4);display:flex;align-items:center;gap:0.4rem}
    .lp-stats{background:var(--red);padding:1.5rem 0;color:#fff}
    .lp-stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;line-height:1}
    .lp-stat-lbl{font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;opacity:0.8;margin-top:0.25rem}
    .lp-section{padding:5rem 0}
    .lp-section-alt{background:#fff}
    .lp-pre{font-size:0.75rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--red);margin-bottom:0.5rem}
    .lp-title{font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:900;line-height:1.25;margin-bottom:1rem}
    .lp-title span{color:var(--red)}
    .lp-body{font-size:0.97rem;color:var(--gray);line-height:1.8}
    .lp-feature-card{background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem 1.5rem;height:100%;transition:all 0.25s;position:relative;overflow:hidden}
    .lp-feature-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:var(--red);transform:scaleX(0);transform-origin:left;transition:transform 0.3s ease}
    .lp-feature-card:hover{border-color:var(--red);box-shadow:0 8px 32px rgba(192,57,43,0.12);transform:translateY(-4px)}
    .lp-feature-card:hover::after{transform:scaleX(1)}
    .lp-feature-icon{width:56px;height:56px;border-radius:14px;background:var(--red-pale);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--red);margin-bottom:1.2rem}
    .lp-feature-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;margin-bottom:0.6rem}
    .lp-role-card{border-radius:16px;padding:2.5rem 2rem;height:100%;transition:all 0.25s}
    .lp-role-card.admin-card{background:linear-gradient(135deg,var(--red-dark) 0%,#6d1b12 100%);color:#fff}
    .lp-role-card.staff-card{background:#fff;border:1.5px solid var(--border);color:var(--dark)}
    .lp-role-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,0.15)}
    .lp-role-avatar{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:1.2rem}
    .admin-card .lp-role-avatar{background:rgba(255,255,255,0.15)}
    .staff-card .lp-role-avatar{background:var(--red-pale);color:var(--red)}
    .lp-role-perm{display:flex;align-items:center;gap:0.5rem;font-size:0.88rem;margin-bottom:0.5rem;opacity:0.9}
    .admin-card .lp-role-perm i{color:var(--gold)}
    .staff-card .lp-role-perm i{color:var(--red)}
    .lp-step{display:flex;gap:1.2rem;margin-bottom:2rem}
    .lp-step-num{width:44px;height:44px;border-radius:50%;background:var(--red);color:#fff;font-weight:700;font-family:'Playfair Display',serif;font-size:1.1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 14px rgba(192,57,43,0.3)}
    .lp-step-content h6{font-weight:700;margin-bottom:0.3rem;font-size:1rem}
    .lp-step-content p{font-size:0.88rem;color:var(--gray);margin:0;line-height:1.7}
    .lp-cta{background:linear-gradient(135deg,var(--red-dark) 0%,#3d0c07 100%);padding:5rem 0;text-align:center;color:#fff;position:relative;overflow:hidden}
    .lp-footer{background:#111;color:rgba(255,255,255,0.6);padding:2rem 0;font-size:0.85rem}
    @keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
    .anim{opacity:0}
    .anim.visible{animation:fadeUp 0.6s ease forwards}
    .anim.d1{animation-delay:0.1s}.anim.d2{animation-delay:0.2s}.anim.d3{animation-delay:0.3s}
    @media(max-width:991.98px){.lp-hero-img-wrap{display:none}.lp-nav-links .lp-nav-link{display:none}}
  </style>
</head>
<body>

  <nav class="lp-nav" id="navbar">
    <div class="lp-brand">
      <div class="lp-brand-icon"><i class="bi bi-award-fill"></i></div>
      <div><div class="lp-brand-text">Medy's Catering</div><div class="lp-brand-sub">Staff Portal</div></div>
    </div>
    <div class="lp-nav-links">
      <a href="#features" class="lp-nav-link">Features</a>
      <a href="#roles" class="lp-nav-link">Roles</a>
      <a href="#how" class="lp-nav-link">How It Works</a>
      <a href="login.php" class="lp-btn-login ms-2"><i class="bi bi-box-arrow-in-right"></i> Staff Login</a>
    </div>
  </nav>

  <section class="lp-hero">
    <div class="lp-hero-circle"></div>
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="lp-hero-content">
            <div class="lp-hero-pre anim"><i class="bi bi-stars"></i> Event Management System</div>
            <h1 class="lp-hero-title anim d1">Manage Every <span>Event</span><br>With Ease &amp; Precision</h1>
            <p class="lp-hero-desc anim d2">Medy's Catering Staff Portal is your all-in-one digital system for managing bookings, coordinating event schedules, collecting client feedback, and generating performance reports — all in one place.</p>
            <div class="lp-hero-btns anim d3">
              <a href="login.php" class="lp-btn-primary" id="heroCta"><i class="bi bi-box-arrow-in-right"></i> Go to Staff Login</a>
              <a href="#features" class="lp-btn-ghost"><i class="bi bi-arrow-down-circle"></i> Learn More</a>
            </div>
          </div>
        </div>
        <div class="col-lg-6 d-none d-lg-block">
          <div class="position-relative">
            <div class="lp-hero-img-card">
              <img src="assets/pic1.jpg" alt="Medy's Catering past event" />
              <div class="lp-hero-badge"><i class="bi bi-shield-check-fill"></i> Trusted Since 2010 · 500+ Events</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="lp-stats">
    <div class="container">
      <div class="row g-3 text-center">
        <div class="col-6 col-md-3"><div class="lp-stat-num" data-target="500">0</div><div class="lp-stat-lbl">Events Managed</div></div>
        <div class="col-6 col-md-3"><div class="lp-stat-num" data-target="10">0</div><div class="lp-stat-lbl">Years in Service</div></div>
        <div class="col-6 col-md-3"><div class="lp-stat-num" data-target="98">0</div><div class="lp-stat-lbl">% Client Satisfaction</div></div>
        <div class="col-6 col-md-3"><div class="lp-stat-num" data-target="50">0</div><div class="lp-stat-lbl">Menu Selections</div></div>
      </div>
    </div>
  </section>

  <section class="lp-section" id="features">
    <div class="container">
      <div class="text-center mb-5"><p class="lp-pre">System Features</p><h2 class="lp-title">Everything Staff <span>Need</span></h2></div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4 anim"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-calendar-check-fill"></i></div><div class="lp-feature-title">Booking Management</div><p class="lp-body">Create, view, edit, and delete client event bookings. Filter by status, package, and date with instant search.</p></div></div>
        <div class="col-md-6 col-lg-4 anim d1"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-calendar3"></i></div><div class="lp-feature-title">Event Schedule Calendar</div><p class="lp-body">Interactive monthly calendar with color-coded events by type. Toggle between calendar and list views.</p></div></div>
        <div class="col-md-6 col-lg-4 anim d2"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-chat-heart-fill"></i></div><div class="lp-feature-title">Client Feedback</div><p class="lp-body">Record and manage client feedback with star ratings. Track unread responses and monitor satisfaction scores.</p></div></div>
        <div class="col-md-6 col-lg-4 anim d1"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-bar-chart-fill"></i></div><div class="lp-feature-title">Reports &amp; Analytics</div><p class="lp-body">Visual booking charts, status breakdowns, event type analytics, and package popularity — all printable.</p></div></div>
        <div class="col-md-6 col-lg-4 anim d2"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-people-fill"></i></div><div class="lp-feature-title">Staff Account Management</div><p class="lp-body">Admin-only control to create, edit, and manage staff accounts with role-based access permissions.</p></div></div>
        <div class="col-md-6 col-lg-4 anim d3"><div class="lp-feature-card"><div class="lp-feature-icon"><i class="bi bi-grid-fill"></i></div><div class="lp-feature-title">Live Dashboard</div><p class="lp-body">At-a-glance summary of bookings, pending approvals, recent feedback, and upcoming events on login.</p></div></div>
      </div>
    </div>
  </section>

  <section class="lp-section lp-section-alt" id="roles">
    <div class="container">
      <div class="text-center mb-5"><p class="lp-pre">Access Levels</p><h2 class="lp-title">Two <span>Roles</span>, One System</h2></div>
      <div class="row g-4 justify-content-center">
        <div class="col-md-6 col-lg-5 anim">
          <div class="lp-role-card admin-card">
            <div class="lp-role-avatar"><i class="bi bi-shield-fill-check"></i></div>
            <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.3rem;">Administrator</h4>
            <p style="opacity:0.65;font-size:0.82rem;margin-bottom:1.5rem;">Full system access</p>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> View &amp; manage all bookings</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> Manage event schedule</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> View &amp; manage client feedback</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> Generate and view reports</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> Manage staff accounts &amp; roles</div>
            <a href="login.php" style="display:inline-flex;align-items:center;gap:0.4rem;margin-top:1.5rem;background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:50px;padding:0.6rem 1.4rem;font-size:0.88rem;font-weight:600;">
              <i class="bi bi-box-arrow-in-right"></i> Login as Admin
            </a>
          </div>
        </div>
        <div class="col-md-6 col-lg-5 anim d2">
          <div class="lp-role-card staff-card">
            <div class="lp-role-avatar"><i class="bi bi-person-badge-fill"></i></div>
            <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.3rem;">Staff</h4>
            <p style="color:var(--gray);font-size:0.82rem;margin-bottom:1.5rem;">Operational access</p>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> View &amp; manage bookings</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> Manage event schedule</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> View &amp; record client feedback</div>
            <div class="lp-role-perm"><i class="bi bi-check-circle-fill"></i> View reports &amp; analytics</div>
            <div class="lp-role-perm" style="opacity:0.35;"><i class="bi bi-x-circle-fill" style="color:#dc2626;"></i> Cannot manage staff accounts</div>
            <a href="login.php" style="display:inline-flex;align-items:center;gap:0.4rem;margin-top:1.5rem;background:var(--red);color:#fff;border-radius:50px;padding:0.6rem 1.4rem;font-size:0.88rem;font-weight:600;">
              <i class="bi bi-box-arrow-in-right"></i> Login as Staff
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="lp-cta">
    <div class="container position-relative" style="z-index:1;">
      <p class="lp-pre" style="color:rgba(255,255,255,0.5);">Get Started</p>
      <h2 class="lp-title" style="color:#fff;max-width:500px;margin:0 auto 1rem;">Ready to Streamline <span style="color:var(--gold);">Operations?</span></h2>
      <p class="lp-body" style="color:rgba(255,255,255,0.7);max-width:450px;margin:0 auto 2.5rem;">Log in with your staff credentials to start managing bookings, schedules, and more.</p>
      <a href="login.php" class="lp-btn-primary" id="ctaBtn" style="font-size:1rem;padding:1rem 2.5rem;display:inline-flex;">
        <i class="bi bi-box-arrow-in-right"></i> Access Staff Portal
      </a>
    </div>
  </section>

  <footer class="lp-footer">
    <div class="container">
      <div class="row align-items-center g-3">
        <div class="col-md-6">
          <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.4rem;">
            <i class="bi bi-award-fill" style="color:var(--red);font-size:1.1rem;"></i>
            <span style="font-family:'Playfair Display',serif;color:#fff;font-size:1rem;">Medy's Catering</span>
          </div>
          <p style="margin:0;font-size:0.8rem;">Staff Management System &mdash; For internal use only.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p style="margin:0;font-size:0.78rem;">&copy; 2025 Medy's Catering. All rights reserved.</p>
          <p style="margin:0;font-size:0.75rem;opacity:0.5;">Developed as part of PUP research project.</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script>
    window.addEventListener('scroll', () => {
      document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 40);
    });

    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
      });
    });

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.anim').forEach(el => observer.observe(el));

    const counterObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target, target = parseInt(el.dataset.target);
        let count = 0;
        const step = Math.ceil(target / 50);
        const timer = setInterval(() => {
          count = Math.min(count + step, target);
          el.textContent = count + '+';
          if (count >= target) clearInterval(timer);
        }, 35);
        counterObserver.unobserve(el);
      });
    }, { threshold: 0.5 });
    document.querySelectorAll('.lp-stat-num[data-target]').forEach(el => counterObserver.observe(el));

    if (sessionStorage.getItem('mc_user')) {
      const ctaBtn   = document.getElementById('ctaBtn');
      const heroCta  = document.getElementById('heroCta');
      if (ctaBtn)  { ctaBtn.href  = 'dashboard.php'; ctaBtn.innerHTML  = '<i class="bi bi-grid-fill"></i> Go to Dashboard'; }
      if (heroCta) { heroCta.href = 'dashboard.php'; heroCta.innerHTML = '<i class="bi bi-grid-fill"></i> Go to Dashboard'; }
    }
  </script>
</body>
</html>
