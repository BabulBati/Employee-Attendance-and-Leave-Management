<?php
include "../includes/header.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth();
$role = $_SESSION['user']['role'];
$userId = $_SESSION['user']['id'];
?>

<h2>Attendance</h2>

<?php if ($role === 'employee'): ?>
    <form id="markPresentForm">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <button type="submit" class="btn">Mark Present</button>
    </form>

    <div class="table-card">
        <table width="50%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="employeeTable"></tbody>
        </table>
    </div>
    <div id="empPagination" style="margin-top:10px;"></div>
<?php endif; ?>

<?php if ($role === 'admin'): ?>
    <label>
        Select Date:
        <input type="date" id="filterDate" value="<?= date('Y-m-d') ?>">
    </label>
    <button class="btn" onclick="loadAdmin()">Filter</button>
    <div class="table-card">
        <table width="90%">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="adminTable"></tbody>
        </table>
    </div>
    <div id="adminPagination" style="margin-top:10px;"></div>
<?php endif; ?>

<!-- TOAST CONTAINER -->
<div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>

<script>
    const csrf = '<?= csrf() ?>';

    /* ======================
       TOAST FUNCTION
    ====================== */
    function showToast(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.background = type === 'success' ? '#4caf50' : '#f44336';
        toast.style.color = 'white';
        toast.style.padding = '10px 20px';
        toast.style.marginTop = '10px';
        toast.style.borderRadius = '5px';
        toast.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => { toast.style.opacity = '1'; }, 50);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300) }, duration);
    }

    /* ======================
       EMPLOYEE
    ====================== */
    let empPage = 1;
    function loadEmployee(page = 1) {
        empPage = page;
        fetch(`../ajax/attendance-ajax.php?page=${page}`)
            .then(r => r.json())
            .then(res => {
                if (!res.data.length) {
                    employeeTable.innerHTML = `<tr><td colspan="2" style="text-align:center;">No attendance found</td></tr>`;
                    empPagination.innerHTML = '';
                    return;
                }
                employeeTable.innerHTML = res.data.map(r => `<tr><td>${r.attendance_date}</td><td>${r.status}</td></tr>`).join('');

                // Pagination
                let totalPages = Math.ceil(res.total / res.limit);
                let paginationHtml = '';
                if (page > 1) paginationHtml += `<button onclick="loadEmployee(${page - 1})">Previous</button> `;
                if (page < totalPages) paginationHtml += `<button onclick="loadEmployee(${page + 1})">Next</button>`;
                empPagination.innerHTML = paginationHtml;
            });
    }

    <?php if ($role === 'employee'): ?>
        document.getElementById('markPresentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            fetch('../ajax/attendance-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ mark_present: 1, csrf: csrf })
            })
                .then(r => r.json())
                .then(res => {
                    showToast(res.message, res.success ? 'success' : 'error');
                    loadEmployee(empPage);
                });
        });
    <?php endif; ?>

    /* ======================
       ADMIN
    ====================== */
    let adminPage = 1;
    function loadAdmin(page = 1) {
        adminPage = page;
        const date = document.getElementById('filterDate').value;
        fetch(`../ajax/attendance-ajax.php?page=${page}&date=${date}`)
            .then(r => r.json())
            .then(res => {
                if (!res.data.length) {
                    adminTable.innerHTML = `<tr><td colspan="4" style="text-align:center;">No attendance found</td></tr>`;
                    adminPagination.innerHTML = '';
                    return;
                }
                adminTable.innerHTML = res.data.map(r => `
            <tr>
                <td>${r.name}</td>
                <td><input type="date" id="d_${r.id}" value="${r.attendance_date}"></td>
                <td>
                    <select id="s_${r.id}">
                        <option ${r.status == 'Present' ? 'selected' : ''}>Present</option>
                        <option ${r.status == 'Absent' ? 'selected' : ''}>Absent</option>
                    </select>
                </td>
                <td><button onclick="save(${r.user_id},'${r.attendance_date}',${r.id})">Save</button></td>
            </tr>
        `).join('');

                // Pagination
                let totalPages = Math.ceil(res.total / res.limit);
                let paginationHtml = '';
                if (page > 1) paginationHtml += `<button onclick="loadAdmin(${page - 1})">Previous</button> `;
                if (page < totalPages) paginationHtml += `<button onclick="loadAdmin(${page + 1})">Next</button>`;
                adminPagination.innerHTML = paginationHtml;
            });
    }

    function save(userId, oldDate, rowId) {
        fetch('../ajax/attendance-ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                update_attendance: 1,
                user_id: userId,
                status: document.getElementById('s_' + rowId).value,
                date: document.getElementById('d_' + rowId).value,
                original_date: oldDate,
                csrf: csrf
            })
        })
            .then(r => r.json())
            .then(res => {
                showToast(res.message, res.success ? 'success' : 'error');
                loadAdmin(adminPage);
            });
    }

/* INITIAL LOAD */
<?php if ($role === 'employee'): ?>loadEmployee(); <?php endif; ?>
<?php if ($role === 'admin'): ?>loadAdmin(); <?php endif; ?>
</script>

<?php include "../includes/footer.php"; ?>