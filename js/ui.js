(function () {
    const themeStorageKey = 'morganizer-theme';
    const guideStorageKey = 'morganizer-guide-hidden';
    const root = document.documentElement;

    function getInitialTheme() {
        const saved = localStorage.getItem(themeStorageKey);
        if (saved === 'light' || saved === 'dark') return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        localStorage.setItem(themeStorageKey, theme);

        const isDark = theme === 'dark';
        document.querySelectorAll('.theme-toggle').forEach((btn) => {
            btn.setAttribute('aria-pressed', String(isDark));
            btn.setAttribute('aria-label', isDark ? 'Ativar modo claro' : 'Ativar modo escuro');
            btn.textContent = isDark ? '☀ Modo claro' : '🌙 Modo escuro';
        });
    }

    function applyGuideVisibility(isHidden) {
        const guideCard = document.getElementById('quickGuideCard');
        const showGuideBtn = document.getElementById('showGuideBtn');
        if (!guideCard || !showGuideBtn) return;

        guideCard.hidden = isHidden;
        showGuideBtn.hidden = !isHidden;
        showGuideBtn.setAttribute('aria-expanded', String(!isHidden));
        localStorage.setItem(guideStorageKey, isHidden ? 'true' : 'false');
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getInitialTheme());

        document.querySelectorAll('.theme-toggle').forEach((btn) => {
            btn.addEventListener('click', function () {
                const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        });

        const hideGuideBtn = document.getElementById('hideGuideBtn');
        const showGuideBtn = document.getElementById('showGuideBtn');
        if (hideGuideBtn && showGuideBtn) {
            applyGuideVisibility(localStorage.getItem(guideStorageKey) === 'true');

            hideGuideBtn.addEventListener('click', function () {
                applyGuideVisibility(true);
                showGuideBtn.focus();
            });

            showGuideBtn.addEventListener('click', function () {
                applyGuideVisibility(false);
                hideGuideBtn.focus();
            });
        }
    });
})();
