/**
 * Global keyboard shortcuts and the quieter easter eggs.
 *
 *   Ctrl/Cmd + K, /   command palette
 *   ~ or `            terminal
 *   Konami code       a brief scan across the grid
 *   logo × 5          developer diagnostics
 */

import { $, toast } from '@/core/util.js';
import { togglePalette, openPalette, openTerminal, openDiagnostics } from '@/core/palette.js';

const typing = () => {
    const el = document.activeElement;
    return el && (el.matches('input, textarea, select') || el.isContentEditable);
};

export function initKeys() {
    const konami = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
    let progress = 0;

    addEventListener('keydown', (e) => {
        if ((e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            togglePalette();
            return;
        }

        if (typing() || e.metaKey || e.ctrlKey || e.altKey) return;

        if (e.key === '/') {
            e.preventDefault();
            openPalette();
            return;
        }

        if (e.key === '~' || e.key === '`') {
            e.preventDefault();
            openTerminal();
            return;
        }

        // Konami code.
        progress = e.key === konami[progress] || e.key.toLowerCase() === konami[progress] ? progress + 1 : (e.key === konami[0] ? 1 : 0);

        if (progress === konami.length) {
            progress = 0;
            document.documentElement.classList.add('konami');
            toast('↑↑↓↓←→←→BA — grid unlocked');
            setTimeout(() => document.documentElement.classList.remove('konami'), 4000);
        }
    });

    /* Click the logo five times quickly for diagnostics. */
    const logo = $('#logo');
    let clicks = 0;
    let timer;

    logo?.addEventListener('click', () => {
        clicks++;
        clearTimeout(timer);
        timer = setTimeout(() => { clicks = 0; }, 900);

        if (clicks >= 5) {
            clicks = 0;
            openDiagnostics();
        }
    });

    /* A note for anyone who opens the console. */
    const style = 'font: 600 13px/1.6 ui-monospace, monospace; color: #ffb04d;';
    console.log(
        '%c\n  afifa.dev — hello, fellow engineer.\n\n  You opened the console, so you are exactly who I want to talk to.\n'
        + '  Type `hire afifa` into the command palette (Ctrl K), or press ~ for a terminal.\n',
        style,
    );
}
