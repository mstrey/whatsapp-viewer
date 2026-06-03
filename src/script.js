document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const messages = document.querySelectorAll('.message');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            messages.forEach(msg => {
                const text = msg.textContent.toLowerCase();
                if (text.includes(term)) {
                    msg.style.display = '';
                } else {
                    msg.style.display = 'none';
                }
            });
        });
    }
});