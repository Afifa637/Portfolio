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

//Scroll reveal animation
ScrollReveal({ 
    distance: '80px',
    duration: 2000,
    delay: 200
 });

ScrollReveal().reveal('.home-content, .heading', { origin: 'top' });
ScrollReveal().reveal('.home-img, .services-container, .project-box, .skills-container, .contact-form, .education-container', { origin: 'bottom' });
ScrollReveal().reveal('.home-content h1, .about-img, .contact-content', { origin: 'left' });
ScrollReveal().reveal('.home-content p, .about-content, .home-img-container', { origin: 'right' });


// Style Switcher Toggle
const styleSwitcher = document.getElementById('style-switcher'),
    switcherToggle = document.getElementById('switcher-toggle'),
    switcherClose = document.getElementById('switcher-close');

switcherToggle.addEventListener('click', () => {
    styleSwitcher.classList.add('show-switcher');
});

switcherClose.addEventListener('click', () => {
    styleSwitcher.classList.remove('show-switcher');
});

// Change color theme
const colors = document.querySelectorAll('.style-switcher-color');
colors.forEach(color => {
    color.addEventListener('click', () => {
        const activeColor = color.style.getPropertyValue('--hue');

        // Remove active class from previous selection
        colors.forEach(c => c.classList.remove('active-color'));
        color.classList.add('active-color');

        // Apply new hue to root
        document.documentElement.style.setProperty('--hue', activeColor);
    });
});

// Theme Switcher (Light / Dark)
const themeInputs = document.querySelectorAll('input[name="body-theme"]');

themeInputs.forEach(input => {
    input.addEventListener('change', () => {
        document.documentElement.setAttribute('data-theme', input.value);
    });
});

// Ensure MixItUp is properly initialized
let mixerProject = mixitup('.project-container', {
    selectors: {    
        target : '.project-card'
    },
    animation: {
        duration: 300
    }
});

//link active work
const linkWork = document.querySelectorAll('.project-item');

function activeWork() {
        linkWork.forEach(l => l.classList.remove('active-work'));
        this.classList.add('active-work');
}

linkWork.forEach(l => l.addEventListener('click', activeWork));

//project popup
document.addEventListener('click', (e) => {
    if(e.target.classList.contains('project-button')) {
        toggleProjectPopup();
        projectItemDetails(e.target.parentElement);
    }
})

function toggleProjectPopup() {
    document.querySelector('.project-popup').classList.toggle('open'); 
}

document.querySelector('.project-popup-close').addEventListener('click', toggleProjectPopup);

function projectItemDetails(projectItem) {
    document.querySelector('.pp-thumbnail img').src = projectItem.querySelector('.project-img').src;
    document.querySelector('.project-popup-subtitles span').innerHTML = projectItem.querySelector('.project-title').innerHTML;
    document.querySelector('.project-popup-body').innerHTML = projectItem.querySelector('.project-item-details').innerHTML;
}   