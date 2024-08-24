document.addEventListener('DOMContentLoaded', function() {
    var dismissButton = document.querySelector('.nap-dismiss-button');
    var notification = document.querySelector('.nap-notification');

    if (dismissButton) {
        dismissButton.addEventListener('click', function() {
            notification.classList.add('dismissed');
        });
    }
});
