document.addEventListener('DOMContentLoaded', function() {
    // Load table if present
    if (document.getElementById('po-table')) {
        fetch(window.location.href, {headers: {'Accept':'application/json'}})
            .then(r=>r.json()).then(data=>{
                const tbody = document.querySelector('#po-table tbody');
                tbody.innerHTML = '';
                (data.data || []).forEach(function(po, idx){
                    const tr = document.createElement('tr');
                    // format po_date into human readable form
                    function formatDate(d) {
                        if (!d) return '';
                        const dd = new Date(d);
                        if (isNaN(dd)) return d;
                        return dd.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                    }
                    const poDate = formatDate(po.po_date || po.date);
                    tr.innerHTML = `
                        <td>${idx+1}</td>
                        <td><a href="/purchaseOrders/${po.id}" target="_blank" rel="noopener">${po.po_number}</a></td>
                        <td>${po.supplier?po.supplier.name:''}</td>
                        <td>${po.project?po.project.name:''}</td>
                        <td>${poDate}</td>
                        <td>${(typeof window.currencySymbol !== 'undefined' ? window.currencySymbol + ' ' : '') + (po.amount ? Number(po.amount).toFixed(2) : '')}</td>
                        <td>
                            <a class="btn btn-sm btn-secondary me-1" href="/purchaseOrders/${po.id}" target="_blank" rel="noopener">View</a>
                            <a class="btn btn-sm btn-primary" href="/purchaseOrders/${po.id}/edit">Edit</a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // Supplier autosuggest
    const supplierText = document.getElementById('supplier_text');
    const supplierSuggestions = document.getElementById('supplier-suggestions');
    // helper to populate supplier detail cards
    function populateSupplierDetails(s) {
        if (!s) return;
        const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '-'; };
        setText('supplier_company', s.name || '-');
        setText('supplier_contact', s.contact_person || '-');
        setText('supplier_phone', s.mobile || '-');
        setText('supplier_gst', s.gst_no || '-');
        const addrParts = [];
        if (s.address_line1) addrParts.push(s.address_line1);
        if (s.address_line2) addrParts.push(s.address_line2);
        if (s.city) addrParts.push(s.city);
        if (s.pincode) addrParts.push(s.pincode);
        if (s.location && !addrParts.includes(s.location)) addrParts.unshift(s.location);
        setText('supplier_address', addrParts.join(', ') || (s.location || '-'));
    }
    if (supplierText) {
        let timer; supplierText.addEventListener('input', function(){
            const q = supplierText.value.trim();
            const supplierIdEl = document.getElementById('supplier_id'); if (supplierIdEl) supplierIdEl.value = '';
            if (timer) clearTimeout(timer);
            supplierSuggestions.innerHTML = '';
            // hide details when query too short
            if (q.length < 3) {
                supplierSuggestions.classList.add('d-none');
                const clearText = (id) => { const el = document.getElementById(id); if (el) el.textContent = '-'; };
                ['supplier_company','supplier_contact','supplier_phone','supplier_gst','supplier_address'].forEach(clearText);
                return;
            }
            timer = setTimeout(()=>{
                fetch('/purchaseOrders/suppliers/search?q='+encodeURIComponent(q), {headers:{'Accept':'application/json'}})
                    .then(r=>r.json()).then(res=>{
                        const list = res.data || [];
                        supplierSuggestions.innerHTML = '';
                        if (list.length) {
                            supplierSuggestions.classList.remove('d-none');
                        } else {
                            supplierSuggestions.classList.add('d-none');
                        }
                        list.forEach(s => {
                            const el = document.createElement('button');
                            el.type='button'; el.className='list-group-item list-group-item-action text-start';
                            const title = document.createElement('div'); title.className = 'fw-semibold'; title.textContent = s.name;
                            const sub = document.createElement('div'); sub.className = 'small text-muted';
                            const cp = s.contact_person ? (s.contact_person + (s.mobile?(' • '+s.mobile):'')) : (s.mobile? s.mobile : '');
                            sub.textContent = cp + (s.city?(' • '+s.city):'');
                            el.appendChild(title); el.appendChild(sub);
                            el.addEventListener('click', function(){
                                document.getElementById('supplier_text').value = s.name;
                                document.getElementById('supplier_id').value = s.id;
                                supplierSuggestions.innerHTML = '';
                                supplierSuggestions.classList.add('d-none');
                                populateSupplierDetails(s);
                            });
                            supplierSuggestions.appendChild(el);
                        });
                    }).catch(()=>{
                        supplierSuggestions.classList.add('d-none');
                    });
            },300);
        });

        // hide suggestions when clicking outside
        document.addEventListener('click', function(e){
            if (!supplierSuggestions) return;
            if (e.target === supplierText) return;
            if (!supplierSuggestions.contains(e.target)) {
                supplierSuggestions.classList.add('d-none');
            }
        });

        // hide with Escape key
        supplierText.addEventListener('keydown', function(e){ if (e.key === 'Escape') supplierSuggestions.classList.add('d-none'); });
    }

    // Add supplier modal flow
    const addSupplierBtn = document.getElementById('add-supplier-btn');
    if (addSupplierBtn) {
        addSupplierBtn.addEventListener('click', function(){
            const modal = new bootstrap.Modal(document.getElementById('supplierModal'));
            modal.show();
        });
    }

    // wire Add Product button if present
    const addProductBtn = document.getElementById('add-product-btn');
    if (addProductBtn) addProductBtn.addEventListener('click', function(){ if (typeof window.submitProduct === 'function') window.submitProduct(); });

    // Product autosuggest
    const productText = document.getElementById('product_text');
    const productSuggestions = document.getElementById('product-suggestions');
    let selectedProduct = null; // store selected product object from server
    if (productText) {
        let pTimer; productText.addEventListener('input', function(){
            const q = productText.value.trim();
            selectedProduct = null; document.getElementById('product_id').value = '';
            if (pTimer) clearTimeout(pTimer);
            productSuggestions.innerHTML = '';
            if (q.length < 3) { productSuggestions.classList.add('d-none'); return; }
            pTimer = setTimeout(()=>{
                fetch('/products/search?q='+encodeURIComponent(q), {headers:{'Accept':'application/json'}})
                    .then(r=>r.json()).then(res=>{
                        const list = res.data || [];
                        productSuggestions.innerHTML = '';
                        if (list.length) productSuggestions.classList.remove('d-none'); else productSuggestions.classList.add('d-none');
                        list.forEach(p => {
                            const el = document.createElement('button'); el.type='button'; el.className='list-group-item list-group-item-action text-start';
                            const title = document.createElement('div'); title.className = 'fw-semibold'; title.textContent = p.name;
                            const sub = document.createElement('div'); sub.className = 'small text-muted';
                            const meta = [];
                            if (p.category && p.category.name) meta.push(p.category.name);
                            if (p.uom && p.uom.name) meta.push(p.uom.name);
                            sub.textContent = meta.join(' • ');
                            el.appendChild(title); el.appendChild(sub);
                            el.addEventListener('click', function(){
                                productText.value = p.name;
                                document.getElementById('product_id').value = p.id;
                                selectedProduct = p;
                                productSuggestions.innerHTML = ''; productSuggestions.classList.add('d-none');
                            });
                            productSuggestions.appendChild(el);
                        });
                    }).catch(()=>{ productSuggestions.classList.add('d-none'); });
            },300);
        });
        // click outside
        document.addEventListener('click', function(e){ if (!productSuggestions) return; if (e.target === productText) return; if (!productSuggestions.contains(e.target)) productSuggestions.classList.add('d-none'); });
        productText.addEventListener('keydown', function(e){ if (e.key === 'Escape') productSuggestions.classList.add('d-none'); });
    }

    // Supplier modal save
    const modalSave = document.getElementById('modal-save-supplier');
    if (modalSave) modalSave.addEventListener('click', function(){
        const form = document.getElementById('supplier-modal-form');
        const fd = new FormData(form);
        // clear errors
        ['modal_name','modal_contact_person','modal_mobile','modal_email','modal_location'].forEach(f=>{ const el=document.getElementById(f); if(el) el.classList.remove('is-invalid'); const err=document.getElementById(f+'-error'); if(err) err.textContent=''; });
        fetch('/suppliers', {method:'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json'}, body: fd})
            .then(r=>r.json()).then(data=>{
                if (data.status === 'success') {
                    const supplier = data.data;
                    // close modal
                    const modalEl = document.getElementById('supplierModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    // prefill supplier fields and details
                    document.getElementById('supplier_text').value = supplier.name;
                    document.getElementById('supplier_id').value = supplier.id;
                    populateSupplierDetails(supplier);
                } else if (data.errors) {
                    for (const f in data.errors) {
                        const el = document.getElementById('modal_'+f);
                        if (el) {
                            el.classList.add('is-invalid');
                            const err = document.getElementById('modal_'+f+'-error'); if (err) err.textContent = data.errors[f][0];
                        }
                    }
                }
            });
    });

    // Quotation table helpers (add row, totals)
    function ensureQuotationTable() {
        const container = document.getElementById('quotationContainer');
        if (!container) return null;
        if (!document.getElementById('quotationTable')) {
            container.innerHTML = `
                <table id="quotationTable" class="table table-bordered">
                    <thead>
                                <tr>
                                    <th style="width:5%">S.No</th>
                                    <th style="width:15%">Category</th>
                                    <th style="width:30%">Product Name</th>
                                    <th style="width:8%">UOM</th>
                                    <th style="width:8%">Qty</th>
                                    <th style="width:12%">Rate</th>
                                    <th style="width:12%">Amount</th>
                                    <th style="width:10%">Action</th>
                                </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total</td>
                            <td id="grandTotal" class="fw-bold">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            `;
        }
        return document.getElementById('quotationTable');
    }

    function renumberRows() {
           const rows = document.querySelectorAll('#quotationTable tbody tr');
           rows.forEach((tr, idx) => { const sn = tr.querySelector('td'); if (sn) sn.textContent = idx+1; });
    }

    function updateRowAmount(row) {
        if (!row) return;
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const rateRaw = row.querySelector('.rate-input')?.value;
        const span = row.querySelector('.amount-display');
        // If rate not provided (empty), leave amount blank to reflect absence
        if (rateRaw === '' || rateRaw === null || typeof rateRaw === 'undefined') {
            if (span) span.textContent = '';
            return;
        }
        const rate = parseFloat(rateRaw) || 0;
        const amt = qty * rate;
        if (span) span.textContent = amt.toFixed(2);
    }

    // Floating validation popup using Bootstrap alert styles
    function showValidationPopup(message, type = 'warning') {
        // create container once
        let container = document.getElementById('validation-popup-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'validation-popup-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = 2000;
            document.body.appendChild(container);
        }
        const id = 'vp-' + Date.now();
        const alert = document.createElement('div');
        alert.id = id;
        alert.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
        alert.role = 'alert';
        alert.style.minWidth = '260px';
        alert.innerHTML = `
            <div class="small">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(alert);
        // auto remove after 4 seconds
        setTimeout(()=>{
            try { const bsAlert = bootstrap.Alert.getOrCreateInstance(alert); bsAlert.close(); } catch(e){ alert.remove(); }
        }, 4000);
    }

    function updateTotals() {
        let sum = 0;
        const rows = document.querySelectorAll('#quotationTable tbody tr');
        rows.forEach(tr => {
                const span = tr.querySelector('.amount-display');
                const amtRaw = span ? (span.textContent || '').trim() : '';
                if (amtRaw !== '') {
                    const amt = parseFloat(amtRaw.replace(/,/g,'')) || 0;
                    sum += amt;
                }
            });
            const g = document.getElementById('grandTotal'); if (g) g.textContent = sum.toFixed(2);
    }

    function addQuotationRow(item) {
        const tbl = ensureQuotationTable(); if (!tbl) return;
        const tbody = tbl.querySelector('tbody');
        const tr = document.createElement('tr');
        const desc = item && (item.description || item.product) ? (item.description || item.product) : '';
        const qtyVal = item && item.quantity ? item.quantity : '';
        const rateVal = (item && (item.unit_price !== undefined && item.unit_price !== null)) ? item.unit_price : '';
        const totalVal = (item && (item.total !== undefined && item.total !== null) && item.total !== '') ? parseFloat(item.total).toFixed(2) : '';
        tr.innerHTML = `
            <td>0</td>
            <td>${(item && item.category) ? item.category : ''}</td>
            <td>${desc}<input type="hidden" class="product-id-input" value="${(item && item.product_id) ? item.product_id : ''}"></td>
            <td>${(item && (item.uom || item.uom_name)) ? (item.uom?.name || item.uom_name || item.uom) : ''}
                <input type="hidden" class="uom-input" value="${(item && (item.uom || item.uom_name)) ? (item.uom?.name || item.uom_name || item.uom) : ''}">
                <input type="hidden" class="uom-id-input" value="${(item && item.uom_id) ? item.uom_id : (item && item.uom && item.uom.id ? item.uom.id : '')}">
            </td>
        <td><input type="number" min="0" step="any" class="form-control form-control-sm qty-input" value="${qtyVal}" /></td>
        <td><input type="number" min="0" step="any" class="form-control form-control-sm rate-input" value="${rateVal}" /></td>
        <td><span class="amount-display fw-semibold">${totalVal}</span></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">Remove</button></td>
        `;
        tbody.appendChild(tr);
        // attach handlers
        tr.querySelector('.qty-input').addEventListener('input', function(){ updateRowAmount(tr); updateTotals(); });
        tr.querySelector('.rate-input').addEventListener('input', function(){ updateRowAmount(tr); updateTotals(); });
        tr.querySelector('.remove-row').addEventListener('click', function(){ tr.remove(); renumberRows(); updateTotals(); });
        renumberRows(); updateTotals();
    }

    // Called from inline onclick in template
    window.submitProduct = function() {
        const text = document.getElementById('product_text'); if (!text) return;
        const val = (document.getElementById('product_id')?.value || '').trim();
        const name = text.value.trim();
        if (!name) return;
        // If user selected from suggestions, use that product object
        if (val && selectedProduct) {
            const prod = selectedProduct;
            const cat = (prod.category && prod.category.name) ? prod.category.name : '';
            addQuotationRow({
                product: prod.name,
                description: prod.name,
                category: cat,
                quantity:1,
                unit_price:'',
                total:'',
                product_id: prod.id,
                uom: prod.uom || null,
                uom_id: prod.uom && prod.uom.id ? prod.uom.id : null
            });
        } else {
            // fallback: attempt to map via local helper or just use name
            const category = getCategoryForProduct(name);
            addQuotationRow({product: name, description: name, category: category, quantity:1, unit_price:'', total:''});
        }
        // clear input
        text.value = '';
        selectedProduct = null; if (document.getElementById('product_id')) document.getElementById('product_id').value = '';
    };

    // Simple mapping for product -> category. Kept lightweight and editable.
    function getCategoryForProduct(productName) {
        const map = {
            'Msand': 'Construction Material',
            'Steel': 'Metals',
            'Pipes': 'Piping'
        };
        return map[productName] || '';
    }

    // If editing, prefill existing items
    if (window.PO_EDIT_ITEMS && Array.isArray(window.PO_EDIT_ITEMS) && window.PO_EDIT_ITEMS.length) {
        window.PO_EDIT_ITEMS.forEach(it => addQuotationRow({description: it.description, quantity: it.quantity, unit_price: (it.unit_price !== undefined && it.unit_price !== null) ? it.unit_price : '', total: (it.total !== undefined && it.total !== null) ? it.total : ''}));
    }

    // PO form submit (create + edit)
    const poForm = document.getElementById('po-form');
    if (poForm) {
        // When company is selected, fetch the next available PO number and display it
        const companySelect = document.getElementById('company_id');
        if (companySelect) {
            let companyTimer = null;
            companySelect.addEventListener('change', function(){
                const companyId = companySelect.value;
                const poNumberEl = document.getElementById('po_number');
                if (!companyId) { if (poNumberEl) poNumberEl.value = ''; return; }
                // Optimistically set prefix from option attribute if present
                const opt = companySelect.options[companySelect.selectedIndex];
                const prefix = opt && opt.dataset ? (opt.dataset.poPrefix || '') : '';
                if (prefix && poNumberEl && (!poNumberEl.value || poNumberEl.value.trim()==='')) {
                    // set a temporary visual prefix + timestamp while we fetch the authoritative number
                    const tmp = prefix + new Date().getTime().toString().slice(-6);
                    poNumberEl.value = tmp;
                }
                if (companyTimer) clearTimeout(companyTimer);
                companyTimer = setTimeout(()=>{
                    fetch('/purchaseOrders/next-number?company_id='+encodeURIComponent(companyId), {headers:{'Accept':'application/json'}})
                        .then(r=>r.json()).then(res=>{
                            if (res && res.data && res.data.po_number) {
                                if (poNumberEl) poNumberEl.value = res.data.po_number;
                            }
                        }).catch(err=>{
                            console.debug('Failed to fetch next PO number', err);
                        });
                }, 250);
            });
        }
        poForm.addEventListener('submit', function(e){
            e.preventDefault();
            // Client-side validation: supplier, company, project, site engineer, products
            const missing = [];
            const supplierId = (document.getElementById('supplier_id')?.value || '').trim();
            const companyEl = document.querySelector('[name="company_id"]');
            const companyVal = (companyEl && companyEl.value) ? companyEl.value.trim() : '';
            const projectVal = (document.getElementById('project_id')?.value || '').trim();
            const siteEngineerVal = (document.getElementById('site_engineer_id')?.value || '').trim();
            const existingRows = document.querySelectorAll('#quotationTable tbody tr');

            // helper to show inline invalid feedback
            function setFieldInvalid(key, msg) {
                const el = document.getElementById(key) || document.querySelector('[name="'+key+'"]');
                if (!el) return;
                el.classList.add('is-invalid');
                const errId = (el.id || key) + '-error';
                let err = document.getElementById(errId);
                if (!err) {
                    err = document.createElement('div');
                    err.id = errId;
                    err.className = 'invalid-feedback d-block';
                    // prefer appending after the element
                    if (el.parentNode) el.parentNode.appendChild(err);
                }
                err.textContent = msg;
            }

            // clear previous validation states for relevant fields
            ['supplier_text','company_id','project_id','site_engineer_id','quotationContainer'].forEach(k=>{
                const el = document.getElementById(k) || document.querySelector('[name="'+k+'"]');
                if (el) el.classList.remove('is-invalid');
                const err = document.getElementById((el && el.id ? el.id : k)+'-error'); if (err) err.textContent = '';
            });

            if (!supplierId) missing.push('Supplier');
            if (!companyVal) missing.push('Company');
            if (!projectVal) missing.push('Project');
            if (!siteEngineerVal) missing.push('Site Engineer');
            if (!existingRows || existingRows.length === 0) missing.push('Products');

            if (missing.length) {
                // mark fields invalid where possible
                if (!supplierId) setFieldInvalid('supplier_text', 'Please select a supplier');
                if (!companyVal) setFieldInvalid('company_id', 'Please choose a company');
                if (!projectVal) setFieldInvalid('project_id', 'Please choose a project');
                if (!siteEngineerVal) setFieldInvalid('site_engineer_id', 'Please choose a site engineer');
                if (!existingRows || existingRows.length === 0) {
                    // show message near quotationContainer if possible
                    const qc = document.getElementById('quotationContainer');
                    if (qc) {
                        let err = document.getElementById('quotationContainer-error');
                        if (!err) { err = document.createElement('div'); err.id = 'quotationContainer-error'; err.className = 'invalid-feedback d-block'; qc.appendChild(err); }
                        err.textContent = 'Please add at least one product';
                    }
                }
                // show nicer popup instead of blocking alert()
                showValidationPopup('Please fill/select the following required fields: ' + missing.join(', '));
                // focus first invalid field if any
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    try { firstInvalid.focus(); } catch(e){}
                }
                return;
            }

            // Validate product rows: ensure Qty > 0. Rate/Amount are optional and may be left blank.
            const rowsToValidate = document.querySelectorAll('#quotationTable tbody tr');
            const rowErrors = [];
            rowsToValidate.forEach((r, idx) => {
                const qtyInput = r.querySelector('.qty-input');
                const qty = parseFloat(qtyInput?.value) || 0;
                // clear previous per-row errors
                if (qtyInput) qtyInput.classList.remove('is-invalid');
                const existingRowErr = r.querySelector('.row-error'); if (existingRowErr) existingRowErr.remove();

                if (qty <= 0 || isNaN(qty)) {
                    rowErrors.push({index: idx+1, qty, row: r});
                    if (qtyInput) qtyInput.classList.add('is-invalid');
                    const td = r.querySelector('td:last-child');
                    if (td) {
                        const err = document.createElement('div'); err.className = 'text-danger small row-error'; err.textContent = 'Enter valid Qty'; td.appendChild(err);
                    }
                }
            });

            if (rowErrors.length) {
                showValidationPopup('Please correct product rows: ensure Qty is entered (> 0)');
                const firstInvalid = document.querySelector('.is-invalid'); if (firstInvalid) try{ firstInvalid.focus(); }catch(e){}
                return;
            }

            const fd = new FormData();
            // If creating (no po_id) and po_number is blank, generate a unique PO number so server validation passes.
            const poNumberEl = document.getElementById('po_number');
            let poIdEl = document.getElementById('po_id');
            if (poNumberEl && (!poNumberEl.value || poNumberEl.value.trim() === '') && (!poIdEl || !poIdEl.value)) {
                // Generate a compact timestamp-based PO number and prefix it with company-specific prefix if available
                const now = new Date();
                const pad = (n) => n.toString().padStart(2,'0');
                let genCore = now.getFullYear() + pad(now.getMonth()+1) + pad(now.getDate()) + pad(now.getHours()) + pad(now.getMinutes()) + pad(now.getSeconds());
                let prefix = '';
                // Try to read prefix from the selected company option (data-po-prefix) or from a global mapping
                try {
                    const companyEl = document.querySelector('[name="company_id"]');
                    if (companyEl && companyEl.options && companyEl.selectedIndex >= 0) {
                        const opt = companyEl.options[companyEl.selectedIndex];
                        if (opt && opt.dataset && opt.dataset.poPrefix) prefix = opt.dataset.poPrefix;
                    }
                    // fallback: window.COMPANY_PO_PREFIXES mapping (id -> prefix)
                    if (!prefix && window.COMPANY_PO_PREFIXES && companyEl && companyEl.value) {
                        prefix = window.COMPANY_PO_PREFIXES[companyEl.value] || '';
                    }
                } catch (e) { prefix = ''; }
                const gen = (prefix ? (prefix) : 'PO-') + genCore;
                poNumberEl.value = gen;
                showValidationPopup('PO Number auto-generated: ' + gen, 'info');
            }
            // If po_date is empty, default to today (also support legacy 'date' id)
            const poDateEl = document.getElementById('po_date') || document.getElementById('date');
            if (poDateEl && (!poDateEl.value || poDateEl.value.trim() === '')) {
                const d = new Date();
                const pad = (n) => n.toString().padStart(2,'0');
                poDateEl.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
            }
            // append simple inputs (except amount we'll calculate from grandTotal)
            // append po_date if present, and also include legacy 'date' field for compatibility
            const simpleFields = ['po_number','po_date','date','supplier_id','project_id','status','notes','company_id','site_engineer_id'];
            simpleFields.forEach(k => { const el = document.getElementById(k) || document.querySelector('[name="'+k+'"]'); if (el) fd.append(k, el.value); });

            // include grand total as amount (ensure numeric string, remove commas)
            const grandTotalEl = document.getElementById('grandTotal');
            const grandValue = grandTotalEl ? (grandTotalEl.textContent || '0').replace(/,/g,'').trim() : '0';
            fd.append('amount', grandValue);

            // serialize items (rows have: S.No, Category, ProductName, Qty input, Rate input, Amount span)
            const rows = document.querySelectorAll('#quotationTable tbody tr');
            rows.forEach((tr, idx) => {
                const desc = tr.children[2]?.textContent?.trim() || '';
                const uomId = tr.querySelector('.uom-id-input')?.value || '';
                const productId = tr.querySelector('.product-id-input')?.value || '';
                const qty = (tr.querySelector('.qty-input')?.value || 0).toString();
                const rateRaw = (tr.querySelector('.rate-input')?.value || '').toString();
                const amtRaw = (tr.querySelector('.amount-display')?.textContent || '').replace(/,/g,'').trim();
                fd.append(`items[${idx}][description]`, desc);
                fd.append(`items[${idx}][uom_id]`, uomId);
                fd.append(`items[${idx}][product_id]`, productId);
                fd.append(`items[${idx}][quantity]`, qty);
                // append unit_price and total only when user entered a value (keep fields absent otherwise)
                if (rateRaw !== '') fd.append(`items[${idx}][unit_price]`, rateRaw);
                if (amtRaw !== '') fd.append(`items[${idx}][total]`, amtRaw);
            });

            // clear errors
            ['po_number','supplier_id'].forEach(f=>{ const el=document.getElementById(f); if(el) el.classList.remove('is-invalid'); const err=document.getElementById(f+'-error'); if(err) err.textContent=''; });

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // decide url and method (create vs edit)
            let url = '/purchaseOrders';
            const headers = {'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json'};
            if (poIdEl && poIdEl.value) {
                url += '/' + poIdEl.value;
                headers['X-HTTP-Method-Override'] = 'PUT';
            }

            // DEBUG: dump FormData to console to confirm site_engineer_id and other fields
            try {
                const dump = {};
                for (const pair of fd.entries()) { dump[pair[0]] = pair[1]; }
                console.debug('PO submit payload', dump);
            } catch (e) { console.debug('PO submit FormData inspect failed', e); }

            fetch(url, {method:'POST', headers: headers, body: fd})
                .then(r=>r.json()).then(data=>{
                    if (data.status === 'success') {
                        const body = document.getElementById('successModalBody'); if (body) body.textContent = data.message || 'Purchase Order saved successfully';
                        const successModal = new bootstrap.Modal(document.getElementById('successModal')); if (successModal) successModal.show();
                        setTimeout(()=>{ window.location.href = '/purchaseOrders'; },1200);
                    } else if (data.errors) {
                        for (const f in data.errors) {
                            const el = document.getElementById(f);
                            if (el) el.classList.add('is-invalid');
                            const err = document.getElementById(f+'-error'); if (err) err.textContent = data.errors[f][0];
                        }
                    }
                });
        });
    }

    // PO edit form submit
    const poEditForm = document.getElementById('po-edit-form');
    if (poEditForm) {
        poEditForm.addEventListener('submit', function(e){
            e.preventDefault();
            const id = document.getElementById('po_id').value;
            const fd = new FormData();
            ['po_number','date','supplier_id','project_id','amount','status','notes'].forEach(k=>{ const el=document.getElementById(k); if(el) fd.append(k, el.value); });
            fetch('/purchaseOrders/'+id, {method:'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'X-HTTP-Method-Override':'PUT','Accept':'application/json'}, body: fd})
                .then(r=>r.json()).then(data=>{
                    if (data.status === 'success') {
                        const body = document.getElementById('successModalBody'); if (body) body.textContent = data.message || 'Purchase Order updated successfully';
                        const successModal = new bootstrap.Modal(document.getElementById('successModal')); if (successModal) successModal.show();
                        setTimeout(()=>{ window.location.href = '/purchaseOrders'; },1200);
                    } else if (data.errors) {
                        for (const f in data.errors) {
                            const el = document.getElementById(f);
                            if (el) el.classList.add('is-invalid');
                            const err = document.getElementById(f+'-error'); if (err) err.textContent = data.errors[f][0];
                        }
                    }
                });
        });
    }

});
