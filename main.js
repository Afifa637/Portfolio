const navMenu = document.getElementById("nav-menu");
const navToggle = document.getElementById("nav-toggle");
const navLinks = document.querySelectorAll(".nav-link");
const header = document.getElementById("header");
const progressBar = document.getElementById("scroll-progress-bar");
const backToTop = document.getElementById("back-to-top");

if (navToggle) {
  navToggle.addEventListener("click", () => {
    const isOpen = navMenu.classList.toggle("active");
    navToggle.classList.toggle("active", isOpen);
    navToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });
}

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    navMenu.classList.remove("active");
    navToggle?.classList.remove("active");
    navToggle?.setAttribute("aria-expanded", "false");
  });
});

function toggleReadMore() {
  const moreText = document.getElementById("moreText");
  const btn = document.getElementById("readMoreBtn");

  if (!moreText || !btn) return;

  const isVisible = moreText.style.display === "block";
  moreText.style.display = isVisible ? "none" : "block";
  btn.textContent = isVisible ? "Read More" : "Read Less";
}

function acceptTerms() {
  if (document.getElementById("terms")?.checked) {
    document.cookie =
      "terms_accepted=true; path=/; max-age=" + 60 * 60 * 24 * 365;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const moreText = document.getElementById("moreText");
  if (moreText) {
    moreText.style.display = "none";
  }
});

// Scroll section active link + progress
const sections = document.querySelectorAll("section");
let lastScrollY = window.scrollY;

window.addEventListener("scroll", () => {
  const top = window.scrollY;

  sections.forEach((section) => {
    const offset = section.offsetTop - 160;
    const height = section.offsetHeight;
    const id = section.getAttribute("id");

    if (top >= offset && top < offset + height) {
      navLinks.forEach((link) => link.classList.remove("active"));
      const activeLink = document.querySelector(`.header nav a[href*="${id}"]`);
      activeLink?.classList.add("active");
    }
  });

  header?.classList.toggle("sticky", top > 80);

  if (backToTop) {
    backToTop.classList.toggle("show", top > 600);
  }

  if (header) {
    header.classList.toggle("hide", top > lastScrollY && top > 200);
  }
  lastScrollY = top;

  if (progressBar) {
    const scrollHeight =
      document.documentElement.scrollHeight -
      document.documentElement.clientHeight;
    const progress = scrollHeight > 0 ? (top / scrollHeight) * 100 : 0;
    progressBar.style.width = `${progress}%`;
  }
});

backToTop?.addEventListener("click", () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
});

// Project filtering (MixItUp)
if (window.mixitup) {
  mixitup(".project-container", {
    selectors: { target: ".mix" },
    animation: { duration: 300 },
  });
}

// Project search
const projectSearch = document.getElementById("project-search");
if (projectSearch) {
  projectSearch.addEventListener("input", (e) => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll(".project-card").forEach((card) => {
      const haystack =
        `${card.dataset.title} ${card.dataset.tech} ${card.dataset.role}`.toLowerCase();
      card.classList.toggle("hidden", !haystack.includes(query));
    });
  });
}

// Project modal
// Rich case-study style details panel

document.addEventListener("DOMContentLoaded", () => {
  const popup = document.querySelector(".project-popup");
  const popupClose = document.querySelector(".project-popup-close");
  const popupImg = document.querySelector(".project-popup-img");
  const popupCategory = document.querySelector("#popup-category");
  const popupTitle = document.querySelector(".popup-title");
  const popupDesc = document.querySelector(".popup-description");
  const popupInfo = document.querySelector(".popup-info");
  const popupLink = document.querySelector(".view-link");
  const popupTags = document.getElementById("popup-tags");
  let lastFocusedElement = null;

  if (!popup) return;

  function escapeHtml(value) {
    const div = document.createElement("div");
    div.textContent = value || "";
    return div.innerHTML;
  }

  function formatDescription(desc) {
    const lines = (desc || "").split("\n");
    let html = "";
    let inList = false;

    lines.forEach((rawLine) => {
      const line = rawLine.trim();
      if (!line) {
        if (inList) {
          html += "</ul>";
          inList = false;
        }
        return;
      }
      if (/[:：]$/.test(line) && !line.startsWith("-")) {
        if (inList) {
          html += "</ul>";
          inList = false;
        }
        html += `<h4>${escapeHtml(line)}</h4>`;
      } else if (/^[-–]\s+/.test(line)) {
        if (!inList) {
          html += "<ul>";
          inList = true;
        }
        html += `<li>${escapeHtml(line.replace(/^[-–]\s+/, ""))}</li>`;
      } else {
        if (inList) {
          html += "</ul>";
          inList = false;
        }
        html += `<p>${escapeHtml(line)}</p>`;
      }
    });

    if (inList) html += "</ul>";
    return html || "<p>No detailed description has been added yet.</p>";
  }

  function getFocusable() {
    return [...popup.querySelectorAll('a[href], button:not([disabled]), input, textarea, select, [tabindex]:not([tabindex="-1"])')]
      .filter((el) => el.offsetParent !== null);
  }

  function closePopup() {
    popup.classList.remove("open");
    popup.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
    lastFocusedElement?.focus?.();
  }

  function openPopup(card, trigger) {
    const details = card?.querySelector(".portfolio-item-details");
    if (!details) return;
    lastFocusedElement = trigger || document.activeElement;

    const img = card.querySelector(".project-img");
    const category = details.querySelector(".details-category")?.textContent || card.dataset.category || "Project";
    const title = details.querySelector(".details-title")?.textContent || card.dataset.title || "Project";
    const description = details.querySelector(".details-description")?.textContent || "";
    const tech = (card.dataset.tech || "")
      .split(",")
      .map((t) => t.trim())
      .filter(Boolean);

    if (popupImg) {
      popupImg.src = img?.src || details.querySelector(".details-image")?.textContent || "";
      popupImg.alt = `${title} preview`;
    }
    if (popupCategory) popupCategory.textContent = category;
    if (popupTitle) popupTitle.textContent = title;
    if (popupDesc) popupDesc.innerHTML = formatDescription(description);
    if (popupInfo) popupInfo.innerHTML = details.querySelector(".details-info")?.innerHTML || "";

    if (popupTags) {
      popupTags.innerHTML = tech.map((tag) => `<span>${escapeHtml(tag)}</span>`).join("");
      popupTags.style.display = tech.length ? "flex" : "none";
    }

    const viewLink = details.querySelector(".details-info li a")?.href || card.dataset.link || "";
    if (popupLink) {
      if (viewLink && viewLink !== window.location.href + "#") {
        popupLink.href = viewLink;
        popupLink.style.display = "inline-flex";
      } else {
        popupLink.href = "#";
        popupLink.style.display = "none";
      }
    }

    popup.classList.add("open");
    popup.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    setTimeout(() => popupClose?.focus(), 30);
  }

  document.querySelectorAll(".project-button").forEach((btn) => {
    btn.addEventListener("click", () => openPopup(btn.closest(".project-card"), btn));
  });

  popupClose?.addEventListener("click", closePopup);

  popup.addEventListener("click", (e) => {
    if (e.target === popup) closePopup();
  });

  document.addEventListener("keydown", (e) => {
    if (!popup.classList.contains("open")) return;
    if (e.key === "Escape") closePopup();
    if (e.key === "Tab") {
      const focusable = getFocusable();
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });
});

// Typed text
document.addEventListener("DOMContentLoaded", () => {
  const roles = window.homeRoles || [
    "Web Developer",
    "App Developer",
    "Designer",
  ];
  if (window.Typed && document.querySelector(".typing-text")) {
    new Typed(".typing-text", {
      strings: roles,
      typeSpeed: 70,
      backSpeed: 60,
      backDelay: 1200,
      loop: true,
      showCursor: true,
      cursorChar: "|",
    });
  } else {
    const typing = document.querySelector(".typing-text");
    if (typing) typing.textContent = roles[0];
  }
});

// Style switcher + theme
const styleSwitcher = document.getElementById("style-switcher");
const switcherToggle = document.getElementById("switcher-toggle");
const switcherClose = document.getElementById("switcher-close");
const gear = document.querySelector(".nav-settings");

switcherToggle?.addEventListener("click", () => {
  styleSwitcher?.classList.add("show-switcher");
  gear?.classList.add("active");
});

switcherClose?.addEventListener("click", () => {
  styleSwitcher?.classList.remove("show-switcher");
  gear?.classList.remove("active");
});

const colors = document.querySelectorAll(".style-switcher-color");

colors.forEach((color) => {
  color.addEventListener("click", () => {
    const activeColor = color.style.getPropertyValue("--hue");
    colors.forEach((c) => c.classList.remove("active-color"));
    color.classList.add("active-color");
    document.documentElement.style.setProperty("--hue", activeColor);
    localStorage.setItem("preferredColor", activeColor);
  });
});

const savedColor = localStorage.getItem("preferredColor");
if (savedColor) {
  document.documentElement.style.setProperty("--hue", savedColor);
  colors.forEach((c) => {
    c.classList.toggle(
      "active-color",
      c.style.getPropertyValue("--hue") === savedColor,
    );
  });
}

document.documentElement.classList.remove("light");
document.documentElement.classList.add("dark");
document.body.classList.remove("light");
document.body.classList.add("dark");
localStorage.setItem("preferredTheme", "dark");
document.cookie = `theme=dark; path=/; max-age=${60 * 60 * 24 * 365}`;

// Scroll reveal
document.addEventListener("DOMContentLoaded", () => {
  if (!window.ScrollReveal) return;
  const sr = ScrollReveal({
    origin: "bottom",
    distance: "50px",
    duration: 900,
    delay: 120,
    reset: false,
  });

  sr.reveal(".section-title", { origin: "top" });
  sr.reveal(".home-content", { delay: 200 });
  sr.reveal(".home-banner", { delay: 350, origin: "right" });
  sr.reveal(".about-grid", { delay: 200 });
  sr.reveal(".services-grid", { delay: 350, interval: 150 });
  sr.reveal(".skills-item", { interval: 150 });
  sr.reveal(".project-card", { interval: 150 });
  sr.reveal(".contact-card", { interval: 150 });
  sr.reveal(".contact-form", { origin: "right", delay: 200 });
});

// Futuristic tilt hover effect
document.addEventListener("DOMContentLoaded", () => {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  const tiltCards = document.querySelectorAll(
    ".project-card, .service-card, .skills-item, .contact-card, .counter-card, .detail-item, .stat-card, .timeline-item",
  );

  tiltCards.forEach((card) => {
    card.classList.add("tilt-card");

    card.addEventListener("mousemove", (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      const rx = (-y * 6).toFixed(2);
      const ry = (x * 8).toFixed(2);
      card.style.setProperty("--rx", `${rx}deg`);
      card.style.setProperty("--ry", `${ry}deg`);
      card.style.setProperty("--ty", "-8px");
      card.style.setProperty("--glow-x", `${(x + 0.5) * 100}%`);
      card.style.setProperty("--glow-y", `${(y + 0.5) * 100}%`);
    });

    card.addEventListener("mouseleave", () => {
      card.style.setProperty("--rx", "0deg");
      card.style.setProperty("--ry", "0deg");
      card.style.setProperty("--ty", "0px");
    });
  });
});

// Skills filter
document.querySelectorAll(".tab-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const filter = btn.dataset.filter;
    document
      .querySelectorAll(".tab-btn")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    document.querySelectorAll(".skills-item").forEach((item) => {
      const match = filter === "all" || item.dataset.category === filter;
      item.classList.toggle("hidden", !match);
    });
  });
});

// Contact form loading + copy email
const contactForm = document.getElementById("contact-form");
const contactSubmit = document.getElementById("contact-submit");
contactForm?.addEventListener("submit", () => {
  contactSubmit?.classList.add("loading");
});

const copyEmailBtn = document.getElementById("copy-email");
copyEmailBtn?.addEventListener("click", async () => {
  const email = copyEmailBtn.dataset.email;
  if (!email) return;
  try {
    await navigator.clipboard.writeText(email);
    copyEmailBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
    setTimeout(() => {
      copyEmailBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy Email';
    }, 2000);
  } catch (e) {
    window.location.href = `mailto:${email}`;
  }
});

// Particle background
const canvas = document.getElementById("hero-particles");
if (canvas && canvas.getContext) {
  const ctx = canvas.getContext("2d");
  let particles = [];

  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }

  function createParticles() {
    particles = Array.from({ length: 60 }, () => ({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      r: Math.random() * 2 + 0.5,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.4,
    }));
  }

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "rgba(255,255,255,0.35)";
    particles.forEach((p) => {
      p.x += p.vx;
      p.y += p.vy;
      if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
      if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
    });
    requestAnimationFrame(draw);
  }

  resize();
  createParticles();
  draw();
  window.addEventListener("resize", () => {
    resize();
    createParticles();
  });
}
