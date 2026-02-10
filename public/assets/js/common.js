// Common frontend helpers
// Sets jQuery AJAX X-CSRF-TOKEN header if jQuery is present
(function(){
    function ready(fn){
        if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function(){
        var meta = document.querySelector('meta[name="csrf-token"]');
        var token = meta ? meta.getAttribute('content') : null;
        if (window.jQuery && token) {
            try {
                window.jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
            } catch(e) {
                console.error('ajaxSetup failed', e);
            }
        }
    });

    // small helper to safely get CSRF token for fetch calls
    window.getCsrfToken = function(){
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    };

    // Global success/error modal alert used by backend pages
    window.showAlert = function(message, type = 'success'){
        try {
            var body = document.getElementById('successModalBody');
            if (body) {
                body.textContent = message;
                var modalEl = document.getElementById('successModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
                setTimeout(function(){ var m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); }, 1400);
                return;
            }
        } catch(e){ /* fallthrough to alert */ }
        alert(message);
    };
})();
