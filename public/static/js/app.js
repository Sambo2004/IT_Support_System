// IT Support System Client Script - Mobile & Responsive Interactions

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('wrapper');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // 1. Sidebar Toggle / Open
    if (sidebarToggle && wrapper) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            wrapper.classList.toggle('toggled');
        });
    }

    // 2. Sidebar Close (Mobile X button)
    if (sidebarClose && wrapper) {
        sidebarClose.addEventListener('click', function (e) {
            e.preventDefault();
            wrapper.classList.remove('toggled');
        });
    }

    // 3. Close sidebar on overlay click
    if (sidebarOverlay && wrapper) {
        sidebarOverlay.addEventListener('click', function () {
            wrapper.classList.remove('toggled');
        });
    }

    // 4. Auto-close sidebar on mobile when navigating links
    const sidebarLinks = document.querySelectorAll('#sidebar .nav-link');
    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992 && wrapper) {
                wrapper.classList.remove('toggled');
            }
        });
    });

    // 5. Auto-dismiss Flash Alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // 6. Double-submit prevention on forms
    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !form.dataset.submitting) {
                form.dataset.submitting = 'true';
                submitBtn.classList.add('disabled');
            }
        });
    });

    // 7. Live Unread Notifications Poller
    const notifBadge = document.getElementById('unreadNotifBadge');
    if (notifBadge) {
        function checkNotifications() {
            const notifUrl = notifBadge.dataset.url;
            if (!notifUrl) return;
            fetch(notifUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.count && data.count > 0) {
                        notifBadge.innerText = data.count;
                        notifBadge.classList.remove('d-none');
                    } else {
                        notifBadge.classList.add('d-none');
                    }
                })
                .catch(() => {});
        }
        checkNotifications();
        setInterval(checkNotifications, 30000);
    }
});
