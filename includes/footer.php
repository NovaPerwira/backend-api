<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Auto-hide flash messages
        setTimeout(() => {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.style.opacity = '0';
                flashMessage.style.transform = 'translateX(100%)';
                setTimeout(() => flashMessage.remove(), 300);
            }
        }, 5000);

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>