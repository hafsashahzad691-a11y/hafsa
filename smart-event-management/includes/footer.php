    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>

    
<button id="scrollToTop" style="position: fixed; bottom: 30px; right: 30px; width: 45px; height: 45px; background: #667eea; color: white; border: none; border-radius: 50%; cursor: pointer; display: none; z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
var scrollBtn = document.getElementById('scrollToTop');
window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollBtn.style.display = 'block';
    } else {
        scrollBtn.style.display = 'none';
    }
});
scrollBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
<div style="text-align: center; padding: 20px; margin-top: 30px; border-top: 1px solid #e2e8f0; color: #6e7683; font-size: 0.8rem;">
                   Smart Event Management System | City University
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.querySelector('.sidebar');
    var toggleBtn = document.getElementById('sidebarToggle');

    if (!sidebar || !toggleBtn) return;

    var backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    function openSidebar() {
        sidebar.classList.add('active');
        backdrop.classList.add('active');
        if (typeof updateSidebarToggleButton === 'function') {
            updateSidebarToggleButton();
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        backdrop.classList.remove('active');
        if (typeof updateSidebarToggleButton === 'function') {
            updateSidebarToggleButton();
        }
    }

    toggleBtn.addEventListener('click', function (event) {
        event.stopPropagation();
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    backdrop.addEventListener('click', closeSidebar);

    document.addEventListener('click', function (event) {
        if (
            sidebar.classList.contains('active') &&
            !sidebar.contains(event.target) &&
            !toggleBtn.contains(event.target)
        ) {
            closeSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});
</script>

</body>
</html>