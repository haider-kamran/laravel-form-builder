/**
 * Laravel Form Builder — Admin JS
 * Drag-and-drop form builder powered by SortableJS
 */
(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────────────────
    let fields       = [];          // Array of field objects
    let selectedId   = null;        // Currently selected field uid
    let uidCounter   = 0;

    const CHOICE_TYPES    = ['select', 'radio', 'checkbox'];
    const RANGE_TYPES     = ['range'];
    const RATING_TYPES    = ['rating'];
    const FILE_TYPES      = ['file', 'image'];
    const NO_OPTIONS_TYPES = ['text','email','number','textarea','password',
                              'hidden','url','tel','date','datetime','time',
                              'color','toggle','repeater'];

    const FIELD_ICONS = {
        text:'bi-input-cursor-text', email:'bi-envelope', number:'bi-hash',
        textarea:'bi-text-paragraph', password:'bi-shield-lock', hidden:'bi-eye-slash',
        url:'bi-link-45deg', tel:'bi-telephone', select:'bi-chevron-down',
        radio:'bi-record-circle', checkbox:'bi-check2-square', toggle:'bi-toggle-on',
        date:'bi-calendar', datetime:'bi-calendar-event', time:'bi-clock',
        file:'bi-paperclip', image:'bi-image', range:'bi-sliders',
        color:'bi-palette', rating:'bi-star', repeater:'bi-table',
    };

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────
    const uid  = () => 'f' + (++uidCounter) + '_' + Date.now();
    const icon = (type) => FIELD_ICONS[type] || 'bi-input-cursor';
    const slugify = (str) => str.toLowerCase().trim()
                                .replace(/[^a-z0-9]+/g, '_')
                                .replace(/^_|_$/g, '');

    const el  = (id)    => document.getElementById(id);
    const qs  = (sel)   => document.querySelector(sel);
    const qsa = (sel)   => document.querySelectorAll(sel);

    // ─────────────────────────────────────────────────────────────────
    // DOM refs
    // ─────────────────────────────────────────────────────────────────
    const canvas       = el('field-canvas');
    const canvasEmpty  = el('canvas-empty');
    const fieldProps   = el('field-props');
    const formSettings = el('form-settings');
    const propsTitle   = el('props-panel-title');
    const mainForm     = el('main-form');

    // Meta inputs
    const metaTitle   = el('meta-title');
    const metaSlug    = el('meta-slug');
    const metaDesc    = el('meta-description');
    const metaActive  = el('meta-active');

    // Field property inputs
    const propLabel    = el('prop-label');
    const propName     = el('prop-name');
    const propPH       = el('prop-placeholder');
    const propHelp     = el('prop-help');
    const propRequired = el('prop-required');
    const propMin      = el('prop-min');
    const propMax      = el('prop-max');
    const propRegex    = el('prop-regex');
    const propCustom   = el('prop-custom-rule');
    const propChoices  = el('prop-choices');
    const propRangeMin = el('prop-range-min');
    const propRangeMax = el('prop-range-max');
    const propRangeStep= el('prop-range-step');
    const propRatingMax= el('prop-rating-max');
    const propMultiple = el('prop-multiple');
    const propAccept   = el('prop-accept');
    const propLogicField = el('prop-logic-field');
    const propLogicOp    = el('prop-logic-operator');
    const propLogicVal   = el('prop-logic-value');
    const propLogicAct   = el('prop-logic-action');

    // Settings
    const sEnableNotif   = el('s-enable-notif');
    const sRecipientsInput = el('s-recipients-input');
    const sRecipientsHidden = el('s-recipients-hidden');
    const recipientChips = el('recipient-chips');
    const sEnableWebhook = el('s-enable-webhook');
    const sWebhookUrl    = el('s-webhook-url');

    // ─────────────────────────────────────────────────────────────────
    // Auto-slug from title
    // ─────────────────────────────────────────────────────────────────
    metaTitle.addEventListener('input', function () {
        if (!metaSlug.value || metaSlug.dataset.auto !== 'false') {
            metaSlug.value = slugify(metaTitle.value);
        }
    });
    metaSlug.addEventListener('input', function () {
        metaSlug.dataset.auto = 'false';
    });

    // ─────────────────────────────────────────────────────────────────
    // Load existing fields (edit mode)
    // ─────────────────────────────────────────────────────────────────
    if (typeof EXISTING_FIELDS !== 'undefined' && EXISTING_FIELDS.length) {
        EXISTING_FIELDS.forEach(function (f) {
            const fieldObj = {
                uid: uid(),
                id: f.id || null,
                label: f.label,
                name: f.name,
                type: f.type,
                required: !!f.required,
                placeholder: f.options?.placeholder || '',
                help: f.options?.help || '',
                min: f.validation_rules?.min || '',
                max: f.validation_rules?.max || '',
                regex: f.validation_rules?.regex || '',
                custom_rule: f.validation_rules?.custom || '',
                choices: choicesObjToText(f.options?.choices || {}),
                range_min: f.options?.min || 0,
                range_max: f.options?.max || 100,
                range_step: f.options?.step || 1,
                rating_max: f.options?.max || 5,
                multiple: !!f.options?.multiple,
                accept: f.options?.accept || '',
                logic: f.options?.logic || null,
            };
            fields.push(fieldObj);
            renderFieldCard(fieldObj);
        });
    }
    syncEmpty();

    // ─────────────────────────────────────────────────────────────────
    // Sortable — canvas reorder
    // ─────────────────────────────────────────────────────────────────
    const sortable = Sortable.create(canvas, {
        animation: 180,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        filter: '.canvas-empty',
        onEnd: syncFieldOrder,
    });

    // ─────────────────────────────────────────────────────────────────
    // Drag from palette → canvas
    // ─────────────────────────────────────────────────────────────────
    let draggingType = null;
    let draggingLabel = null;

    qsa('#field-palette .palette-item').forEach(function (item) {
        item.addEventListener('dragstart', function (e) {
            draggingType  = item.dataset.type;
            draggingLabel = item.dataset.label;
            e.dataTransfer.effectAllowed = 'copy';
        });
        item.addEventListener('dragend', function () {
            draggingType = null;
        });

        // Double-click to add at bottom
        item.addEventListener('dblclick', function () {
            addField(item.dataset.type, item.dataset.label);
        });
    });

    canvas.addEventListener('dragover', function (e) {
        if (draggingType) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            canvas.classList.add('drag-over');
        }
    });
    canvas.addEventListener('dragleave', function () {
        canvas.classList.remove('drag-over');
    });
    canvas.addEventListener('drop', function (e) {
        canvas.classList.remove('drag-over');
        if (draggingType) {
            e.preventDefault();
            addField(draggingType, draggingLabel);
        }
    });

    // ─────────────────────────────────────────────────────────────────
    // Add Field
    // ─────────────────────────────────────────────────────────────────
    function addField(type, label) {
        const usedNames = fields.map(f => f.name);
        let baseName    = slugify(label);
        let name        = baseName;
        let counter     = 2;
        while (usedNames.includes(name)) { name = baseName + '_' + counter++; }

        const fieldObj = {
            uid: uid(), id: null,
            label: label, name: name, type: type,
            required: false,
            placeholder: '', help: '',
            min: '', max: '', regex: '', custom_rule: '',
            choices: '', range_min: 0, range_max: 100, range_step: 1,
            rating_max: 5, multiple: false, accept: '', logic: null,
        };
        fields.push(fieldObj);
        renderFieldCard(fieldObj);
        syncEmpty();
        selectField(fieldObj.uid);
    }

    // ─────────────────────────────────────────────────────────────────
    // Render a field card on canvas
    // ─────────────────────────────────────────────────────────────────
    function renderFieldCard(fieldObj) {
        canvasEmpty.style.display = 'none';

        const card = document.createElement('div');
        card.className = 'field-card';
        card.dataset.uid = fieldObj.uid;

        card.innerHTML = `
            <div class="field-card-header">
                <span class="drag-handle"><i class="bi bi-grip-vertical"></i></span>
                <i class="bi ${icon(fieldObj.type)} field-icon"></i>
                <div class="field-info">
                    <div class="field-label-display">${escHtml(fieldObj.label)}</div>
                    <div class="field-type-display">${fieldObj.type}</div>
                </div>
                <div class="field-card-actions">
                    ${fieldObj.required ? '<span class="req-badge">Required</span>' : ''}
                    <button type="button" class="btn-remove-field" title="Remove field"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;

        card.addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-field')) return;
            selectField(fieldObj.uid);
        });

        card.querySelector('.btn-remove-field').addEventListener('click', function (e) {
            e.stopPropagation();
            removeField(fieldObj.uid);
        });

        canvas.appendChild(card);
    }

    // ─────────────────────────────────────────────────────────────────
    // Remove field
    // ─────────────────────────────────────────────────────────────────
    function removeField(uid) {
        fields = fields.filter(f => f.uid !== uid);
        const card = canvas.querySelector(`[data-uid="${uid}"]`);
        if (card) card.remove();
        if (selectedId === uid) {
            selectedId = null;
            showFormSettings();
        }
        syncEmpty();
    }

    // ─────────────────────────────────────────────────────────────────
    // Select field → populate props panel
    // ─────────────────────────────────────────────────────────────────
    function selectField(uid) {
        selectedId = uid;
        const fieldObj = fields.find(f => f.uid === uid);
        if (!fieldObj) return;

        // Highlight card
        qsa('.field-card').forEach(c => c.classList.remove('selected'));
        const card = canvas.querySelector(`[data-uid="${uid}"]`);
        if (card) card.classList.add('selected');

        propsTitle.textContent = 'Field: ' + fieldObj.label;
        fieldProps.style.display    = '';
        formSettings.style.display  = 'none';

        // Populate basic
        propLabel.value    = fieldObj.label;
        propName.value     = fieldObj.name;
        propPH.value       = fieldObj.placeholder;
        propHelp.value     = fieldObj.help;
        propRequired.checked = fieldObj.required;

        // Populate validation
        propMin.value    = fieldObj.min;
        propMax.value    = fieldObj.max;
        propRegex.value  = fieldObj.regex;
        propCustom.value = fieldObj.custom_rule;

        // Options tab visibility
        const isChoice   = CHOICE_TYPES.includes(fieldObj.type);
        const isRange    = RANGE_TYPES.includes(fieldObj.type);
        const isRating   = RATING_TYPES.includes(fieldObj.type);
        const isFile     = FILE_TYPES.includes(fieldObj.type);

        el('choices-editor').style.display  = isChoice ? '' : 'none';
        el('range-options').style.display   = isRange  ? '' : 'none';
        el('rating-options').style.display  = isRating ? '' : 'none';
        el('file-options').style.display    = isFile   ? '' : 'none';

        propChoices.value   = fieldObj.choices;
        propRangeMin.value  = fieldObj.range_min;
        propRangeMax.value  = fieldObj.range_max;
        propRangeStep.value = fieldObj.range_step;
        propRatingMax.value = fieldObj.rating_max;
        propMultiple.checked = fieldObj.multiple;
        propAccept.value    = fieldObj.accept;

        // Logic
        const logic = fieldObj.logic || {};
        propLogicField.value = logic.field   || '';
        propLogicOp.value    = logic.operator || '=';
        propLogicVal.value   = logic.value   || '';
        propLogicAct.value   = logic.action  || 'show';

        // Switch to Basic tab
        activateTab('basic');
    }

    // Show form settings (nothing selected)
    function showFormSettings() {
        propsTitle.textContent     = 'Form Settings';
        fieldProps.style.display   = 'none';
        formSettings.style.display = '';
        qsa('.field-card').forEach(c => c.classList.remove('selected'));
    }

    // Click on canvas background deselects
    canvas.addEventListener('click', function (e) {
        if (e.target === canvas || e.target === canvasEmpty || e.target.closest('.canvas-empty')) {
            selectedId = null;
            showFormSettings();
        }
    });

    // ─────────────────────────────────────────────────────────────────
    // Props → sync back to field object on input
    // ─────────────────────────────────────────────────────────────────
    function withSelected(fn) {
        if (!selectedId) return;
        const fieldObj = fields.find(f => f.uid === selectedId);
        if (fieldObj) fn(fieldObj);
    }

    function syncCardDisplay(fieldObj) {
        const card = canvas.querySelector(`[data-uid="${fieldObj.uid}"]`);
        if (!card) return;
        card.querySelector('.field-label-display').textContent = fieldObj.label;
        card.querySelector('.field-type-display').textContent  = fieldObj.type;
        const actions = card.querySelector('.field-card-actions');
        const badge   = actions.querySelector('.req-badge');
        if (fieldObj.required && !badge) {
            const span = document.createElement('span');
            span.className = 'req-badge';
            span.textContent = 'Required';
            actions.insertBefore(span, actions.firstChild);
        } else if (!fieldObj.required && badge) {
            badge.remove();
        }
    }

    propLabel.addEventListener('input', function () {
        withSelected(function (f) {
            f.label = propLabel.value;
            if (!propName.dataset.manual) {
                propName.value = slugify(propLabel.value);
                f.name = propName.value;
            }
            syncCardDisplay(f);
        });
    });
    propName.addEventListener('input', function () {
        propName.dataset.manual = '1';
        withSelected(f => { f.name = propName.value; });
    });
    propPH.addEventListener('input',       function () { withSelected(f => f.placeholder  = propPH.value); });
    propHelp.addEventListener('input',     function () { withSelected(f => f.help         = propHelp.value); });
    propRequired.addEventListener('change',function () { withSelected(f => { f.required = propRequired.checked; syncCardDisplay(f); }); });
    propMin.addEventListener('input',      function () { withSelected(f => f.min          = propMin.value); });
    propMax.addEventListener('input',      function () { withSelected(f => f.max          = propMax.value); });
    propRegex.addEventListener('input',    function () { withSelected(f => f.regex        = propRegex.value); });
    propCustom.addEventListener('input',   function () { withSelected(f => f.custom_rule  = propCustom.value); });
    propChoices.addEventListener('input',  function () { withSelected(f => f.choices      = propChoices.value); });
    propRangeMin.addEventListener('input', function () { withSelected(f => f.range_min    = propRangeMin.value); });
    propRangeMax.addEventListener('input', function () { withSelected(f => f.range_max    = propRangeMax.value); });
    propRangeStep.addEventListener('input',function () { withSelected(f => f.range_step   = propRangeStep.value); });
    propRatingMax.addEventListener('input',function () { withSelected(f => f.rating_max   = propRatingMax.value); });
    propMultiple.addEventListener('change',function () { withSelected(f => f.multiple      = propMultiple.checked); });
    propAccept.addEventListener('input',   function () { withSelected(f => f.accept        = propAccept.value); });
    propLogicField.addEventListener('input',  function () { withSelected(f => { f.logic = getLogic(f); }); });
    propLogicOp.addEventListener('change',    function () { withSelected(f => { f.logic = getLogic(f); }); });
    propLogicVal.addEventListener('input',    function () { withSelected(f => { f.logic = getLogic(f); }); });
    propLogicAct.addEventListener('change',   function () { withSelected(f => { f.logic = getLogic(f); }); });

    function getLogic(f) {
        if (!propLogicField.value.trim()) return null;
        return {
            field: propLogicField.value.trim(),
            operator: propLogicOp.value,
            value: propLogicVal.value,
            action: propLogicAct.value,
            target: f.name,
        };
    }

    // ─────────────────────────────────────────────────────────────────
    // Tab switching (field props)
    // ─────────────────────────────────────────────────────────────────
    qsa('#field-props .fb-tab').forEach(function (tab) {
        tab.addEventListener('click', function () { activateTab(tab.dataset.tab); });
    });

    function activateTab(name) {
        qsa('#field-props .fb-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
        ['basic','validation','options','logic'].forEach(function (t) {
            const pane = el('tab-' + t);
            if (pane) pane.style.display = t === name ? '' : 'none';
        });
    }

    // Settings panel tab switching
    qsa('#form-settings .fb-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            qsa('#form-settings .fb-tab').forEach(t => t.classList.toggle('active', t === tab));
            el('stab-notif').style.display   = tab.dataset.tab === 'notif'   ? '' : 'none';
            el('stab-webhook').style.display = tab.dataset.tab === 'webhook' ? '' : 'none';
        });
    });

    // ─────────────────────────────────────────────────────────────────
    // Recipient chips
    // ─────────────────────────────────────────────────────────────────
    function renderChips() {
        const values = sRecipientsHidden.value.split(',').filter(Boolean);
        recipientChips.innerHTML = '';
        values.forEach(function (email) {
            const chip = document.createElement('span');
            chip.className = 'notification-chip';
            chip.innerHTML = `${escHtml(email)} <button type="button" data-email="${escHtml(email)}">×</button>`;
            chip.querySelector('button').addEventListener('click', function () {
                removeRecipient(email);
            });
            recipientChips.appendChild(chip);
        });
    }
    function addRecipient(email) {
        const list = sRecipientsHidden.value.split(',').filter(Boolean);
        if (email && !list.includes(email)) {
            list.push(email);
            sRecipientsHidden.value = list.join(',');
        }
        renderChips();
    }
    function removeRecipient(email) {
        const list = sRecipientsHidden.value.split(',').filter(e => e !== email);
        sRecipientsHidden.value = list.join(',');
        renderChips();
    }
    sRecipientsInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addRecipient(sRecipientsInput.value.trim());
            sRecipientsInput.value = '';
        }
    });
    renderChips();

    // ─────────────────────────────────────────────────────────────────
    // Test Webhook
    // ─────────────────────────────────────────────────────────────────
    const btnTestWebhook = el('btn-test-webhook');
    if (btnTestWebhook && FORM_ID) {
        btnTestWebhook.addEventListener('click', function () {
            fetch(ADMIN_URLS.testWebhook, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ webhook_url: sWebhookUrl.value }),
            })
            .then(r => r.json())
            .then(d => alert(d.status === 'ok' ? '✅ Webhook responded: ' + d.code : '❌ Error: ' + d.message));
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Test Email
    // ─────────────────────────────────────────────────────────────────
    const btnTestEmail = el('btn-test-email');
    if (btnTestEmail && FORM_ID) {
        btnTestEmail.addEventListener('click', function () {
            const recipients = sRecipientsHidden.value;
            if (!recipients) { alert('Add at least one recipient email.'); return; }
            fetch(ADMIN_URLS.testEmail, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ recipients }),
            })
            .then(r => r.json())
            .then(d => alert(d.status === 'ok' ? '✅ Test email sent.' : '❌ Error: ' + d.message));
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Save Form
    // ─────────────────────────────────────────────────────────────────
    el('btn-save-form').addEventListener('click', function () {
        if (!metaTitle.value.trim()) { alert('Form title is required.'); metaTitle.focus(); return; }

        // Inject meta into form
        setHidden('title',       metaTitle.value);
        setHidden('slug',        metaSlug.value || slugify(metaTitle.value));
        setHidden('description', metaDesc.value);
        setHidden('is_active',   metaActive.checked ? '1' : '0');

        // Sync field order from DOM
        syncFieldOrder();

        // Build fields JSON
        const payload = fields.map(function (f) {
            const options = {};
            if (f.placeholder) options.placeholder = f.placeholder;
            if (f.help)        options.help        = f.help;

            if (CHOICE_TYPES.includes(f.type)) {
                options.choices = choicesTextToObj(f.choices);
            }
            if (RANGE_TYPES.includes(f.type)) {
                options.min  = parseFloat(f.range_min) || 0;
                options.max  = parseFloat(f.range_max) || 100;
                options.step = parseFloat(f.range_step) || 1;
            }
            if (RATING_TYPES.includes(f.type)) {
                options.max = parseInt(f.rating_max) || 5;
            }
            if (FILE_TYPES.includes(f.type)) {
                options.multiple = !!f.multiple;
                if (f.accept) options.accept = f.accept;
            }
            if (f.logic) options.logic = f.logic;

            const validation = {};
            if (f.min)         validation.min    = f.min;
            if (f.max)         validation.max    = f.max;
            if (f.regex)       validation.regex  = f.regex;
            if (f.custom_rule) validation.custom = f.custom_rule;

            return {
                label: f.label, name: f.name, type: f.type,
                required: f.required, options, validation,
            };
        });

        el('fields-json').value = JSON.stringify(payload);

        // Settings JSON
        const settings = {
            enable_notifications: sEnableNotif.checked,
            notification_recipients: sRecipientsHidden.value.split(',').filter(Boolean),
            webhook_enabled: sEnableWebhook.checked,
            webhook_url: sWebhookUrl.value,
        };
        el('settings-json').value = JSON.stringify(settings);

        mainForm.submit();
    });

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────
    function syncFieldOrder() {
        const cards = canvas.querySelectorAll('.field-card[data-uid]');
        const newFields = [];
        cards.forEach(function (card) {
            const f = fields.find(f => f.uid === card.dataset.uid);
            if (f) newFields.push(f);
        });
        fields = newFields;
    }

    function syncEmpty() {
        canvasEmpty.style.display = fields.length ? 'none' : '';
    }

    function setHidden(name, value) {
        let inp = mainForm.querySelector(`[name="${name}"]`);
        if (!inp) {
            inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name;
            mainForm.appendChild(inp);
        }
        inp.value = value;
    }

    function choicesTextToObj(text) {
        const obj = {};
        (text || '').split('\n').forEach(function (line) {
            const parts = line.trim().split('|');
            if (parts.length >= 2) {
                obj[parts[0].trim()] = parts.slice(1).join('|').trim();
            } else if (parts[0].trim()) {
                obj[parts[0].trim()] = parts[0].trim();
            }
        });
        return obj;
    }

    function choicesObjToText(obj) {
        return Object.entries(obj || {}).map(([v, l]) => `${v}|${l}`).join('\n');
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                          .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function csrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    // Initialise form settings panel by default
    showFormSettings();

})();
