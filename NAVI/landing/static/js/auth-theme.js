(function () {
    const preference = localStorage.getItem('navi-theme-preference') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldUseDark = preference === 'dark' || (preference === 'system' && prefersDark);

    if (shouldUseDark) {
        document.documentElement.classList.add('theme-dark');
        document.body && document.body.classList.add('theme-dark');
    }
})();
