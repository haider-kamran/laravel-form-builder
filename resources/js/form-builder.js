/**
 * Laravel Form Builder – Frontend JS
 * Handles: Conditional Logic | Repeater Fields
 */
document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------------------------
    // 1. CONDITIONAL LOGIC
    //    Reads [data-logic] on each .field-wrapper and shows/hides accordingly.
    // -------------------------------------------------------------------------
    document.querySelectorAll('.field-wrapper[data-logic]').forEach(function (wrapper) {
        let logic;
        try {
            logic = JSON.parse(wrapper.getAttribute('data-logic'));
        } catch (e) {
            console.error('[FormBuilder] Invalid conditional-logic JSON', e);
            return;
        }

        const watchedName = logic.field;
        const evaluate    = function () {
            const targets = document.querySelectorAll('[name="' + watchedName + '"]');
            let value     = '';

            targets.forEach(function (el) {
                if ((el.type === 'checkbox' || el.type === 'radio') && el.checked) {
                    value = el.value;
                } else if (el.type !== 'checkbox' && el.type !== 'radio') {
                    value = el.value;
                }
            });

            let match = false;
            switch (logic.operator) {
                case '=':  match = value === logic.value; break;
                case '!=': match = value !== logic.value; break;
                case '>':  match = parseFloat(value) > parseFloat(logic.value); break;
                case '<':  match = parseFloat(value) < parseFloat(logic.value); break;
                default:   match = value === logic.value;
            }

            wrapper.style.display = (logic.action === 'show') === match ? 'block' : 'none';
        };

        document.querySelectorAll('[name="' + watchedName + '"]').forEach(function (el) {
            el.addEventListener('change', evaluate);
            el.addEventListener('input',  evaluate);
        });

        evaluate(); // run immediately on load
    });

    // -------------------------------------------------------------------------
    // 2. REPEATER FIELDS
    //    Builds rows dynamically, re-indexes names on add/remove.
    // -------------------------------------------------------------------------
    document.querySelectorAll('.repeater-wrapper').forEach(function (wrapper) {
        const fieldName = wrapper.getAttribute('data-repeater-name');
        let   subFields = [];
        try {
            subFields = JSON.parse(wrapper.getAttribute('data-repeater-fields') || '[]');
        } catch (e) {
            console.error('[FormBuilder] Invalid repeater fields JSON', e);
        }

        const itemsContainer = wrapper.querySelector('.repeater-items');
        const addButton      = wrapper.querySelector('.btn-add-repeater');

        // ---- Re-index all existing and newly added rows -----
        const reIndex = function () {
            itemsContainer.querySelectorAll('.repeater-item').forEach(function (item, idx) {
                // Update row heading
                const heading = item.querySelector('.card-header strong');
                if (heading) heading.textContent = 'Row ' + (idx + 1);

                // Re-show remove button for all rows except the first
                const removeBtn = item.querySelector('.btn-remove-repeater');
                if (removeBtn) {
                    removeBtn.style.display = idx === 0 ? 'none' : '';
                }

                // Re-name all inputs/selects/textareas inside the row
                item.querySelectorAll('[data-field-name]').forEach(function (el) {
                    const subName = fieldName + '[' + idx + '][' + el.getAttribute('data-field-name') + ']';
                    el.setAttribute('name', subName);
                });
            });
        };

        // ---- Build a brand-new blank row ----
        const buildRow = function (values) {
            values = values || {};

            const card = document.createElement('div');
            card.className = 'repeater-item card mb-3';

            // Card header
            const header = document.createElement('div');
            header.className = 'card-header d-flex justify-content-between align-items-center py-2';
            header.innerHTML = '<strong class="small text-muted">Row</strong>';

            const removeBtn = document.createElement('button');
            removeBtn.type      = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger btn-remove-repeater';
            removeBtn.textContent = '× Remove';
            removeBtn.addEventListener('click', function () {
                card.remove();
                reIndex();
            });
            header.appendChild(removeBtn);
            card.appendChild(header);

            // Card body
            const body = document.createElement('div');
            body.className = 'card-body';

            const row = document.createElement('div');
            row.className = 'row g-2';

            subFields.forEach(function (subField) {
                const col   = document.createElement('div');
                col.className = 'col-md-' + (subField.width || 6) + ' mb-1';

                const label = document.createElement('label');
                label.className   = 'form-label small fw-semibold';
                label.textContent = (subField.label || subField.name) + (subField.required ? ' *' : '');
                col.appendChild(label);

                let input;
                const subType = subField.type || 'text';

                if (subType === 'textarea') {
                    input = document.createElement('textarea');
                    input.className = 'form-control form-control-sm repeater-input';
                    input.rows = 2;
                    input.textContent = values[subField.name] || '';
                } else if (subType === 'select') {
                    input = document.createElement('select');
                    input.className = 'form-select form-select-sm repeater-input';
                    const blank = document.createElement('option');
                    blank.value = '';
                    blank.textContent = 'Select…';
                    input.appendChild(blank);
                    Object.entries(subField.choices || {}).forEach(function ([val, lbl]) {
                        const opt = document.createElement('option');
                        opt.value       = val;
                        opt.textContent = lbl;
                        if ((values[subField.name] || '') === val) opt.selected = true;
                        input.appendChild(opt);
                    });
                } else {
                    input = document.createElement('input');
                    input.type      = subType;
                    input.className = 'form-control form-control-sm repeater-input';
                    input.value     = values[subField.name] || '';
                }

                input.setAttribute('data-field-name', subField.name);
                if (subField.required) input.required = true;

                col.appendChild(input);
                row.appendChild(col);
            });

            body.appendChild(row);
            card.appendChild(body);
            itemsContainer.appendChild(card);

            // Attach remove listener for any server-rendered remove buttons
            card.querySelectorAll('.btn-remove-repeater').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    card.remove();
                    reIndex();
                });
            });

            reIndex();
        };

        // Attach remove listeners to server-rendered rows
        itemsContainer.querySelectorAll('.btn-remove-repeater').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btn.closest('.repeater-item').remove();
                reIndex();
            });
        });

        // Add-row button
        addButton.addEventListener('click', function () {
            buildRow({});
        });

        // If no rows were rendered server-side, add the first blank row
        if (!itemsContainer.querySelectorAll('.repeater-item').length) {
            buildRow({});
        } else {
            reIndex(); // Ensure first-row remove button is hidden
        }
    });

});
