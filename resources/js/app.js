import Alpine from 'alpinejs';

const THEME_KEY = 'gab-theme';

/**
 * Tema yönetimi. İlk tema, <head> içindeki inline script tarafından
 * (FOUC olmadan) uygulanır; burada yalnızca geçiş ve kayıt yapılır.
 */
Alpine.store('theme', {
    current: document.documentElement.dataset.theme || 'dark',

    get isDark() {
        return this.current === 'dark';
    },

    toggle() {
        this.set(this.isDark ? 'light' : 'dark');
    },

    set(theme) {
        const root = document.documentElement;
        root.classList.add('theme-transition');
        root.dataset.theme = theme;
        this.current = theme;
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {
            // Gizli pencere vb.: tercih yalnızca bu oturumda geçerli olur.
        }
        window.setTimeout(() => root.classList.remove('theme-transition'), 320);
    },
});

/**
 * Header: hero üzerindeyken şeffaf; aşağı kaydırırken gizlenir, yukarıda görünür.
 */
Alpine.data('siteHeader', () => ({
    scrolled: false,
    hidden: false,
    menuOpen: false,
    lastY: 0,

    init() {
        // scrollY okuması bir sonraki kareye: Alpine DOM'u değiştirirken okumak
        // zorunlu yeniden yerleşime (forced reflow, ~140 ms TBT) yol açıyordu.
        requestAnimationFrame(() => this.onScroll());
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
        window.matchMedia('(min-width: 64rem)').addEventListener('change', (e) => {
            if (e.matches) this.closeMenu();
        });
    },

    onScroll() {
        const y = window.scrollY;
        this.scrolled = y > 24;
        this.hidden = !this.menuOpen && y > 320 && y > this.lastY;
        this.lastY = y;
    },

    openMenu() {
        this.menuOpen = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => this.$refs.menuFirst?.focus());
    },

    closeMenu() {
        if (!this.menuOpen) return;
        this.menuOpen = false;
        document.body.style.overflow = '';
        this.$refs.menuButton?.focus();
    },
}));

/**
 * Portföy filtresi: işlem tipine göre kanonik adres, il → ilçe süzme.
 */
Alpine.data('propertyFilter', (config) => ({
    ...config,

    init() {
        this.ilce = this.$refs.district.value;
        this.renderDistricts();
    },

    /** İlçe listesini seçilen ile göre yeniden kurar (JS kapalıyken gruplu liste kalır). */
    renderDistricts() {
        const select = this.$refs.district;
        const list = this.districts[this.il] || [];
        select.replaceChildren(new Option('Tümü', ''));
        list.forEach((d) => select.add(new Option(d, d, false, d === this.ilce)));
        select.disabled = !this.il;
    },

    /** /portfoy?islem=satilik yerine kanonik /satilik adresine gönderir; boş alanları URL'den çıkarır. */
    submit(event) {
        const form = event.target;
        if (this.actions[this.islem]) {
            form.action = this.actions[this.islem];
        }
        form.querySelectorAll('input, select').forEach((el) => {
            if (el.name === 'islem' || (!el.value && el.type !== 'checkbox')) {
                el.disabled = true;
            }
        });

        // Hiç filtre yoksa "/kiralik?" yerine temiz adrese git
        if (![...new FormData(form).keys()].length) {
            event.preventDefault();
            window.location.assign(form.action);

            return;
        }
        // Geri gelindiğinde (bfcache) alanlar kullanılabilir olsun
        window.setTimeout(() => form.querySelectorAll('[disabled]').forEach((el) => (el.disabled = el === this.$refs.district && !this.il)), 0);
    },
}));

/**
 * Çerez bildirimi: "Anladım" denince bu metin sürümü için bir daha gösterilmez.
 * Depolama erişilemiyorsa (gizli pencere vb.) bant her sayfada görünür; işlev bozulmaz.
 */
const COOKIE_NOTICE_KEY = 'gab-cookie-notice';

Alpine.data('cookieNotice', (version) => ({
    open: false,

    init() {
        try {
            this.open = localStorage.getItem(COOKIE_NOTICE_KEY) !== version;
        } catch (e) {
            this.open = true;
        }
    },

    dismiss() {
        this.open = false;
        try {
            localStorage.setItem(COOKIE_NOTICE_KEY, version);
        } catch (e) {
            // Tercih yalnızca bu sayfa için geçerli olur.
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();

/**
 * Scroll reveal: [data-reveal] öğeleri görünür alana girince bir kez açılır.
 */
const revealTargets = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window && revealTargets.length) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );
    revealTargets.forEach((el) => observer.observe(el));
} else {
    revealTargets.forEach((el) => el.classList.add('is-visible'));
}
