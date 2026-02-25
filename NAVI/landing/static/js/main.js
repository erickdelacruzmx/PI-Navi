// Efecto de header al hacer scroll
function applyPublicTheme() {
    const preference = localStorage.getItem('navi-theme-preference') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldUseDark = preference === 'dark' || (preference === 'system' && prefersDark);
    document.body.classList.toggle('theme-dark', shouldUseDark);
    document.documentElement.classList.toggle('theme-dark', shouldUseDark);

    const toggleButtonIcon = document.querySelector('#theme-toggle-public i');
    if (toggleButtonIcon) {
        toggleButtonIcon.className = shouldUseDark ? 'fas fa-sun' : 'fas fa-moon';
    }
}

applyPublicTheme();

const publicThemeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
if (publicThemeMediaQuery.addEventListener) {
    publicThemeMediaQuery.addEventListener('change', applyPublicTheme);
}

window.addEventListener('scroll', function() {
    const header = document.querySelector('.site-header');
    if (!header) {
        return;
    }
    if (window.scrollY > 100) {
        header.classList.add('header-scrolled');
    } else {
        header.classList.remove('header-scrolled');
    }
});

// Smooth scroll para enlaces internos
document.querySelectorAll('a[data-scroll]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href || !href.includes('#')) {
            return;
        }
        const targetId = href.split('#')[1];
        const target = document.getElementById(targetId);
        if (target) {
            e.preventDefault();
            const header = document.querySelector('.site-header');
            const headerHeight = header ? header.offsetHeight : 0;
            window.scrollTo({
                top: target.offsetTop - headerHeight,
                behavior: 'smooth'
            });
        }
    });
});

// Resaltar enlace activo en el menú
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section[id]');
    
    window.addEventListener('scroll', function() {
        let current = '';
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            
            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-section') === current) {
                link.classList.add('active');
            }
        });
    });
});

// Toggle menu mobile
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('nav-toggle');
    const panel = document.getElementById('nav-panel');

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', function() {
        const isHidden = panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', String(isHidden));
    });

    panel.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                panel.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    const publicThemeButton = document.getElementById('theme-toggle-public');
    if (publicThemeButton) {
        publicThemeButton.addEventListener('click', function() {
            const isDark = document.body.classList.contains('theme-dark');
            localStorage.setItem('navi-theme-preference', isDark ? 'light' : 'dark');
            applyPublicTheme();
        });
    }
});