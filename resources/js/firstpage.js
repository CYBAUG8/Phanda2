// Implement the interactive behavior described in the provided HTML.
// This file is loaded by Vite (development) or the built asset (production).

document.addEventListener('DOMContentLoaded', function () {
    const userBtn = document.getElementById('userLogin');
    const providerBtn = document.getElementById('providerLogin');

    if (userBtn) {
        userBtn.addEventListener('click', function () {
            try {
                this.style.animation = 'pulse 0.8s';
            } catch (e) {}
            setTimeout(() => {
                try { this.style.animation = ''; } catch (e) {}
                // Navigate to the user route that exists in routes/web.php
                window.location.href = '/user';
            }, 800);
        });
    }

    if (providerBtn) {
        providerBtn.addEventListener('click', function () {
            try {
                this.style.animation = 'pulse 0.8s';
            } catch (e) {}
            setTimeout(() => {
                try { this.style.animation = ''; } catch (e) {}
                // Navigate to the provider route that exists in routes/web.php
                window.location.href = '/provider';
            }, 800);
        });
    }

    // Geometric shapes hover brightness handlers (no CSS, so may not be visible)
    const shapes = document.querySelectorAll('.geometric-shape');
    shapes.forEach(shape => {
        shape.addEventListener('mouseenter', function () {
            try { this.style.filter = 'brightness(1.5)'; } catch (e) {}
        });
        shape.addEventListener('mouseleave', function () {
            try { this.style.filter = 'brightness(1)'; } catch (e) {}
        });
    });

    // Parallax effect for geometric background
    window.addEventListener('mousemove', function (e) {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        const s1 = document.querySelector('.shape-1');
        const s2 = document.querySelector('.shape-2');
        const s3 = document.querySelector('.shape-3');
        try { if (s1) s1.style.transform = `translate(${x * 30}px, ${y * 30}px) rotate(${x * 360}deg)`; } catch (e) {}
        try { if (s2) s2.style.transform = `translate(${x * -20}px, ${y * -20}px) rotate(${y * 360}deg)`; } catch (e) {}
        try { if (s3) s3.style.transform = `translate(${x * 15}px, ${y * 15}px) rotate(${x * 180}deg)`; } catch (e) {}
    });

    // Dynamic text effect for logo
    const logo = document.querySelector('.phanda-logo');
    if (logo) {
        const originalText = logo.textContent || 'Phanda';
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        logo.addEventListener('mouseenter', function () {
            let iterations = 0;
            const interval = setInterval(() => {
                this.textContent = this.textContent.split('')
                    .map((letter, index) => {
                        if (index < iterations) {
                            return originalText[index] || '';
                        }
                        return letters[Math.floor(Math.random() * 26)];
                    })
                    .join('');
                if (iterations >= originalText.length) clearInterval(interval);
                iterations += 1 / 3;
            }, 40);
        });
    }
});

export default {};
