/**
 * Admin interactions: drawer, destructive-action confirmation, drag reordering,
 * image previews, and repeatable field rows.
 *
 * Dependency-free, and every feature degrades to a working form without it:
 * reordering falls back to the saved order, deletes still submit, uploads still
 * work. Nothing here is required to manage the site.
 */

(() => {
    'use strict';

    const $  = (s, c = document) => c.querySelector(s);
    const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

    /* ------------------------------------------------------------ drawer -- */

    const sidebar = $('#admin-sidebar');
    const overlay = $('#admin-overlay');
    const toggle  = $('#admin-menu-toggle');

    const setDrawer = (open) => {
        sidebar?.classList.toggle('is-open', open);
        if (overlay) overlay.hidden = !open;
        document.body.classList.toggle('is-locked', open);
    };

    toggle?.addEventListener('click', () => setDrawer(!sidebar?.classList.contains('is-open')));
    overlay?.addEventListener('click', () => setDrawer(false));

    addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setDrawer(false);
    });

    /* ------------------------------------------------------ confirmation -- */

    // Deleting is irreversible, so it always asks — including when the button
    // submits a separate form via the `form` attribute.
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        if (!confirm(trigger.dataset.confirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    /* -------------------------------------------------- image preview ----- */

    $$('[data-image-upload]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            const wrap = input.closest('.image-field');
            const preview = wrap?.querySelector('.image-preview');
            if (!preview) return;

            const url = URL.createObjectURL(file);
            preview.innerHTML = `<img src="${url}" alt="">`;
            preview.removeAttribute('data-empty');
            preview.querySelector('img').onload = () => URL.revokeObjectURL(url);
        });
    });

    // Typing a path straight into the text field updates the preview too.
    $$('[data-image-path]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = input.closest('.image-field')?.querySelector('.image-preview');
            if (!preview) return;

            if (input.value.trim() === '') {
                preview.innerHTML = '<span>No image</span>';
                preview.dataset.empty = 'true';
            } else {
                preview.innerHTML = `<img src="../${input.value.trim()}" alt="">`;
                preview.removeAttribute('data-empty');
            }
        });
    });

    /* ------------------------------------------------- drag to reorder ----- */

    const table = $('[data-sortable] tbody');

    if (table) {
        const saveButton = $('#save-order');
        let dragged = null;

        const syncOrder = () => {
            $$('tr', table).forEach((row) => {
                const input = $('input[name="order[]"]', row);
                if (input) input.value = row.dataset.id;
            });

            if (saveButton) saveButton.hidden = false;
        };

        $$('tr', table).forEach((row) => {
            row.addEventListener('dragstart', (e) => {
                dragged = row;
                row.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                // Firefox needs data set for the drag to begin at all.
                e.dataTransfer.setData('text/plain', row.dataset.id);
            });

            row.addEventListener('dragend', () => {
                row.classList.remove('is-dragging');
                $$('tr', table).forEach((r) => r.classList.remove('is-over'));
                dragged = null;
            });

            row.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragged || dragged === row) return;

                row.classList.add('is-over');
                e.dataTransfer.dropEffect = 'move';
            });

            row.addEventListener('dragleave', () => row.classList.remove('is-over'));

            row.addEventListener('drop', (e) => {
                e.preventDefault();
                row.classList.remove('is-over');
                if (!dragged || dragged === row) return;

                const rows = $$('tr', table);
                const from = rows.indexOf(dragged);
                const to   = rows.indexOf(row);

                row.parentNode.insertBefore(dragged, from < to ? row.nextSibling : row);
                syncOrder();
            });
        });
    }

    /* -------------------------------------------------- repeatable rows ---- */

    // Used by the project editor for the "What it does" bullets.
    $$('[data-repeater]').forEach((repeater) => {
        const list = $('[data-repeater-list]', repeater);
        const add  = $('[data-repeater-add]', repeater);
        const name = repeater.dataset.repeater;

        const makeRow = (value = '') => {
            const row = document.createElement('div');
            row.className = 'repeater-row';
            row.innerHTML =
                `<input type="text" name="${name}[]" value="${value.replace(/"/g, '&quot;')}" placeholder="One feature per row">` +
                `<button type="button" class="btn btn-sm btn-danger" data-repeater-remove aria-label="Remove">&times;</button>`;
            return row;
        };

        add?.addEventListener('click', () => {
            const row = makeRow();
            list.appendChild(row);
            $('input', row).focus();
        });

        list?.addEventListener('click', (e) => {
            if (e.target.closest('[data-repeater-remove]')) {
                e.target.closest('.repeater-row').remove();
            }
        });

        // Enter adds the next row rather than submitting the whole form.
        list?.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' || e.target.tagName !== 'INPUT') return;

            e.preventDefault();
            const row = makeRow();
            e.target.closest('.repeater-row').after(row);
            $('input', row).focus();
        });
    });

    /* ------------------------------------------------- mobile table labels -- */

    // Pairs each cell with its column heading so the stacked phone layout
    // stays readable.
    $$('.admin-table').forEach((t) => {
        const headings = $$('thead th', t).map((th) => th.textContent.trim());

        $$('tbody tr', t).forEach((row) => {
            $$('td', row).forEach((cell, i) => {
                if (headings[i]) cell.dataset.label = headings[i];
            });
        });
    });

    /* -------------------------------------------------- unsaved changes ---- */

    const form = $('.admin-form');

    if (form) {
        let dirty = false;

        form.addEventListener('input', () => { dirty = true; });
        form.addEventListener('submit', () => { dirty = false; });

        addEventListener('beforeunload', (e) => {
            if (!dirty) return;
            e.preventDefault();
            e.returnValue = '';
        });
    }
})();
