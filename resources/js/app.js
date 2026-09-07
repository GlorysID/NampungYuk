import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            if (this.dark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        }
    });
});

window.Alpine = Alpine;
Alpine.start();

// Dynamic mouse-tracking spotlight border for Vercel / Supabase cards
document.addEventListener('mousemove', (e) => {
    const cards = document.querySelectorAll('.spotlight-card');
    if (!cards.length) return;
    for (let i = 0; i < cards.length; i++) {
        const card = cards[i];
        const rect = card.getBoundingClientRect();
        // Only compute if card is in visible viewport
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        }
    }
});

