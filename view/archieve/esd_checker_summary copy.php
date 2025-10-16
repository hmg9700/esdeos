<?php
require_once __DIR__ . '/../config/db.php';

// Get current month and year
$current_month = date('m');
$current_year = date('Y');

// Fetch distinct machine locations (lines)
$line_sql = "SELECT DISTINCT machine_location FROM log WHERE machine_location <> '' ORDER BY machine_location ASC";
$line_stmt = $pdo->query($line_sql);
$lines = $line_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="container py-4">
    <h1 class="h4 mb-3">ESD Checker Summary</h1>

    <!-- Filter Form -->
    <form class="row g-2 mb-3" id="summary-filter">
        <!-- Month -->
        <div class="col-auto">
            <label class="form-label">Month</label>
            <select class="form-select" name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($m == $current_month ? 'selected' : '') ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Year -->
        <div class="col-auto">
            <label class="form-label">Year</label>
            <select class="form-select" name="year">
                <?php
                $year_start = 2023;
                $year_end = date('Y') + 1;
                for ($y = $year_start; $y <= $year_end; $y++): ?>
                    <option value="<?= $y ?>" <?= ($y == $current_year ? 'selected' : '') ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Line -->
        <div class="col-auto">
            <label class="form-label">Line / Machine</label>
            <select class="form-select" name="line">
                <option value="%%">All Line</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?= htmlspecialchars($line) ?>">
                        <?= htmlspecialchars($line) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Section 
        <div class="col-auto">
            <label class="form-label">Section</label>
            <select class="form-select" name="section">
                <option value="%%">All Section</option>
                <option value="1">Pagi (1)</option>
                <option value="2">Siang (2)</option>
                <option value="0">Other (0)</option>
            </select>
        </div>
        -->

        <!-- Submit -->
        <div class="col-auto align-self-end">
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        </div>
    </form>

    <!-- Data Table Host -->
    <div id="summary-host" class="table-responsive"></div>
</div>

<!-- JavaScript to Fetch & Render Pivot Data -->
<script>
(async () => {
    const host = document.getElementById('summary-host');
    const form = document.getElementById('summary-filter');

    function renderTable(title, rows) {
        if (!rows.length) {
            return `<div class="alert alert-warning">${title}: No data found.</div>`;
        }

        let html = [`<h5 class="mt-4">${title}</h5>`];
        html.push('<div class="table-scroll-wrapper"><table class="table table-bordered table-sm table-striped freeze-table">');
        html.push('<thead><tr><th>Machine Location</th><th>Name</th><th>NIK</th>');

        for (let day = 1; day <= 31; day++) {
            html.push(`<th>${day}</th>`);
        }

        html.push('</tr></thead><tbody>');

        for (const r of rows) {
            html.push(`<tr>
                <td>${r.machine_location ?? ''}</td>
                <td>${r.name ?? ''}</td>
                <td>${r.nik ?? ''}</td>`);

            for (let day = 1; day <= 31; day++) {
                const value = r[day.toString()] ?? '-';
                let cellClass = '';
                if (value === 'OK') cellClass = 'bg-success text-white';
                else if (value === 'NG') cellClass = 'bg-danger text-white';

                html.push(`<td class="${cellClass}" style="text-align:center;">${value}</td>`);
            }

            html.push('</tr>');
        }

        html.push('</tbody></table>');
        return html.join('');
    }

    async function load() {
        const fd = new FormData(form);
        const qs = new URLSearchParams(fd).toString();
        const res = await fetch('api/rfid-summary.php?' + qs, { cache: 'no-store' });
        const data = await res.json();

        const section1 = data.section1 || [];
        const section2 = data.section2 || [];

        const pagiTable = renderTable('Section 1 - Pagi', section1);
        const siangTable = renderTable('Section 2 - Siang', section2);

        host.innerHTML = pagiTable + siangTable;
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        load();
    });

    load(); // Initial load
})();
</script>
