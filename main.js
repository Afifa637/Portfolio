const navMenu = document.getElementById('nav-menu');
const navToggle = document.getElementById('nav-toggle');

// Toggle drawer + animate icon
navToggle.addEventListener('click', () => {
  navMenu.classList.toggle('active');
  navToggle.classList.toggle('active');
});

// Close drawer when a nav link is clicked
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    navMenu.classList.remove('active');
    navToggle.classList.remove('active');
  });
});

function toggleReadMore() {
    const moreText = document.getElementById("moreText");
    const btn = document.getElementById("readMoreBtn");

    if (moreText.style.display === "block") {
      // Hide the content
      moreText.style.display = "none";
      btn.textContent = "Read More";
    } else {
      // Show the content
      moreText.style.display = "block";
      btn.textContent = "Read Less";
    }
  }

  // Ensure the read-more content is hidden on page load
  document.addEventListener("DOMContentLoaded", () => {
    const moreText = document.getElementById("moreText");
    if (moreText) {
      moreText.style.display = "none";
    }
  });

// Scroll section active link

let sections = document.querySelectorAll('section');
let navLinks = document.querySelectorAll('.header nav a');

window.onscroll = () => {
    sections.forEach(section => {
        let top = window.scrollY;
        let offset = section.offsetTop - 150;
        let height = section.offsetHeight;
        let id = section.getAttribute('id');

        if(top >= offset && top < offset + height) {
            navLinks.forEach(links => {
                links.classList.remove('active');
                document.querySelector('.header nav a[href*=' + id + ']').classList.add('active');
            });
        }
    });

    // Sticky navbar

    let header = document.querySelector('.header');
    header.classList.toggle('sticky', window.scrollY > 100);

    //Remove toggle icon navbar

    menuIcon.classList.remove('fa-xmark');
    navbar.classList.remove('active');
};

// Filtering (MixItUp)
let mixer = mixitup('.project-container', {
  selectors: { target: '.mix' },
  animation: { duration: 300 }
});

// Popup functionality
document.addEventListener("DOMContentLoaded", () => {
  const popup = document.querySelector(".project-popup");
  const popupClose = document.querySelector(".project-popup-close");
  const popupImg = document.querySelector(".project-popup-img");
  const popupCategory = document.querySelector("#popup-category");
  const popupTitle = document.querySelector(".popup-title");
  const popupDesc = document.querySelector(".popup-description");
  const popupInfo = document.querySelector(".popup-info");

  document.querySelectorAll(".project-button").forEach(btn => {
      btn.addEventListener("click", () => {
          let card = btn.closest(".project-card");
          let details = card.querySelector(".portfolio-item-details");

          if (!details) return;

          // Fill popup content
          popupImg.src = card.querySelector(".project-img").src;
          popupCategory.textContent =
              card.classList.contains("web") ? "Web" :
              card.classList.contains("app") ? "App" : "Design";

          popupTitle.textContent = details.querySelector(".details-title").textContent;
          popupDesc.textContent = details.querySelector(".details-description").textContent;
          popupInfo.innerHTML = details.querySelector(".details-info").innerHTML;

          popup.classList.add("open");
      });
  });

  // Close popup
  popupClose.addEventListener("click", () => popup.classList.remove("open"));
});


document.addEventListener("DOMContentLoaded", () => {
  new Typed(".typing-text", {
    strings: ["Web Developer", "App Developer", "Designer"],
    typeSpeed: 70,
    backSpeed: 70,
    backDelay: 1000,
    loop: true,
    showCursor: true,
    cursorChar: "|",
  });
});
