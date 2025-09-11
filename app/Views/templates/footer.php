    <!-- Bootstrap JS bundle (includes Popper) needed by sb-admin script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>
    <script>
        // Delegated handler: show confirm dialog for elements (forms or links) that opt-in via data-confirm
        (function(){
            function handleAction(e) {
                var el = e.target;
                // Walk up to nearest element that has data-confirm
                while (el && el !== document) {
                    if (el.dataset && el.dataset.confirm) break;
                    el = el.parentNode;
                }
                if (!el || el === document) return;
                var message = el.dataset.confirm || 'Are you sure?';

                // If it's a link, confirm before navigating
                if (el.tagName === 'A') {
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                    return;
                }

                // If inside a form/button structure, let the form submission be intercepted
                // Find nearest form
                var form = el.tagName === 'FORM' ? el : el.closest('form');
                if (form) {
                    if (!confirm(message)) {
                        e.preventDefault();
                        return;
                    }
                    // allow submission
                }
            }

            // Attach to document for delegation (clicks and submits)
            document.addEventListener('click', function(e){
                try { handleAction(e); } catch(err) { /* noop */ }
            }, true);

            // For forms that are submitted programmatically or via Enter key, handle submit event as well
            document.addEventListener('submit', function(e){
                try {
                    var form = e.target;
                    if (!form) return;
                    // If the form itself has data-confirm, prompt
                    if (form.dataset && form.dataset.confirm) {
                        if (!confirm(form.dataset.confirm)) {
                            e.preventDefault();
                        }
                    }
                } catch(err) { /* noop */ }
            }, true);
        })();
    </script>
</body>
</html> 