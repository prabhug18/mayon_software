window.Quotation = (function () {
    let itemIndex = 0;
    let deleteTargetId = null;

    function initList(selector) {
        const table = $(selector).DataTable({
            ajax: { url: '/quotations', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'quotation_no' },
                { data: null, render: function (row) { return (row.company && row.company.name) || ''; } },
                { data: null, render: function (row) { return (row.enquiry && row.enquiry.name) || '-'; } },
                { data: 'quotation_date', render: function (dt) { if (!dt) return '-'; try { return new Date(dt).toLocaleDateString(); } catch (e) { return dt; } } },
                { data: 'valid_till', render: function (dt) { if (!dt) return '-'; try { return new Date(dt).toLocaleDateString(); } catch (e) { return dt; } } },
                { data: 'quotation_type', render: function (v) { return v ? v.replace('_', ' ') : ''; } },
                { data: 'grand_total', render: function (v) { return '₹ ' + parseFloat(v || 0).toFixed(2); } },
                {
                    data: 'status', render: function (v) {
                        const badges = { DRAFT: 'secondary', SENT: 'info', APPROVED: 'success', REVISED: 'warning' };
                        return `<span class="badge bg-${badges[v] || 'secondary'}">${v}</span>`;
                    }
                },
                {
                    data: null, orderable: false, render: function (data) {
                        return `<a href="/quotations/${data.id}" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>
                    <a href="/quotations/${data.id}/edit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                    <a href="/quotations/${data.id}/pdf" class="btn btn-sm btn-outline-danger me-1" target="_blank"><i class="bi bi-file-pdf"></i></a>
                    <button class="btn btn-sm btn-outline-danger" onclick="Quotation.confirmDelete(${data.id})"><i class="bi bi-trash"></i></button>`;
                    }
                }
            ],
            order: [[0, 'desc']]
        });
        window.QuotationTable = table;
    }

    function initCreateForm(selector) {
        console.log('Initializing Create Form:', selector);
        const form = document.querySelector(selector);
        if (!form) {
            console.warn('Form not found:', selector);
            return;
        }

        // Company change -> fetch quotation number
        const companySelect = document.getElementById('company_id');
        if (companySelect) {
            companySelect.addEventListener('change', function () {
                const companyId = this.value;
                if (!companyId) return;
                fetch(`/quotations/next-number?company_id=${companyId}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        if (d.data && d.data.quotation_no) {
                            document.getElementById('quotation_no').value = d.data.quotation_no;
                        }
                    })
                    .catch(() => console.error('Failed to fetch quotation number'));
            });
        }

        // Enquiry change -> fetch enquiry details and populate items
        const enquirySelect = document.getElementById('enquiry_id');
        if (enquirySelect) {
            enquirySelect.addEventListener('change', function () {
                const enquiryId = this.value;
                if (!enquiryId) {
                    if (document.getElementById('customer_name')) document.getElementById('customer_name').value = '';
                    if (document.getElementById('customer_address')) document.getElementById('customer_address').value = '';
                    return;
                }
                fetch(`/enquiries/${enquiryId}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        const enq = d.data;
                        if (enq) {
                            if (document.getElementById('customer_name')) {
                                document.getElementById('customer_name').value = enq.name || '';
                            }
                            if (document.getElementById('customer_address')) {
                                document.getElementById('customer_address').value = enq.location || '';
                            }

                            if (enq.service_id) {
                            // Find or add a row
                            let row = document.querySelector('.item-row');
                            if (!row) {
                                addItem();
                                row = document.querySelector('.item-row');
                            }

                            const sSelect = row.querySelector('.service-select');
                            const iSelect = row.querySelector('.service-item-select');

                            if (sSelect) {
                                sSelect.value = enq.service_id;
                                // Manually trigger change to load items
                                sSelect.dispatchEvent(new Event('change'));

                                // Wait for items to load before setting service item
                                if (enq.service_item_id) {
                                    setTimeout(() => {
                                        if (iSelect) {
                                            iSelect.value = enq.service_item_id;
                                            iSelect.dispatchEvent(new Event('change'));
                                        }
                                    }, 500);
                                }
                            }
                            }
                        }
                    })
                    .catch(() => console.error('Failed to fetch enquiry details'));
            });
        }

        // Add first item on load
        addItem();

        // Add item button
        const addBtn = document.getElementById('add-item-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                addItem();
            });
        }

        // Summernote Initialization for Terms Content
        initEditor('#terms_content');

        // Terms Selection Change
        const termsSelect = document.getElementById('terms_condition_id');
        if (termsSelect) {
            termsSelect.addEventListener('change', function () {
                const id = this.value;
                if (!id) {
                    $('#terms_content').summernote('code', '');
                    return;
                }
                fetch(`/terms-conditions/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.data && d.data.content) {
                            $('#terms_content').summernote('code', d.data.content);
                        }
                    })
                    .catch(e => console.error('Failed to fetch terms', e));
            });
        }

        // Form submission
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Quotation form submit triggered');

            try {
                clearErrors();

                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
                }

                // Sync Summernote content back to textarea
                let termsContent = '';
                try {
                    const editor = $('#terms_content');
                    if (editor.length && editor.data('summernote')) {
                        termsContent = editor.summernote('code');
                    } else {
                        const ta = document.getElementById('terms_content');
                        termsContent = ta ? ta.value : '';
                    }
                } catch (se) {
                    console.warn('Summernote sync failed', se);
                    const ta = document.getElementById('terms_content');
                    termsContent = ta ? ta.value : '';
                }

                const fd = new FormData(form);
                const data = {};
                fd.forEach((value, key) => {
                    if (key.includes('[')) {
                        const match = key.match(/^(.+?)\[(\d+)\]\[(.+)\]$/);
                        if (match) {
                            const arrayName = match[1];
                            const index = parseInt(match[2]);
                            const fieldName = match[3];
                            if (!data[arrayName]) data[arrayName] = [];
                            if (!data[arrayName][index]) data[arrayName][index] = {};
                            data[arrayName][index][fieldName] = value;
                        }
                    } else if (key === 'terms_content' || key === '_token') {
                        // handled separately
                    } else {
                        data[key] = value;
                    }
                });

                // Compact the items array to remove any gaps from deleted rows
                if (data.items && Array.isArray(data.items)) {
                    data.items = data.items.filter(item => item !== undefined && item !== null);
                }

                data.terms_content = termsContent;

                console.log('Submitting Quotation Data:', data);

                const token = typeof window.getCsrfToken === 'function' ? window.getCsrfToken() : '';

                fetch('/quotations', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(r => {
                        if (r.status == 422) return r.json().then(x => { throw { validation: x }; });
                        if (!r.ok) return r.json().then(x => { throw { error: x.message || 'Server error' }; });
                        return r.json();
                    })
                    .then(d => {
                        showAlert(d.message || 'Quotation created successfully');
                        setTimeout(() => { window.location.href = '/quotations'; }, 900);
                    })
                    .catch(err => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Create Quotation';
                        }
                        console.error('Submission Error:', err);

                        if (err.validation && err.validation.errors) {
                            console.table(err.validation.errors);
                            const errors = err.validation.errors;
                            for (const k in errors) {
                                let targetId = k.replace(/\./g, '_');
                                let errorEl = document.getElementById(k + '-error') || document.getElementById(targetId + '-error');
                                if (errorEl) {
                                    errorEl.textContent = errors[k][0];
                                    errorEl.style.display = 'block';
                                }
                                let input = document.getElementById(k) || document.getElementsByName(k)[0];
                                if (input) input.classList.add('is-invalid');
                            }
                            showAlert('Please fix the validation errors.', 'danger');
                        } else {
                            showAlert(err.error || 'Error creating quotation', 'danger');
                        }
                    });
            } catch (fatal) {
                console.error('Fatal JS error during submit:', fatal);
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Create Quotation';
                }
                alert('An error occurred before submission: ' + fatal.message);
            }
        });
    }

    function initEditForm(selector) {
        console.log('Initializing Edit Form:', selector);
        const form = document.querySelector(selector);
        if (!form) {
            console.warn('Form not found:', selector);
            return;
        }

        const quotationId = form.dataset.quotationId;
        if (!quotationId) {
            console.warn('Quotation ID missing on form dataset');
            return;
        }

        // Initialize existing items
        itemIndex = document.querySelectorAll('.item-row').length;

        // Add item button
        const addBtn = document.getElementById('add-item-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                addItem();
            });
        }

        // Wire up existing items
        wireItemEvents();

        // Summernote Initialization for Terms Content
        initEditor('#terms_content');

        // Terms Selection Change
        const termsSelect = document.getElementById('terms_condition_id');
        if (termsSelect) {
            termsSelect.addEventListener('change', function () {
                const id = this.value;
                if (!id) {
                    $('#terms_content').summernote('code', '');
                    return;
                }
                fetch(`/terms-conditions/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.data && d.data.content) {
                            $('#terms_content').summernote('code', d.data.content);
                        }
                    })
                    .catch(e => console.error('Failed to fetch terms', e));
            });
        }

        // Form submission
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Quotation edit form submit triggered');

            try {
                clearErrors();

                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
                }

                // Sync Summernote content
                let termsContent = '';
                try {
                    const editor = $('#terms_content');
                    if (editor.length && editor.data('summernote')) {
                        termsContent = editor.summernote('code');
                    } else {
                        const ta = document.getElementById('terms_content');
                        termsContent = ta ? ta.value : '';
                    }
                } catch (se) {
                    console.warn('Summernote sync failed', se);
                    const ta = document.getElementById('terms_content');
                    termsContent = ta ? ta.value : '';
                }

                const fd = new FormData(form);
                const data = {};
                fd.forEach((value, key) => {
                    if (key.includes('[')) {
                        const match = key.match(/^(.+?)\[(\d+)\]\[(.+)\]$/);
                        if (match) {
                            const arrayName = match[1];
                            const index = parseInt(match[2]);
                            const fieldName = match[3];
                            if (!data[arrayName]) data[arrayName] = [];
                            if (!data[arrayName][index]) data[arrayName][index] = {};
                            data[arrayName][index][fieldName] = value;
                        }
                    } else if (key === 'terms_content' || key === '_token') {
                        // handled separately
                    } else {
                        data[key] = value;
                    }
                });

                // Compact the items array to remove any gaps from deleted rows
                if (data.items && Array.isArray(data.items)) {
                    data.items = data.items.filter(item => item !== undefined && item !== null);
                }

                data.terms_content = termsContent;

                const token = typeof window.getCsrfToken === 'function' ? window.getCsrfToken() : '';

                fetch(`/quotations/${quotationId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-HTTP-Method-Override': 'PUT',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(r => {
                        if (r.status == 422) return r.json().then(x => { throw { validation: x }; });
                        if (!r.ok) return r.json().then(x => { throw { error: x.message || 'Server error' }; });
                        return r.json();
                    })
                    .then(d => {
                        showAlert(d.message || 'Quotation updated successfully');
                        setTimeout(() => { window.location.href = '/quotations'; }, 900);
                    })
                    .catch(err => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Update Quotation';
                        }
                        console.error('Submission Error:', err);

                        if (err.validation && err.validation.errors) {
                            console.table(err.validation.errors);
                            const errors = err.validation.errors;
                            for (const k in errors) {
                                let targetId = k.replace(/\./g, '_');
                                let errorEl = document.getElementById(k + '-error') || document.getElementById(targetId + '-error');
                                if (errorEl) {
                                    errorEl.textContent = errors[k][0];
                                    errorEl.style.display = 'block';
                                }
                                let input = document.getElementById(k) || document.getElementsByName(k)[0];
                                if (input) input.classList.add('is-invalid');
                            }
                            showAlert('Please fix the validation errors.', 'danger');
                        } else {
                            showAlert(err.error || 'Error updating quotation', 'danger');
                        }
                    });
            } catch (fatal) {
                console.error('Fatal JS error during submit:', fatal);
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Update Quotation';
                }
                alert('An error occurred before submission: ' + fatal.message);
            }
        });
    }

    function addItem() {
        try {
            const template = document.getElementById('item-row-template');
            if (!template) {
                console.warn('Item row template not found');
                return;
            }

            const tbody = document.getElementById('items-tbody');
            if (!tbody) {
                console.warn('Items tbody not found');
                return;
            }

            // Create a temporary container to parse the template
            const temp = document.createElement('tbody');
            temp.innerHTML = template.innerHTML.replace(/INDEX/g, itemIndex);
            
            // Get all rows from the template (should be 2 now)
            const rows = Array.from(temp.querySelectorAll('tr'));
            rows.forEach(row => tbody.appendChild(row));

            // Wire events using the main row (the first one)
            wireItemRowEvents(rows[0], rows[1]);

            itemIndex++;
            console.log('Added item rows', itemIndex);
        } catch (e) {
            console.error('Error in addItem:', e);
        }
    }

    function wireItemEvents() {
        // In edit mode, we need to pair rows. Every item has 2 rows.
        const allRows = document.querySelectorAll('.item-row');
        for(let i=0; i<allRows.length; i+=2) {
            if(allRows[i] && allRows[i+1]) {
                wireItemRowEvents(allRows[i], allRows[i+1]);
            }
        }
    }

    function wireItemRowEvents(mainRow, descRow) {
        // Manual toggle
        const manualToggle = mainRow.querySelector('.manual-toggle');
        const serviceSelect = mainRow.querySelector('.service-select');
        const manualServiceInput = mainRow.querySelector('.manual-service-input');
        const itemSelect = mainRow.querySelector('.service-item-select');
        const manualItemInput = mainRow.querySelector('.manual-item-input');

        if (manualToggle) {
            manualToggle.addEventListener('change', function () {
                if (this.checked) {
                    serviceSelect.classList.add('d-none');
                    serviceSelect.removeAttribute('required');
                    manualServiceInput.classList.remove('d-none');
                    // manualServiceInput.setAttribute('required', 'required'); // Made optional as per request

                    itemSelect.classList.add('d-none');
                    itemSelect.removeAttribute('required');
                    manualItemInput.classList.remove('d-none');
                    manualItemInput.setAttribute('required', 'required');
                } else {
                    serviceSelect.classList.remove('d-none');
                    serviceSelect.setAttribute('required', 'required');
                    manualServiceInput.classList.add('d-none');
                    manualServiceInput.removeAttribute('required');

                    itemSelect.classList.remove('d-none');
                    itemSelect.setAttribute('required', 'required');
                    manualItemInput.classList.add('d-none');
                    manualItemInput.removeAttribute('required');
                }
            });
        }

        // Service change -> load items
        const unitInput = mainRow.querySelector('.unit-input');
        const descInput = descRow ? descRow.querySelector('.description-input') : mainRow.querySelector('.description-input');
        const gstInput = mainRow.querySelector('.gst-input');

        if (serviceSelect && itemSelect) {
            serviceSelect.addEventListener('change', function () {
                const serviceId = this.value;
                if (!serviceId) {
                    itemSelect.innerHTML = '<option value="">Select Item</option>';
                    return;
                }

                fetch(`/quotations/service-items?service_id=${serviceId}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(d => {
                        const items = d.data || [];
                        itemSelect.innerHTML = '<option value="">Select Item</option>';
                        items.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.item_name;
                            opt.dataset.unit = (item.unit_master && item.unit_master.name) || item.unit || '';
                            opt.dataset.description = item.description || '';
                            opt.dataset.gst = item.default_gst_percentage || 18;
                            opt.dataset.rate = item.default_rate || 0;
                            itemSelect.appendChild(opt);
                        });
                    })
                    .catch(() => console.error('Failed to load service items'));
            });
        }
        // Service item change -> populate unit, description, GST
        if (itemSelect) {
            itemSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                if (selected && selected.dataset) {
                    if (unitInput) unitInput.value = selected.dataset.unit || '';
                    if (descInput && !descInput.value) descInput.value = selected.dataset.description || '';
                    if (gstInput) gstInput.value = selected.dataset.gst || 18;
                    const baseCostInput = mainRow.querySelector('.base-cost-input');
                    if (baseCostInput) {
                        baseCostInput.value = selected.dataset.rate || 0;
                        // Manually trigger input event to update selling rate
                        baseCostInput.dispatchEvent(new Event('input'));
                    }
                }
                calculateLineTotal(mainRow);
            });
        }

        // Base cost or margin change -> calculate selling rate
        const baseCostInput = mainRow.querySelector('.base-cost-input');
        const marginTypeSelect = mainRow.querySelector('.margin-type-select');
        const marginValueInput = mainRow.querySelector('.margin-value-input');
        const sellingRateInput = mainRow.querySelector('.selling-rate-input');

        [baseCostInput, marginTypeSelect, marginValueInput].forEach(el => {
            if (el) {
                el.addEventListener('input', function () {
                    const baseCost = parseFloat(baseCostInput?.value || 0);
                    const marginType = marginTypeSelect?.value || 'PERCENTAGE';
                    const marginValue = parseFloat(marginValueInput?.value || 0);

                    let sellingRate = baseCost;
                    if (marginType === 'PERCENTAGE') {
                        sellingRate = baseCost + (baseCost * (marginValue / 100));
                    } else {
                        sellingRate = baseCost + marginValue;
                    }

                    if (sellingRateInput) sellingRateInput.value = sellingRate.toFixed(2);
                    calculateLineTotal(mainRow);
                });
            }
        });

        // Selling rate, quantity, GST change -> calculate line total
        const quantityInput = mainRow.querySelector('.quantity-input');
        [sellingRateInput, quantityInput, gstInput].forEach(el => {
            if (el) {
                el.addEventListener('input', function () {
                    calculateLineTotal(mainRow);
                });
            }
        });

        // Remove button
        const removeBtn = mainRow.querySelector('.remove-item-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                mainRow.remove();
                if(descRow) descRow.remove();
                calculateTotals();
            });
        }
    }

    function calculateLineTotal(row) {
        const sellingRate = parseFloat(row.querySelector('.selling-rate-input')?.value || 0);
        const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 1);
        const unit = row.querySelector('.unit-input')?.value || '';
        const gst = parseFloat(row.querySelector('.gst-input')?.value || 0);

        let subtotal = (unit === 'LS') ? sellingRate : (sellingRate * quantity);
        let gstAmount = subtotal * (gst / 100);
        let lineTotal = subtotal + gstAmount;

        const lineTotalInput = row.querySelector('.line-total-input');
        if (lineTotalInput) lineTotalInput.value = lineTotal.toFixed(2);

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let gstTotal = 0;

        document.querySelectorAll('.item-main-row').forEach(row => {
            const sellingRate = parseFloat(row.querySelector('.selling-rate-input')?.value || 0);
            const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 1);
            const unit = row.querySelector('.unit-input')?.value || '';
            const gst = parseFloat(row.querySelector('.gst-input')?.value || 0);

            let itemSubtotal = (unit === 'LS') ? sellingRate : (sellingRate * quantity);
            let itemGst = itemSubtotal * (gst / 100);

            subtotal += itemSubtotal;
            gstTotal += itemGst;
        });

        const grandTotal = subtotal + gstTotal;

        const subtotalDisplay = document.getElementById('subtotal-display');
        const gstTotalDisplay = document.getElementById('gst-total-display');
        const grandTotalDisplay = document.getElementById('grand-total-display');

        if (subtotalDisplay) subtotalDisplay.textContent = '₹ ' + subtotal.toFixed(2);
        if (gstTotalDisplay) gstTotalDisplay.textContent = '₹ ' + gstTotal.toFixed(2);
        if (grandTotalDisplay) grandTotalDisplay.textContent = '₹ ' + grandTotal.toFixed(2);
    }

    function confirmDelete(id) {
        deleteTargetId = id;
        if (confirm('Are you sure you want to delete this quotation?')) {
            performDelete();
        }
    }

    function performDelete() {
        if (!deleteTargetId) return;
        fetch(`/quotations/${deleteTargetId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(r => r.json())
            .then(data => {
                showAlert(data.message || 'Deleted');
                if (window.QuotationTable) window.QuotationTable.ajax.reload();
                deleteTargetId = null;
            })
            .catch(() => { showAlert('Error deleting quotation'); });
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function initEditor(selector) {
        const area = $(selector);
        if (!area.length) return;

        function applyEditor() {
            if (typeof $.fn.summernote === 'function') {
                area.summernote({
                    height: 250,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    callbacks: {
                        onInit: function () {
                            console.log('Summernote initialized on', selector);
                        }
                    }
                });
            } else {
                console.warn('Summernote plugin not ready, retrying...');
                setTimeout(applyEditor, 200);
            }
        }
        applyEditor();
    }

    return { initList, initCreateForm, initEditForm, confirmDelete };
})();
