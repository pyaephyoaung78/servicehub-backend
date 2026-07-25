const sidebar = document.querySelector('#admin-sidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    sidebarOverlay?.classList.add('hidden');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
};

const openSidebar = () => {
    sidebar?.classList.remove('-translate-x-full');
    sidebarOverlay?.classList.remove('hidden');
    sidebarToggle?.setAttribute('aria-expanded', 'true');
};

sidebarToggle?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});
