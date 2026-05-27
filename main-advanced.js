(function () {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  // Preloader
  window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    if (!preloader) return;
    setTimeout(() => preloader.classList.add('hidden'), reduceMotion ? 0 : 450);
  });

  // Cursor glow follows pointer
  const glow = document.querySelector('.cursor-glow');
  document.addEventListener('pointermove', (event) => {
    document.documentElement.style.setProperty('--pointer-x', `${event.clientX}px`);
    document.documentElement.style.setProperty('--pointer-y', `${event.clientY}px`);
    if (glow) {
      glow.style.setProperty('--x', `${event.clientX}px`);
      glow.style.setProperty('--y', `${event.clientY}px`);
    }
  }, { passive: true });

  // Custom cursor desktop only
  if (isFinePointer && !reduceMotion) {
    const dot = document.querySelector('.custom-cursor');
    const ring = document.querySelector('.cursor-ring');
    if (dot && ring) {
      document.body.classList.add('cursor-ready');
      let x = window.innerWidth / 2, y = window.innerHeight / 2;
      let rx = x, ry = y;
      document.addEventListener('pointermove', (e) => { x = e.clientX; y = e.clientY; }, { passive: true });
      const animate = () => {
        rx += (x - rx) * 0.18;
        ry += (y - ry) * 0.18;
        dot.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
        ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
        requestAnimationFrame(animate);
      };
      animate();
      document.addEventListener('mouseover', (e) => {
        if (e.target.closest('a, button, .project-card, .skills-item, .contact-card, input, textarea, .style-switcher-color, .preset-btn')) {
          document.body.classList.add('cursor-hover');
        }
      });
      document.addEventListener('mouseout', (e) => {
        if (e.target.closest('a, button, .project-card, .skills-item, .contact-card, input, textarea, .style-switcher-color, .preset-btn')) {
          document.body.classList.remove('cursor-hover');
        }
      });
    }
  }

  // Magnetic buttons and links
  if (isFinePointer && !reduceMotion) {
    document.querySelectorAll('.btn, .social-link, .admin-login-btn, .project-item, .tab-btn').forEach((el) => {
      el.classList.add('magnetic-btn');
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        const x = e.clientX - r.left - r.width / 2;
        const y = e.clientY - r.top - r.height / 2;
        el.style.transform = `translate(${x * 0.12}px, ${y * 0.18}px)`;
      });
      el.addEventListener('mouseleave', () => { el.style.transform = ''; });
    });
  }

  // Mouse light for cards
  document.querySelectorAll('.project-card, .skills-item, .contact-card, .timeline-content, .service-card, .counter-card, .stat-card, .detail-item, .experience-card, .current-card').forEach((card) => {
    card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--glow-x', `${e.clientX - r.left}px`);
      card.style.setProperty('--glow-y', `${e.clientY - r.top}px`);
    }, { passive: true });
  });

  // Terminal typing lines inside hero console
  document.addEventListener('DOMContentLoaded', () => {
    const target = document.getElementById('terminal-typing');
    if (!target || reduceMotion) return;
    const lines = ['$ whoami', '$ npm run portfolio', '$ status --available', '$ open ./projects'];
    let i = 0, j = 0, output = '';
    const type = () => {
      if (i >= lines.length) return;
      const line = lines[i];
      if (j < line.length) {
        output += line[j++];
        target.textContent = output;
        setTimeout(type, 35);
      } else {
        output += '\n'; i++; j = 0; setTimeout(type, 420);
      }
    };
    type();
  });

  // Command palette
  const palette = document.getElementById('command-palette');
  const commandInput = document.getElementById('command-input');
  const commandList = document.getElementById('command-list');
  const commands = [
    ['Home', '#home', 'go'], ['About', '#about', 'go'], ['Experience', '#experience', 'go'],
    ['Education', '#education', 'go'], ['Skills', '#skills', 'go'], ['Projects', '#project', 'go'],
    ['Current Work', '#current-work', 'go'], ['Contact', '#contact', 'go'], ['Admin Login', 'admin/login.php', 'open'],
    ['Download Resume', 'download_cv.php', 'open'], ['Copy Email', 'copy-email', 'copy']
  ];
  const existingCommands = commands.filter((cmd) => cmd[1].startsWith('#') ? document.querySelector(cmd[1]) : true);
  let activeIndex = 0;

  function renderCommands(filter = '') {
    if (!commandList) return;
    const q = filter.toLowerCase();
    const filtered = existingCommands.filter(([name]) => name.toLowerCase().includes(q));
    commandList.innerHTML = filtered.map(([name, value, type], idx) => `
      <button type="button" class="command-item ${idx === activeIndex ? 'active' : ''}" data-value="${value}" data-type="${type}">
        ${name}<span>${type === 'go' ? value : type}</span>
      </button>`).join('') || '<div class="command-item">No command found</div>';
  }
  function openPalette() { if (!palette) return; palette.classList.add('open'); palette.setAttribute('aria-hidden','false'); activeIndex = 0; renderCommands(''); setTimeout(() => commandInput?.focus(), 30); }
  function closePalette() { if (!palette) return; palette.classList.remove('open'); palette.setAttribute('aria-hidden','true'); commandInput.value = ''; }
  function runCommand(button) {
    if (!button) return;
    const value = button.dataset.value;
    const type = button.dataset.type;
    if (type === 'go') document.querySelector(value)?.scrollIntoView({ behavior: 'smooth' });
    if (type === 'open') window.location.href = value;
    if (type === 'copy') document.getElementById('copy-email')?.click();
    closePalette();
  }
  document.addEventListener('keydown', (e) => {
    const modK = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k';
    if (modK) { e.preventDefault(); openPalette(); }
    if (e.key === 'Escape' && palette?.classList.contains('open')) closePalette();
    if (!palette?.classList.contains('open')) return;
    const items = [...document.querySelectorAll('.command-item[data-value]')];
    if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, items.length - 1); renderCommands(commandInput.value); }
    if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); renderCommands(commandInput.value); }
    if (e.key === 'Enter') { e.preventDefault(); runCommand(document.querySelector('.command-item.active')); }
  });
  document.getElementById('command-open')?.addEventListener('click', openPalette);
  palette?.addEventListener('click', (e) => { if (e.target === palette) closePalette(); const btn = e.target.closest('.command-item[data-value]'); if (btn) runCommand(btn); });
  commandInput?.addEventListener('input', () => { activeIndex = 0; renderCommands(commandInput.value); });

  // Section minimap sync
  const minimapLinks = [...document.querySelectorAll('.section-minimap a')];
  const navAwareSections = [...document.querySelectorAll('main section[id]')];
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const id = entry.target.id;
        minimapLinks.forEach((a) => a.classList.toggle('active', a.getAttribute('href') === `#${id}`));
      });
    }, { rootMargin: '-45% 0px -45% 0px', threshold: 0.01 });
    navAwareSections.forEach((s) => observer.observe(s));
  }

  // Theme presets
  const presetButtons = document.querySelectorAll('.preset-btn');
  const savedPreset = localStorage.getItem('themePreset') || 'cyber-blue';
  document.documentElement.dataset.preset = savedPreset;
  presetButtons.forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.preset === savedPreset);
    btn.addEventListener('click', () => {
      document.documentElement.dataset.preset = btn.dataset.preset;
      localStorage.setItem('themePreset', btn.dataset.preset);
      presetButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  // Scroll-linked morph: elements grow into focus and shrink when scrolling away/up
  const morphEls = [...document.querySelectorAll('.scroll-morph')];
  const roadmap = document.querySelector('.education-roadmap');
  let morphTicking = false;

  function updateScrollMorph() {
    morphTicking = false;
    if (reduceMotion) {
      morphEls.forEach((el) => {
        el.style.setProperty('--morph-scale', '1');
        el.style.setProperty('--morph-y', '0px');
        el.style.setProperty('--morph-opacity', '1');
        el.style.setProperty('--morph-blur', '0px');
      });
      return;
    }

    const vh = window.innerHeight || 1;
    const focusLine = vh * 0.52;
    morphEls.forEach((el) => {
      if (el.classList.contains('is-filtered-out')) return;
      const rect = el.getBoundingClientRect();
      const center = rect.top + rect.height / 2;
      const distance = Math.abs(center - focusLine);
      const range = Math.max(vh * 0.68, 420);
      const progress = Math.max(0, Math.min(1, 1 - distance / range));
      const eased = 1 - Math.pow(1 - progress, 3);
      const scale = 0.82 + eased * 0.18;
      const y = (1 - eased) * 54 * (center < focusLine ? -0.45 : 1);
      const opacity = 0.28 + eased * 0.72;
      const blur = (1 - eased) * 2.2;
      el.style.setProperty('--morph-progress', eased.toFixed(3));
      el.style.setProperty('--morph-scale', scale.toFixed(3));
      el.style.setProperty('--morph-y', `${y.toFixed(1)}px`);
      el.style.setProperty('--morph-opacity', opacity.toFixed(3));
      el.style.setProperty('--morph-blur', `${blur.toFixed(2)}px`);
      el.classList.toggle('is-in-focus', eased > 0.72);
    });

    if (roadmap) {
      const r = roadmap.getBoundingClientRect();
      const start = vh * 0.78;
      const end = -r.height * 0.1;
      const progress = Math.max(0.08, Math.min(1, (start - r.top) / (start - end)));
      roadmap.style.setProperty('--roadmap-progress', progress.toFixed(3));
    }
  }

  function requestScrollMorph() {
    if (morphTicking) return;
    morphTicking = true;
    requestAnimationFrame(updateScrollMorph);
  }

  if (morphEls.length || roadmap) {
    updateScrollMorph();
    window.addEventListener('scroll', requestScrollMorph, { passive: true });
    window.addEventListener('resize', requestScrollMorph);
  }

  // Current work filters
  document.querySelectorAll('.current-filter').forEach((btn) => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.currentFilter || 'all';
      document.querySelectorAll('.current-filter').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.current-card').forEach((card) => {
        const match = filter === 'all' || card.dataset.currentType === filter;
        card.classList.toggle('is-filtered-out', !match);
      });
      requestScrollMorph();
    });
  });

})();
