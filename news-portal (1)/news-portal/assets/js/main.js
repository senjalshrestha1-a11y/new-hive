// Small progressive enhancement: nothing on this site requires JS to work.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert-success').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 5000);
    });
});
