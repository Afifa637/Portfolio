const navMenu = document.getElementById('nav-menu');
const navToggle = document.getElementById('nav-toggle');

navToggle.addEventListener('click', () => {
    navMenu.classList.toggle('show-menu');
    navToggle.classList.toggle('animate-toggle');
});


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