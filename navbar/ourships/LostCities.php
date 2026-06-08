<?php
session_start();

include 'ourships.php';

$siteName = "Paglaot";
$tagline = "Pearl of the Orient Sea";
$activePage = "Our Ships";

$navLinks = [
    "Our Ships" => "LostCities.php",
    "Book a Cruise" => "../bookacruise/booking.php",
    "Destinations" => "../destination/destination.php",
    "About" => "../../navbar/about.php"
];

$amenities = new Amenities();

$spa = $amenities->getSpaInfo();
$fitness = $amenities->getFitnessInfo();
$night = $amenities->getNightInfo();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Cruise of Lost Cities</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="page">

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

    <div class="hero">
        <h1>THE CRUISE OF LOST CITIES</h1>
        <p>Uncover the secrets of the abyss. Experience luxury beneath the waves.</p>
    </div>

    <section>
        <h2>ENTERTAINMENT</h2>

        <div class="entertainment-box">

            <div class="ent-card">
                <h3>Live Band</h3>
                <img src="images/Live Band.png" alt="Live Band">
                <p>Splash into aboard different cultural entertaining events.</p>
            </div>

            <div class="ent-card">
                <h3>Fireworks in Sea</h3>
                <img src="images/Fireworks.png" alt="Fireworks">
                <p>Uncover the secrets of the abyss. Experience luxury beneath the waves.</p>
            </div>

            <div class="ent-card">
                <h3>Pool to the Sea</h3>
                <img src="images/Pool.png" alt="Pool">
                <p>Uncover the secrets of the abyss. Experience luxury beneath the waves.</p>
            </div>

        </div>
    </section>

    <section class="amenities-section">
        <h2>AMENITIES</h2>

        <div class="amenities-box">

            <div class="amenity-row">
                <img src="images/Spa.png" alt="Spa and Salon">

                <div class="amenity-info">
                    <h3><?php echo $spa['title']; ?></h3>
                    <p><?php echo $spa['description']; ?></p>
                    <p>
                        Haircut: PHP 350<br>
                        Manicure: PHP 600<br>
                        Open every <?php echo $spa['time']; ?>
                    </p>
                </div>

                <div class="amenity-desc">
                    <p>Splash it up in freshwater pools, aqua play areas and waterslides designed for kids, families and adults.</p>
                </div>
            </div>

            <div class="amenity-row">
                <img src="images/Sports.png" alt="Sports and Fitness">

                <div class="amenity-info">
                    <h3><?php echo $fitness['title']; ?></h3>
                    <p><?php echo $fitness['description']; ?></p>
                    <p>
                        Gym Access: Free<br>
                        Fitness Coach: PHP 500<br>
                        Open every <?php echo $fitness['time']; ?>
                    </p>
                </div>

                <div class="amenity-desc">
                    <p>Stay active with modern fitness equipment, workout areas, and wellness activities while enjoying ocean views.</p>
                </div>
            </div>

            <div class="amenity-row">
                <img src="images/Club.png" alt="Nightclub Lounges">

                <div class="amenity-info">
                    <h3><?php echo $night['title']; ?></h3>
                    <p><?php echo $night['description']; ?></p>
                    <p>
                        Lounge Access: Free<br>
                        Special Drinks: PHP 250<br>
                        Open every <?php echo $night['time']; ?>
                    </p>
                </div>

                <div class="amenity-desc">
                    <p>Enjoy music, lights, drinks, and a relaxing nightlife atmosphere while sailing across the sea.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="section">
        <h2>DINING</h2>

        <div class="dining">

            <div class="food-card">
                <img src="images/Seafoods.png" alt="Seafoods">
                <h3>Seafoods</h3>
                <p>Enjoy freshly prepared seafood meals inspired by the richness of the ocean.</p>
            </div>

            <div class="food-card">
                <img src="images/Casual.png" alt="Casual Dining">
                <h3>Casual Dining</h3>
                <p>Relax with simple, comforting, and delicious meals perfect for every guest.</p>
            </div>

            <div class="food-card">
                <img src="images/filipino.png" alt="Filipino Cuisine">
                <h3>Filipino Cuisine</h3>
                <p>Taste classic Filipino dishes that bring warmth, culture, and flavor aboard.</p>
            </div>

        </div>
    </section>

    <div style="text-align: center; width: 100%;">
    <a href="../bookacruise/booking.php" class="btn" id="bookBtn">BOOK NOW!</a>
</div>


</div>


</body>
</html>
