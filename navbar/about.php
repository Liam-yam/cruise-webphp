<?php
session_start();
$siteName = "Paglaot";
$tagline  = "Pearl of the Orient Sea";
$activePage = "About";

$navLinks = [
  'Our Ships'     => 'ourships/LostCities.php',
  'Book a Cruise' => 'bookacruise/booking.php',
  'Destinations'  => 'destination/destination.php',
  'About'         => 'about.php',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us — Paglaot</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../style.css" />

  <style>
    /* ─── ABOUT PAGE STYLES ─── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:   #0a1628;
      --navy2:  #0d1e38;
      --blue:   #67B5D1;
      --gold:   #c9b99a;
      --gold2:  #e8d9c0;
      --white:  #ffffff;
      --muted:  rgba(255,255,255,0.55);
    }

    body {
      background: var(--navy);
      color: var(--white);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
    }

    /* ── Breadcrumb ── */
    .breadcrumb {
      padding: 1.1rem 2.5rem;
      font-size: 0.78rem;
      color: var(--muted);
      letter-spacing: 0.06em;
    }
    .breadcrumb a { color: var(--muted); text-decoration: none; }
    .breadcrumb a:hover { color: var(--gold); }
    .breadcrumb span { color: var(--gold); }

    /* ── About Hero Section ── */
    .about-hero {
      text-align: center;
      padding: 3.5rem 1.5rem 0;
      max-width: 860px;
      margin: 0 auto;
    }

    .about-eyebrow {
      display: inline-block;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--blue);
      margin-bottom: 1rem;
      opacity: 0;
      animation: fadeUp 0.7s ease forwards 0.1s;
    }

    .about-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.6rem, 5.5vw, 4rem);
      font-weight: 600;
      color: var(--white);
      line-height: 1.15;
      margin-bottom: 1.6rem;
      opacity: 0;
      animation: fadeUp 0.7s ease forwards 0.25s;
    }

    .about-title em {
      color: var(--gold);
      font-style: italic;
    }

    /* ── Message From Us ── */
    .about-message {
      max-width: 680px;
      margin: 0 auto 3.5rem;
      text-align: center;
      opacity: 0;
      animation: fadeUp 0.7s ease forwards 0.4s;
    }

    .about-message p {
      font-size: 1.05rem;
      line-height: 1.85;
      color: rgba(255,255,255,0.72);
      font-weight: 300;
    }

    .about-message p + p {
      margin-top: 1.1rem;
    }

    .about-message .highlight {
      color: var(--gold2);
      font-weight: 500;
    }

    /* ── Divider ── */
    .ornament-divider {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      margin: 0 auto 3.5rem;
      max-width: 400px;
      opacity: 0;
      animation: fadeUp 0.6s ease forwards 0.55s;
    }
    .ornament-divider::before,
    .ornament-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(to right, transparent, var(--gold));
    }
    .ornament-divider::after {
      background: linear-gradient(to left, transparent, var(--gold));
    }
    .ornament-diamond {
      width: 8px; height: 8px;
      background: var(--gold);
      transform: rotate(45deg);
      flex-shrink: 0;
    }

    /* ── Center Photo ── */
    .about-photo-wrap {
      max-width: 900px;
      margin: 0 auto 5rem;
      padding: 0 1.5rem;
      opacity: 0;
      animation: fadeUp 0.85s ease forwards 0.65s;
    }

    .about-photo-frame {
      position: relative;
      border-radius: 6px;
      overflow: hidden;
      box-shadow:
        0 30px 80px rgba(0,0,0,0.6),
        0 0 0 1px rgba(201,185,154,0.25);
    }

    .about-photo-frame::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        transparent 55%,
        rgba(10,22,40,0.65) 100%
      );
      z-index: 1;
      pointer-events: none;
    }

    .about-photo-frame img {
      display: block;
      width: 100%;
      height: auto;
      object-fit: cover;
    }

    /* Caption bar at bottom of photo */
    .photo-caption {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      z-index: 2;
      padding: 1.5rem 2rem;
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
    }

    .photo-caption-left {
      font-family: 'Playfair Display', serif;
      font-style: italic;
      font-size: 1.15rem;
      color: rgba(255,255,255,0.85);
      line-height: 1.4;
    }

    .photo-caption-right {
      font-size: 0.72rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--gold);
      white-space: nowrap;
    }

    /* ── Values Strip ── */
    .values-strip {
      display: flex;
      justify-content: center;
      gap: 0;
      max-width: 900px;
      margin: 0 auto 6rem;
      padding: 0 1.5rem;
      opacity: 0;
      animation: fadeUp 0.7s ease forwards 0.85s;
    }

    .value-item {
      flex: 1;
      text-align: center;
      padding: 2rem 1.5rem;
      border-top: 1px solid rgba(201,185,154,0.2);
      border-bottom: 1px solid rgba(201,185,154,0.2);
      position: relative;
    }

    .value-item + .value-item {
      border-left: 1px solid rgba(201,185,154,0.2);
    }

    .value-icon {
      font-size: 1.6rem;
      margin-bottom: 0.75rem;
      display: block;
    }

    .value-label {
      font-family: 'Playfair Display', serif;
      font-size: 1.05rem;
      color: var(--gold2);
      margin-bottom: 0.4rem;
    }

    .value-desc {
      font-size: 0.82rem;
      color: var(--muted);
      line-height: 1.6;
      font-weight: 300;
    }

    /* ── Animations ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Responsive ── */
    @media (max-width: 600px) {
      .about-title { font-size: 2.2rem; }
      .about-message p { font-size: 0.95rem; }
      .values-strip { flex-direction: column; }
      .value-item + .value-item { border-left: none; border-top: 1px solid rgba(201,185,154,0.2); }
      .photo-caption { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>

<body>

  <!-- ═══════════════════════════════ NAV (reuse site nav) ═══ -->
  <nav>
    <div class="nav-inner">
      <a href="../index.php" class="logo">
        <img src="../static/logo.svg" alt="Logo" class="logo-icon" />
        <div class="logo-text">
          <?php echo $siteName; ?>
          <span><?php echo $tagline; ?></span>
        </div>
      </a>

      <ul class="nav-links">
        <?php foreach ($navLinks as $label => $url): ?>
          <li>
            <a href="<?php echo $url; ?>"
               class="<?php echo ($label === $activePage) ? 'active' : ''; ?>">
              <?php echo $label; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

                        <?php if (isset($_SESSION['user'])): ?>
        <a href="../logout.php" class="btn-signin">Log Out</a>
      <?php else: ?>
        <a href="../login.php" class="btn-signin">Sign In</a>
      <?php endif; ?>

      <button class="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>
  <!-- ════════════════════════════════════════════════════════ -->

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="../index.php">Home</a> &nbsp;/&nbsp; <span>About Us</span>
  </div>

  <!-- ═══════════════════════ ABOUT HERO ══════════════════════ -->
  <section class="about-hero">

    <span class="about-eyebrow">Know Everything... About Us</span>

    <h1 class="about-title">
      About <em>Paglaot</em>
    </h1>

  </section>

  <!-- ═══════════════════════ MESSAGE FROM US ═════════════════ -->
  <div class="about-message" style="max-width:680px;margin:0 auto 3.5rem;text-align:center;padding:0 1.5rem;">
    <p>
      <span class="highlight">Paglaot</span> was born from a deep love of the Philippine sea —
      its shifting blues, its hidden coves, and the warmth of the people who call its islands home.
      We set out to create something more than a cruise line: a vessel for stories, for wonder,
      for the kind of journeys you carry long after you've returned to shore.
    </p>
    <p>
      Every route we chart, every ship we sail, every moment we design is guided by one belief —
      that the ocean is not merely a destination. It is the beginning of something
      <span class="highlight">unforgettable</span>.
    </p>
  </div>

  <!-- ═══════════════════════ ORNAMENT ════════════════════════ -->
  <div class="ornament-divider">
    <div class="ornament-diamond"></div>
  </div>

  <!-- ═══════════════════════ CENTER PHOTO ════════════════════ -->
  <div class="about-photo-wrap">
    <div class="about-photo-frame">
      <!--
        Replace the src below with your actual team photo path.
        e.g. src="static/team.jpg"
        The image from the inspiration screenshot can go here directly.
      -->
      <img
        src="../static/group.jpg"
        alt="The Paglaot Team — Pearl of the Orient Sea"
        onerror="this.style.background='linear-gradient(135deg,#0d1e38 0%,#1a3a5c 50%,#0d1e38 100%)';this.style.minHeight='420px';this.removeAttribute('onerror');"
      />
      <div class="photo-caption">
        <div class="photo-caption-left">
          The crew behind every horizon.
        </div>
        <div class="photo-caption-right">Pearl of the Orient Sea</div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════ VALUES STRIP ════════════════════ -->
  <div class="values-strip">
    <div class="value-item">
      <span class="value-icon">⚓</span>
      <div class="value-label">Our Mission</div>
      <div class="value-desc">To connect every Filipino soul to the beauty of the seas that surround us, one unforgettable voyage at a time.</div>
    </div>
    <div class="value-item">
      <span class="value-icon">🧭</span>
      <div class="value-label">Our Vision</div>
      <div class="value-desc">A world where every journey across Philippine waters feels like coming home — no matter how far we sail.</div>
    </div>
    <div class="value-item">
      <span class="value-icon">🌊</span>
      <div class="value-label">Our Promise</div>
      <div class="value-desc">Luxury, warmth, and wonder — delivered with the grace of the Orient Sea on every crossing we make.</div>
    </div>
  </div>

</body>
</html>