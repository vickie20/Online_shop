document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.querySelector('button[type="button"]');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
