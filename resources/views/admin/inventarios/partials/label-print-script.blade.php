@once
    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('[data-label-print-url]');

                if (!button) {
                    return;
                }

                event.preventDefault();

                const url = button.dataset.labelPrintUrl;

                if (!url) {
                    return;
                }

                document.getElementById('labelPrintFrame')?.remove();

                const frame = document.createElement('iframe');
                frame.id = 'labelPrintFrame';
                frame.src = url;
                frame.style.position = 'fixed';
                frame.style.right = '0';
                frame.style.bottom = '0';
                frame.style.width = '0';
                frame.style.height = '0';
                frame.style.border = '0';
                frame.style.opacity = '0';
                frame.setAttribute('aria-hidden', 'true');

                document.body.appendChild(frame);
            });
        </script>
    @endpush
@endonce
