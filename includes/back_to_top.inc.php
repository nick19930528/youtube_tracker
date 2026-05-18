<style>
.scroll-to-top {
    position: fixed;
    right: max(16px, env(safe-area-inset-right, 0px));
    bottom: max(16px, env(safe-area-inset-bottom, 0px));
    z-index: 9999;
    width: 48px;
    height: 48px;
    padding: 0;
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: 50%;
    background: #0077cc;
    color: #fff;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    -webkit-tap-highlight-color: transparent;
}
.scroll-to-top:hover {
    background: #005fa3;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.22);
}
.scroll-to-top:active {
    transform: translateY(0);
}
.scroll-to-top:focus-visible {
    outline: 2px solid #38bdf8;
    outline-offset: 2px;
}
body[data-theme="dark"] .scroll-to-top {
    border-color: rgba(255, 255, 255, 0.12);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
}
@media (max-width: 720px) {
    .scroll-to-top {
        width: 44px;
        height: 44px;
        font-size: 20px;
        right: max(12px, env(safe-area-inset-right, 0px));
        bottom: max(12px, env(safe-area-inset-bottom, 0px));
    }
}
</style>
<button type="button" class="scroll-to-top" id="scrollToTopBtn" aria-label="回到頂部" title="回到頂部">↑</button>
<script>
(function () {
    var btn = document.getElementById('scrollToTopBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var root = document.scrollingElement || document.documentElement;
        if (typeof root.scrollTo === 'function') {
            root.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            root.scrollTop = 0;
        }
        window.scrollTo(0, 0);
    });
})();
</script>
