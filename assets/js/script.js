/*  ==========
   Attendance Employee View
==========  */
let currentPage = 1;

function loadAttendance(page = 1) {
    currentPage = page;
    fetch(`../ajax/employee_attendance.php?page=${page}`)
        .then(res => res.json())
        .then(res => {
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            res.data.forEach(row => {
                tbody.innerHTML += `<tr><td>${row.attendance_date}</td><td>${row.status}</td></tr>`;
            });

            // Pagination
            const totalPages = Math.ceil(res.total / res.limit);
            const paginationDiv = document.getElementById("pagination");
            paginationDiv.innerHTML = "";
            if (totalPages > 1) {
                if (currentPage > 1) {
                    const prev = document.createElement("button");
                    prev.textContent = "Prev";
                    prev.addEventListener("click", () => loadAttendance(currentPage - 1));
                    paginationDiv.appendChild(prev);
                }
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.disabled = i === currentPage;
                    btn.addEventListener("click", () => loadAttendance(i));
                    paginationDiv.appendChild(btn);
                }
                if (currentPage < totalPages) {
                    const next = document.createElement("button");
                    next.textContent = "Next";
                    next.addEventListener("click", () => loadAttendance(currentPage + 1));
                    paginationDiv.appendChild(next);
                }
            }
        });
}

loadAttendance();

/*  ==========
   Attendance Admin View
==========  */

let adminCurrentPage = 1;

function loadAdminAttendance(page = 1) {
    adminCurrentPage = page;
    const date = document.getElementById("adminFilterDate").value; // get selected date
    fetch(`../ajax/admin_attendance.php?page=${page}&date=${date}`)
        .then(res => res.json())
        .then(res => {
            const tbody = document.querySelector("#adminAttendanceTable tbody");
            tbody.innerHTML = "";

            if (res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No records found for this date.</td></tr>';
                document.getElementById("adminPagination").innerHTML = '';
                return;
            }

            // render table rows (same as before)
            res.data.forEach(row => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${row.name}</td>
                    <td><input type="date" value="${row.attendance_date}" class="admDate"></td>
                    <td>
                        <select class="admStatus">
                            <option value="Present" ${row.status == 'Present' ? 'selected' : ''}>Present</option>
                            <option value="Absent" ${row.status == 'Absent' ? 'selected' : ''}>Absent</option>
                        </select>
                    </td>
                    <td>
                        <button class="admUpdateBtn">Update</button>
                        <input type="hidden" class="admUserId" value="${row.user_id}">
                        <input type="hidden" class="admOrigDate" value="${row.attendance_date}">
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // pagination (same as before)
            const totalPages = Math.ceil(res.total / res.limit);
            const paginationDiv = document.getElementById("adminPagination");
            paginationDiv.innerHTML = "";
            if (totalPages > 1) {
                if (adminCurrentPage > 1) {
                    const prev = document.createElement("button");
                    prev.textContent = "Prev";
                    prev.addEventListener("click", () => loadAdminAttendance(adminCurrentPage - 1));
                    paginationDiv.appendChild(prev);
                }
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.disabled = i === adminCurrentPage;
                    btn.addEventListener("click", () => loadAdminAttendance(i));
                    paginationDiv.appendChild(btn);
                }
                if (adminCurrentPage < totalPages) {
                    const next = document.createElement("button");
                    next.textContent = "Next";
                    next.addEventListener("click", () => loadAdminAttendance(adminCurrentPage + 1));
                    paginationDiv.appendChild(next);
                }
            }

            // update buttons (same as before)
            document.querySelectorAll(".admUpdateBtn").forEach(btn => {
                btn.addEventListener("click", () => {
                    const tr = btn.closest("tr");
                    const userId = tr.querySelector(".admUserId").value;
                    const origDate = tr.querySelector(".admOrigDate").value;
                    const date = tr.querySelector(".admDate").value;
                    const status = tr.querySelector(".admStatus").value;
                    const csrf = '<?= csrf() ?>';

                    fetch("", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `update_attendance=1&csrf=${csrf}&user_id=${userId}&original_date=${origDate}&date=${date}&status=${status}`
                    })
                        .then(res => res.json())
                        .then(res => {
                            alert(res.message);
                            loadAdminAttendance(adminCurrentPage);
                        });
                });
            });

        });
}

loadAdminAttendance(1);