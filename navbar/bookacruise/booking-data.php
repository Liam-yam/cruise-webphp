<?php
require_once dirname(__DIR__, 2) . "/db.php";

class BookingData {
  public $siteName;
  public $tagline;
  public $pageTitle;
  public $activePage;
  public $bookingMessage;

  public $navLinks;
  public $ships;
  public $tripDates;
  public $tierPrices;
  public $promoPrices;
  public $packages;

  public $selectedShip;
  public $selectedTier;

  public function __construct() {
    $this->siteName = "Paglaot";
    $this->tagline = "Pearl of the Orient Sea";
    $this->pageTitle = "Book Cruise";
    $this->activePage = "Book a Cruise";
    $this->bookingMessage = "";

        $this->navLinks = [
      "Our Ships" => "../ourships/LostCities.php",
      "Book a Cruise" => "booking.php",
      "Destinations" => "../destination/destination.php",
      "About" => "../../navbar/about.php"
    ];

    $this->ships = ["Tropical", "Lost Cities", "Masquerade"];

    $this->tripDates = [
      "Tropical" => [
        "June 2 - June 6, 2026",
        "June 16 - June 20, 2026",
        "June 30 - July 4, 2026",
        "July 14 - July 18, 2026"
      ],
      "Lost Cities" => [
        "June 12 - June 16, 2026",
        "June 26 - June 30, 2026",
        "July 10 - July 14, 2026",
        "July 24 - July 28, 2026"
      ],
      "Masquerade" => [
        "June 7 - June 11, 2026",
        "June 23 - June 27, 2026",
        "July 9 - July 13, 2026",
        "July 25 - July 29, 2026"
      ]
    ];

    $this->tierPrices = [
      "PREMIUM" => 32879,
      "ELITE LUX" => 37987,
      "ROYALTY" => 49879
    ];

    $this->promoPrices = [
      "PREMIUM" => 60684,
      "ELITE LUX" => 72584,
      "ROYALTY" => 95684
    ];

    $this->packages = [
      "PREMIUM" => [
        "grade" => "B",
        "class" => "package-premium",
        "image" => "assets/premium.png",
        "alt" => "Premium cabin",
        "features" => [
          "Comfortable interior or basic ocean-view cabin",
          "Access to main dining & buffet",
          "Complimentary non-alcoholic drinks",
          "Access to pool, gym, and shows",
          "Daily housekeeping",
          "Basic onboard activities"
        ]
      ],
      "ELITE LUX" => [
        "grade" => "A",
        "class" => "package-elite",
        "image" => "assets/elite.png",
        "alt" => "Elite lux cabin",
        "features" => [
          "Spacious ocean-view cabin (better location)",
          "Priority check-in & boarding",
          "Access to all premium dining",
          "Complimentary drinks (selected alcoholic included)",
          "Free Wi-Fi (standard)",
          "Spa discounts & wellness access",
          "Pool Access",
          "Reserved seating for shows",
          "Room service (limited)"
        ]
      ],
      "ROYALTY" => [
        "grade" => "S",
        "class" => "package-royalty",
        "image" => "assets/royalty.png",
        "alt" => "Royalty suite cabin",
        "features" => [
          "Luxury suite with balcony",
          "VIP priority boarding & exit",
          "Personal butler / concierge",
          "Unlimited premium dining",
          "Unlimited drinks (premium alcohol included)",
          "High-speed Wi-Fi",
          "Private lounges, pool & deck",
          "Complimentary spa treatments",
          "VIP seating for shows",
          "Private excursions",
          "24/7 room service"
        ]
      ]
    ];

    $this->selectedShip = $this->cleanText($_GET["ship"] ?? "");
    $this->selectedTier = $this->cleanText($_GET["tier"] ?? "");

    if (!array_key_exists($this->selectedShip, $this->tripDates)) {
      $this->selectedShip = "";
    }

    if (!array_key_exists($this->selectedTier, $this->tierPrices)) {
      $this->selectedTier = "";
    }

    $this->handleBooking();
  }

  public function cleanText($value) {
    return trim($value);
  }

  public function formatPeso($amount) {
    return "&#8369;" . number_format($amount);
  }

  public function getTierLabel($tier) {
    switch ($tier) {
      case "PREMIUM":
        return "Premium Cabin";
      case "ELITE LUX":
        return "Elite Lux Cabin";
      case "ROYALTY":
        return "Royalty Suite";
      default:
        return "Cruise Cabin";
    }
  }

  public function getBookingTotal($tier, $adults, $children) {
    $adultPrice = $this->tierPrices[$tier];
    $childPrice = $adultPrice * 0.5;

    return ($adults * $adultPrice) + ($children * $childPrice);
  }

    public function getDepartureDate($tripDate) {
    $parts = array_map("trim", explode("-", $tripDate));
    $tripEndText = end($parts) . ", 2026";
    $timestamp = strtotime($tripEndText);

    return $timestamp ? date("Y-m-d", $timestamp) : null;
  }

  private function requireLogin() {
    if (!isset($_SESSION["user"])) {
      header("Location: ../login.php");
      exit();
    }
  }

    private function resolveUserId($conn) {
    $userEmail = $_SESSION["user_email"] ?? "";

    if (empty($userEmail)) {
      return null;
    }

    $stmt = $conn->prepare(
      "SELECT u.user_id
         FROM tbl_user u
         JOIN tbl_registration r ON r.user_id = u.user_id
        WHERE r.email = ?
        LIMIT 1"
    );

    if (!$stmt) {
      return null;
    }

    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row ? (int) $row["user_id"] : null;
  }

  private function resolveTicketId($conn, $ship, $tier, $departureDate) {
    $stmt = $conn->prepare(
      "SELECT ticket_no
         FROM tbl_ticket
        WHERE cruise_ship = ?
          AND ticket_tier = ?
          AND departure_date = ?
        LIMIT 1"
    );

    if (!$stmt) {
      return null;
    }

    $stmt->bind_param("sss", $ship, $tier, $departureDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row ? (int) $row["ticket_no"] : null;
  }

  private function handlePayment() {
    global $conn;

    if (!isset($_POST["confirm_payment"])) {
      return false;
    }

    $this->requireLogin();

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      unset($_SESSION["pending_booking"]);
    }

    $pendingBooking = $_SESSION["pending_booking"] ?? null;

    if (empty($pendingBooking)) {
      $this->bookingMessage = "Please complete the booking form before payment.";
      return true;
    }

    $paymentMethod = $this->cleanText($_POST["payment_method"] ?? "");
    $paymentReference = $this->getPaymentReference($paymentMethod);

    if (empty($paymentMethod) || empty($paymentReference)) {
      $this->bookingMessage = "Please complete the payment details.";
      return true;
    }

    $userId = $this->resolveUserId($conn);

    if (!$userId) {
      $this->bookingMessage = "Your account is not linked to a user record. Please contact support.";
      return true;
    }

    $ticketId = $this->resolveTicketId(
      $conn,
      $pendingBooking["ship"],
      $pendingBooking["tier"],
      $pendingBooking["departure_date"]
    );

    if (!$ticketId) {
      $this->bookingMessage = "No ticket is configured for the selected ship, tier, and date.";
      return true;
    }

    $status = "paid";

        $bookingStmt = $conn->prepare(
          "INSERT INTO tbl_booking (user_id, ticket_no, adults, children, total_price, status, group_tag)
           VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

    $paymentStmt = $conn->prepare(
      "INSERT INTO tbl_payment (order_no, payment_method, ref_no, amount_paid, paid_date, paid_time)
       VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$bookingStmt || !$paymentStmt) {
      $this->bookingMessage = "Payment cannot be processed until the booking and payment tables exist.";
      return true;
    }

    $adults = (int) $pendingBooking["adults"];
    $children = (int) $pendingBooking["children"];
    $total = (float) $pendingBooking["total"];
    $paidDate = date("Y-m-d");
    $paidTime = date("H:i:s");

    $guestCount = $adults + $children;
    if ($guestCount < 1) {
      $this->bookingMessage = "Please include at least one guest.";
      return true;
    }

    $adultPrice = isset($this->tierPrices[$pendingBooking["tier"]])
      ? (float) $this->tierPrices[$pendingBooking["tier"]]
      : 0;
    $childPrice = $adultPrice * 0.5;
    $pricePerAdult = $adultPrice;
    $pricePerChild = $childPrice;

        $conn->begin_transaction();

    $firstOrderNo = null;
    $orderNumbers = [];
    $groupTag = "GRP-" . date("Ymd") . "-" . bin2hex(random_bytes(3));

    for ($i = 0; $i < $guestCount; $i++) {
      $isChild = ($i >= $adults);
      $guestAdults = $isChild ? 0 : 1;
      $guestChildren = $isChild ? 1 : 0;
      $guestTotal = $isChild ? $pricePerChild : $pricePerAdult;

      $bookingStmt->bind_param(
        "iiiidss",
        $userId,
        $ticketId,
        $guestAdults,
        $guestChildren,
        $guestTotal,
        $status,
        $groupTag
      );

      if (!$bookingStmt->execute()) {
        $conn->rollback();
        $this->bookingMessage = "Booking failed. Please try again.";
        return true;
      }

      $orderNo = $conn->insert_id;
      $orderNumbers[] = $orderNo;
      if ($firstOrderNo === null) {
        $firstOrderNo = $orderNo;
      }
    }

    $paymentStmt->bind_param(
      "isdsss",
      $firstOrderNo,
      $paymentMethod,
      $paymentReference,
      $total,
      $paidDate,
      $paidTime
    );

    if (!$paymentStmt->execute()) {
      $conn->rollback();
      $this->bookingMessage = "Payment failed. Please try again.";
      return true;
    }

    $conn->commit();

    $_SESSION["paid_ticket"] = [
      "order_no"        => $firstOrderNo,
      "order_numbers"   => $orderNumbers,
      "user_id"         => $userId,
      "ticket_no"       => $ticketId,
      "user_name"       => $_SESSION["user"],
      "user_email"      => $_SESSION["user_email"] ?? "",
      "ship"            => $pendingBooking["ship"],
      "trip_date"       => $pendingBooking["trip_date"],
      "departure_date"  => $pendingBooking["departure_date"],
      "tier"            => $pendingBooking["tier"],
      "adults"          => $adults,
      "children"        => $children,
      "total"           => $total,
      "payment_method"  => $paymentMethod,
      "issued_at"       => $paidDate . " " . $paidTime
    ];

    unset($_SESSION["pending_booking"]);
    header("Location: booking.php?ticket=success");
    exit();
  }

  private function getPaymentReference($paymentMethod) {
    switch ($paymentMethod) {
      case "GCash":
        $num = preg_replace("/\D/", "", $_POST["gcash_number"] ?? "");
        if (!preg_match('/^09[0-9]{9}$/', $num)) {
          $_SESSION["payment_error"] = "Invalid GCash number. It must be 11 digits starting with 09.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        $ref = $this->cleanText($_POST["gcash_reference"] ?? "");
        if (!preg_match('/^[0-9]{13}$/', $ref)) {
          $_SESSION["payment_error"] = "Invalid GCash reference number. It must be exactly 13 digits.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return $ref;
      case "Maya":
        $num = preg_replace("/\D/", "", $_POST["maya_number"] ?? "");
        if (!preg_match('/^09[0-9]{9}$/', $num)) {
          $_SESSION["payment_error"] = "Invalid Maya number. It must be 11 digits starting with 09.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        $ref = $this->cleanText($_POST["maya_reference"] ?? "");
        if (!preg_match('/^[A-Za-z0-9]{12}$/', $ref)) {
          $_SESSION["payment_error"] = "Invalid Maya reference number. It must be exactly 12 alphanumeric characters.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return $ref;
      case "BPI":
        $cardNumber = preg_replace("/\D/", "", $_POST["bpi_card_number"] ?? "");
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
          $_SESSION["payment_error"] = "Invalid BPI card number. It must be between 13 and 19 digits.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $_POST["bpi_card_expiry"] ?? "")) {
          $_SESSION["payment_error"] = "Invalid BPI card expiry. Use MM/YY format.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        if (!preg_match('/^[0-9]{3,4}$/', $_POST["bpi_card_cvv"] ?? "")) {
          $_SESSION["payment_error"] = "Invalid BPI CVV. It must be 3 or 4 digits.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return strlen($cardNumber) >= 4 ? "Card ending " . substr($cardNumber, -4) : "";
      case "Visa":
      case "Mastercard":
      case "JCB":
        $cardNumber = preg_replace("/\D/", "", $_POST["card_number"] ?? "");
        return strlen($cardNumber) >= 4 ? "Card ending " . substr($cardNumber, -4) : "";
      default:
        return "";
    }
  }

  private function handleBooking() {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
      return;
    }

    if ($this->handlePayment()) {
      return;
    }

    $this->requireLogin();

    $postedShip = $this->cleanText($_POST["cruise_ship"] ?? "");
    $postedDate = $this->cleanText($_POST["trip_date"] ?? "");
    $postedTier = $this->cleanText($_POST["tier"] ?? "");
    $adults = $this->cleanText($_POST["adults"] ?? "1");
    $children = $this->cleanText($_POST["children"] ?? "0");

    if (empty($postedShip) || empty($postedDate) || empty($postedTier)) {
      $this->bookingMessage = "Please complete the booking form first.";
    } elseif (!is_numeric($adults) || !is_numeric($children)) {
      $this->bookingMessage = "Guest count must be numeric.";
    } elseif (!array_key_exists($postedShip, $this->tripDates) || !in_array($postedDate, $this->tripDates[$postedShip])) {
      $this->bookingMessage = "Please choose a valid cruise date.";
    } elseif (!array_key_exists($postedTier, $this->tierPrices)) {
      $this->bookingMessage = "Please choose a valid tier.";
    } else {
      $adults = (int) $adults;
      $children = (int) $children;
      $total = $this->getBookingTotal($postedTier, $adults, $children);
      $departureDate = $this->getDepartureDate($postedDate);

      $_SESSION["pending_booking"] = [
        "ship" => $postedShip,
        "trip_date" => $postedDate,
        "departure_date" => $departureDate,
        "tier" => $postedTier,
        "adults" => $adults,
        "children" => $children,
        "total" => $total
      ];

      $this->bookingMessage = "Review your booking and complete payment below.";
    }
  }
}

$booking = new BookingData();

$siteName = $booking->siteName;
$tagline = $booking->tagline;
$pageTitle = $booking->pageTitle;
$activePage = $booking->activePage;
$bookingMessage = $booking->bookingMessage;

$navLinks = $booking->navLinks;
$ships = $booking->ships;
$tripDates = $booking->tripDates;
$tierPrices = $booking->tierPrices;
$promoPrices = $booking->promoPrices;
$packages = $booking->packages;

$selectedShip = $booking->selectedShip;
$selectedTier = $booking->selectedTier;
$pendingBooking = $_SESSION["pending_booking"] ?? null;
$paidTicket = $_SESSION["paid_ticket"] ?? null;

if (!empty($paidTicket)) {
  unset($_SESSION["paid_ticket"]);
}

function formatPeso($amount) {
  global $booking;
  return $booking->formatPeso($amount);
}

function getTierLabel($tier) {
  global $booking;
  return $booking->getTierLabel($tier);
}
?>
