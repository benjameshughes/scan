document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach((flashMessage) => {
        flashMessage.addEventListener('click', () => {
            flashMessage.remove();
        });
    });

    flashMessages.forEach((flashMessage) => {
        flashMessage.classList.add('hidden');
        setTimeout(() => {
            flashMessage.classList.remove('hidden');
        }, 500);
    });
});