const destinationContent = document.getElementById("destinationContent");
const destinationLinks = document.querySelectorAll(".destination-link");

const destinations = {
  cebu: {
    banner: "Cebu",
    title: "Cebu Destination",
    description: "Set sail toward Cebu, a premier tropical gateway where vibrant marine life and crystal-clear waters welcome every cruise traveler. Glide past stunning coastlines and discover secluded sandbars that appear like hidden treasures at low tide. From luxurious island stops to unforgettable sunset horizons, Cebu offers a perfect blend of adventure and elegance—an essential highlight on any Philippine cruise itinerary.",
    images: [
      "assets_mainD/view1.png",
      "assets_mainD/view2.png",
      "assets_mainD/view3.png"
    ]
  },
  caticlan: {
    banner: "Caticlan",
    title: "Caticlan Destination",
    description: "Arriving in Caticlan means entering a serene coastal haven just moments away from world-famous shores. Cruise guests are treated to peaceful beaches, authentic local charm, and breathtaking sunrise views over calm waters. It’s more than a gateway—it’s a tranquil prelude to paradise, where every arrival feels exclusive and unspoiled.",
    images: [
      "assets_mainD/view2.png",
      "assets_mainD/view3.png",
      "assets_mainD/view1.png"
    ]
  },
  iloilo: {
    banner: "Ilo-ilo",
    title: "Ilo-ilo Destination",
    description: "Explore a coastline rich in natural beauty and cultural depth. Offshore, the Gigantes Islands showcase dramatic limestone cliffs, hidden coves, and pristine white sands. Each stop reveals a new secret—perfect for travelers seeking both scenic wonder and authentic island experiences.",
    images: [
      "assets_mainD/view3.png",
      "assets_mainD/view1.png",
      "assets_mainD/view2.png"
    ]
  },
  subic: {
    banner: "Subic",
    title: "Subic Destination",
    description: "Calm waters and lush mountain backdrops create a striking arrival. Known for its deep harbor, Subic is ideal for larger vessels and offers unique experiences like wreck diving and eco-adventures. Here, history meets nature beneath the waves, making every stop both scenic and unforgettable.",
    images: [
      "assets_mainD/view1.png",
      "assets_mainD/view3.png",
      "assets_mainD/view2.png"
    ]
  },
  "puerto-prinsesa": {
    banner: "Puerto Prinsesa",
    title: "Puerto Prinsesa Destination",
    description: "Puerto Princesa is a crown jewel of cruise destinations, offering pristine coastlines and world-renowned natural wonders. Passengers can journey to the breathtaking Puerto Princesa Underground River, a UNESCO-listed marvel. With its emerald waters and untouched landscapes, this destination delivers a truly immersive escape into nature.",
    images: [
      "assets_mainD/view2.png",
      "assets_mainD/view1.png",
      "assets_mainD/view3.png"
    ]
  },
  "la-union": {
    banner: "La Union",
    title: "La Union Destination",
    description: "La Union welcomes cruise travelers with its vibrant coastal energy and golden shorelines. Known for its rolling waves and dynamic seaside culture, it’s a perfect stop for those seeking both relaxation and activity. As the sun sets over the West Philippine Sea, guests are treated to one of the most captivating coastal views in the region.",
    images: [
      "assets_mainD/view3.png",
      "assets_mainD/view2.png",
      "assets_mainD/view1.png"
    ]
  },
  davao: {
    banner: "Davao",
    title: "Davao Destination",
    description: "Davao offers a seamless blend of urban sophistication and island adventure. Cruise itineraries often include nearby Samal Island, where powdery beaches and clear waters await. This destination stands out for its balance—modern comforts paired with untouched marine beauty.",
    images: [
      "assets_mainD/view1.png",
      "assets_mainD/view2.png",
      "assets_mainD/view3.png"
    ]
  },
  galas: {
    banner: "Galas",
    title: "Galas Destination",
    description: "Glan is an emerging cruise destination known for its long, pristine beaches and peaceful coastal atmosphere. Crowded tourist hubs, it offers an exclusive escape where nature remains beautifully untouched. It’s the perfect stop for travelers seeking privacy, serenity, and authentic coastal charm.",
    images: [
      "assets_mainD/view2.png",
      "assets_mainD/view3.png",
      "assets_mainD/view1.png"
    ]
  },
  ozamiz: {
    banner: "Ozamiz",
    title: "Ozamiz Destination",
    description: "Ozamiz provides a relaxed and scenic cruise stop along Mindanao’s northern coast. With calm seas, welcoming communities, and picturesque sunsets, it offers a gentle, laid-back experience. Travelers who appreciate quiet beauty and cultural authenticity, Ozamiz completes a diverse and enriching cruise journey.",
    images: [
      "assets_mainD/view3.png",
      "assets_mainD/view1.png",
      "assets_mainD/view2.png"
    ]
  }
};

let currentSlide = 0;
let slideTimer;

function renderDestination(destinationKey) {
  const destination = destinations[destinationKey];

  destinationContent.innerHTML = `
    <div class="section-banner">${destination.banner}</div>

    <div class="hero-card">
      <div class="hero-slider">
        <div class="hero-track" id="heroTrack">
          ${destination.images
            .map((image, index) => `
              <div class="hero-slide">
                <img src="${image}" alt="${destination.title} image ${index + 1}" />
              </div>
            `)
            .join("")}
        </div>
      </div>
    </div>

    <div class="destination-info">
      <h1>${destination.title}</h1>
      <p>${destination.description}</p>
    </div>

    <div class="slider">
      ${destination.images
        .map((image, index) => `
          <button
            class="slider-pill ${index === 0 ? "active" : ""}"
            data-slide="${index}"
            aria-label="Show image ${index + 1}">
          </button>
        `)
        .join("")}
    </div>
  `;

  startSlider();
}

function showSlide(index) {
  const track = document.getElementById("heroTrack");
  const pills = document.querySelectorAll(".slider-pill");

  if (!track || pills.length === 0) {
    return;
  }

  currentSlide = index;
  track.style.transform = `translateX(-${index * 100}%)`;

  pills.forEach((pill, i) => {
    pill.classList.toggle("active", i === index);
  });
}

function startSlider() {
  const pills = document.querySelectorAll(".slider-pill");
  currentSlide = 0;

  clearInterval(slideTimer);

  pills.forEach((pill) => {
    pill.addEventListener("click", () => {
      showSlide(Number(pill.dataset.slide));
    });
  });

  showSlide(0);

  slideTimer = setInterval(() => {
    const totalSlides = document.querySelectorAll(".slider-pill").length;
    let nextSlide = currentSlide + 1;

    if (nextSlide >= totalSlides) {
      nextSlide = 0;
    }

    showSlide(nextSlide);
  }, 3000);
}

destinationLinks.forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();

    destinationLinks.forEach((item) => {
      item.classList.remove("active-destination");
    });

    link.classList.add("active-destination");

    renderDestination(link.dataset.destination);
  });
});

startSlider();