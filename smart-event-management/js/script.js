// Form Validation
function validateForm(formId) {
    let isValid = true;
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
 
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    // Email validation
    const emailInput = form.querySelector('input[type="email"]');
    if (emailInput && emailInput.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            isValid = false;
        }
    }
    
    return isValid;
}

// Delete Confirmation
function confirmDelete(itemName, itemId, module) {
    if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        window.location.href = `${module}.php?delete=${itemId}`;
    }
    return false;
}

// Animated Progress Bars
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.getAttribute('aria-valuenow') || bar.style.width;
        if (width) {
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        }
    });
}

function updateSidebarToggleButton() {
    var sidebar = document.querySelector('.sidebar');
    var toggleBtn = document.getElementById('sidebarToggle');
    if (!sidebar || !toggleBtn) return;
    toggleBtn.classList.toggle('sidebar-open', sidebar.classList.contains('active'));
}

// Toggle Sidebar for Mobile
function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('active');
    updateSidebarToggleButton();
}

// Live Search
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let textMatch = false;
        const cells = rows[i].getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
                const textValue = cells[j].textContent || cells[j].innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    textMatch = true;
                    break;
                }
            }
        }
        rows[i].style.display = textMatch ? '' : 'none';
    }
}

// Dashboard Statistics Animation
function animateNumbers() {
    const numberElements = document.querySelectorAll('.stat-number');
    numberElements.forEach(el => {
        const target = parseInt(el.getAttribute('data-target'));
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                el.innerText = target;
                clearInterval(timer);
            } else {
                el.innerText = Math.floor(current);
            }
        }, 20);
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    animateProgressBars();
    animateNumbers();
    
    // Add event listener for delete buttons
    const deleteBtns = document.querySelectorAll('.delete-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.getAttribute('data-name');
            const id = this.getAttribute('data-id');
            const module = this.getAttribute('data-module');
            confirmDelete(name, id, module);
        });
    });
    
    // Real-time validation on inputs
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });

    if (typeof updateSidebarToggleButton === 'function') {
        updateSidebarToggleButton();
    }
});


// Progress Update
function updateProgress(progressValue, taskId) {
    fetch('update_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&progress=${progressValue}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Delete Budget Confirmation
function confirmDeleteBudget(budgetId, eventName) {
    if (confirm(`Are you sure you want to delete budget for "${eventName}"? This action cannot be undone.`)) {
        window.location.href = `budget.php?delete_budget=${budgetId}`;
    }
    return false;
}