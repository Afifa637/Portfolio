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

document.addEventListener("DOMContentLoaded", () => {
  const popup = document.querySelector(".project-popup");
  const popupClose = document.querySelector(".project-popup-close");
  const popupImg = document.querySelector(".project-popup-img");
  const popupCategory = document.querySelector("#popup-category");
  const popupTitle = document.querySelector(".popup-title");
  const popupDesc = document.querySelector(".popup-description");
  const popupInfo = document.querySelector(".popup-info");
  const popupLink = document.querySelector(".view-link"); // get the button

  // Format description into structured HTML
  function formatDescription(desc) {
    const lines = desc.split("\n");
    let html = "";
    let inList = false;

    lines.forEach(line => {
      line = line.trim();
      if (!line) {
        if (inList) { html += "</ul>"; inList = false; }
        return;
      }
      if (/[:：]$/.test(line) && !line.startsWith('-')) {
        if (inList) { html += "</ul>"; inList = false; }
        html += `<h4>${line}</h4>`;
      }
      else if (/^[-–]\s+/.test(line)) {
        if (!inList) { html += "<ul>"; inList = true; }
        html += `<li>${line.replace(/^[-–]\s+/, "")}</li>`;
      } else {
        if (inList) { html += "</ul>"; inList = false; }
        html += `<p>${line}</p>`;
      }
    });

    if (inList) html += "</ul>";
    return html;
  }

  document.querySelectorAll(".project-button").forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".project-card");
      const details = card.querySelector(".portfolio-item-details");
      if (!details) return;

      // Fill popup content
      popupImg.src = card.querySelector(".project-img").src;
      popupCategory.textContent =
        card.classList.contains("web") ? "Web" :
        card.classList.contains("app") ? "App" : "Terminal";

      popupTitle.textContent = details.querySelector(".details-title").textContent;
      popupDesc.innerHTML = formatDescription(details.querySelector(".details-description").textContent);
      popupInfo.innerHTML = details.querySelector(".details-info").innerHTML;

      // Set the "View Project" link from database
      const viewLink = details.querySelector(".details-info li a")?.href || "#";
      popupLink.href = viewLink;

      popup.classList.add("open");
      popup.setAttribute("aria-hidden", "false");
    });
  });

  // Close popup
  popupClose.addEventListener("click", () => {
    popup.classList.remove("open");
    popup.setAttribute("aria-hidden", "true");
  });
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



const styleSwitcher = document.getElementById('style-switcher');
const switcherToggle = document.getElementById('switcher-toggle');
const switcherClose = document.getElementById('switcher-close');
const gear = document.querySelector('.nav-settings');

switcherToggle.addEventListener('click', () => {
  styleSwitcher.classList.add('show-switcher');
  gear.classList.add('active'); // rotate gear
});

switcherClose.addEventListener('click', () => {
  styleSwitcher.classList.remove('show-switcher');
  gear.classList.remove('active'); // reset gear
});

const colors = document.querySelectorAll('.style-switcher-color');

colors.forEach(color => {
  color.addEventListener('click', () => {
    const activeColor = color.style.getPropertyValue('--hue');
    colors.forEach(c => c.classList.remove('active-color'));
    color.classList.add('active-color');
    document.documentElement.style.setProperty('--hue', activeColor);

    // Save selected color
    localStorage.setItem('preferredColor', activeColor);
  });
});

// Restore saved color on load
const savedColor = localStorage.getItem('preferredColor');
if (savedColor) {
  document.documentElement.style.setProperty('--hue', savedColor);
  colors.forEach(c => {
    c.classList.toggle(
      'active-color',
      c.style.getPropertyValue('--hue') === savedColor
    );
  });
}

const themeInputs = document.querySelectorAll('.style-switcher-input');
const userPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

themeInputs.forEach(input => {
  input.addEventListener('change', () => {
    document.documentElement.classList.remove('dark', 'light');
    document.documentElement.classList.add(input.value);

    localStorage.setItem('preferredTheme', input.value);
  });
});

const savedTheme = localStorage.getItem('preferredTheme');
if (savedTheme) {
  document.documentElement.classList.add(savedTheme);
  document.getElementById(`${savedTheme}-theme`).checked = true;
} else if (userPrefersDark) {
  document.documentElement.classList.add('dark');
  document.getElementById('dark-theme').checked = true;
} else {
  document.documentElement.classList.add('light');
  document.getElementById('light-theme').checked = true;
}