<?php
session_start();

$siteName = "Paglaot";
$tagline = "Pearl of the Orient Sea";
$activePage = "Destinations";

$navLinks = [
    "Our Ships" => "../ourships/LostCities.php",
    "Book a Cruise" => "../bookacruise/booking.php",
    "Destinations" => "destination.php",
    "About" => "../../navbar/about.php"
];

$regions = [
    "Luzon" => [
        ["name" => "Subic", "data" => "subic"],
        ["name" => "Puerto Princesa", "data" => "puerto-prinsesa"],
        ["name" => "La Union", "data" => "la-union"]
    ],
    "Visayas" => [
        ["name" => "Cebu", "data" => "cebu"],
        ["name" => "Caticlan", "data" => "caticlan"],
        ["name" => "Iloilo", "data" => "iloilo"]
    ],
    "Mindanao" => [
        ["name" => "Davao", "data" => "davao"],
        ["name" => "Galas", "data" => "galas"],
        ["name" => "Ozamiz", "data" => "ozamiz"]
    ]
];

$heroImages = [
    "assets_mainD/view1.png",
    "assets_mainD/view2.png",
    "assets_mainD/view3.png"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations | Alena</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
    <div class="nav-inner">

        <a href="../../index.php" class="logo">
            <img src="../../static/logo.svg" alt="Logo" class="logo-icon">

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

            <div style="display:flex; align-items:center; gap:12px;">

                <a href="../profile/profile.php" style="text-decoration:none; color:inherit;">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                    background:rgba(255,255,255,0.08);
                    padding:8px 14px;
                    border-radius:30px;
                    border:1px solid rgba(255,255,255,0.15);
                    cursor:pointer;
                ">

                    <div style="
                        width:34px;
                        height:34px;
                        border-radius:50%;
                        background:#67B5D1;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        color:white;
                        font-weight:700;
                        font-size:.9rem;
                    ">
                        <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
                    </div>

                    <span style="
                        color:white;
                        font-size:.88rem;
                        font-weight:500;
                        max-width:120px;
                        overflow:hidden;
                        text-overflow:ellipsis;
                        white-space:nowrap;
                    ">
                        <?php echo $_SESSION['user']; ?>
                    </span>

                </div>

                </a>

                <a href="../logout.php" class="btn-signin">Log Out</a>

            </div>

        <?php else: ?>

            <a href="../login.php" class="btn-signin">Sign In</a>

        <?php endif; ?>

        <button class="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

<main class="frame">

    <img src="assets_mainD/cruise.png" alt="Cruise background" class="bg-image">

    <div class="backdrop"></div>
    <div class="backdrop-glass"></div>

    <aside class="sidebar">

        <?php foreach ($regions as $regionName => $destinations): ?>

            <section class="region">
                <h2>
                    <?php echo $regionName; ?>
                    <span class="chevron">&#9654;</span>
                </h2>

                <ul>
                    <?php foreach ($destinations as $destination): ?>
                        <li>
                            <a href="#"
                               class="destination-link"
                               data-destination="<?php echo $destination['data']; ?>">
                                <?php echo $destination['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

        <?php endforeach; ?>

    </aside>

    <section class="content" id="destinationContent">

        <div class="section-banner">The Destinations</div>

        <div class="hero-card">
            <div class="hero-slider">
                <div class="hero-track" id="heroTrack">

                    <?php foreach ($heroImages as $index => $image): ?>
                        <div class="hero-slide">
                            <img src="<?php echo $image; ?>" alt="Destination <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

        <div class="destination-info">
            <h1>Welcome to The Destinations</h1>
            <p>Select a destination from the left side to view more details.</p>
        </div>

        <div class="slider">
            <?php foreach ($heroImages as $index => $image): ?>
                <button class="slider-pill <?php echo ($index === 0) ? 'active' : ''; ?>"
                        data-slide="<?php echo $index; ?>"
                        aria-label="Show image <?php echo $index + 1; ?>">
                </button>
            <?php endforeach; ?>
        </div>

    </section>

</main>

<script src="script.js"></script>

</body>
</html>
