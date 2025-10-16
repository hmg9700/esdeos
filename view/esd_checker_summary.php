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

        <!-- Submit -->
        <div class="col-auto align-self-end">
            <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
        </div>
    </form>

    <!-- Data Table Host -->
    <div id="summary-host" class="table-responsive"></div>
</div>

<!-- Sticky Column CSS -->
<style>
.table-scroll-wrapper {
    overflow-x: auto;
    max-width: 100%;
    position: relative;
}

.freeze-table {
    /*  border-collapse: separate; */
    border-spacing: 0;
    table-layout: auto;
}

.freeze-table th,
.freeze-table td {
    white-space: nowrap;
    min-width: 30px;
    text-align: center;
}

/* Sticky Columns */
.freeze-table th.sticky-col,
.freeze-table td.sticky-col {
    position: sticky;
    background: white;
    z-index: 2;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

/* Column 1 */
.freeze-table th.sticky-col-1,
.freeze-table td.sticky-col-1 {
    left: 0;
    z-index: 2;
}

/* Column 2 */
.freeze-table th.sticky-col-2,
.freeze-table td.sticky-col-2 {
    left: 74px;
    z-index: 2;
}

/* Column 3 */
.freeze-table th.sticky-col-3,
.freeze-table td.sticky-col-3 {
    left: 148px;
    z-index: 3;
}
</style>

<!-- JavaScript to Fetch & Render Pivot Data -->
<script>
(async () => {
    const host = document.getElementById('summary-host');
    const form = document.getElementById('summary-filter');

    function renderTable(title, rows) {
        if (!rows.length) {
            return `<div class="alert alert-warning">${title}: No data found.</div>`;
        }

        let html = [`<h6 class="mt-4">${title}</h6>`];
        html.push('<div class="table-scroll-wrapper">');
        //html.push('<table class="table table-bordered table-sm table-striped freeze-table">');
        html.push('<table class="table  table-sm freeze-table">');
        html.push('<thead><tr>');
        html.push('<th class="sticky-col sticky-col-1">Location</th>');
        html.push('<th class="sticky-col sticky-col-2">Name</th>');
        html.push('<th ">NIK</th>');

        for (let day = 1; day <= 31; day++) {
            html.push(`<th>${day}</th>`);
        }

        html.push('</tr></thead><tbody>');

        for (const r of rows) {
            html.push('<tr>');
            html.push(`<td class="sticky-col sticky-col-1">${r.machine_location ?? ''}</td>`);
            html.push(`<td class="sticky-col sticky-col-2">${r.name ?? ''}</td>`);
            html.push(`<td>${r.nik ?? ''}</td>`);

            for (let day = 1; day <= 31; day++) {
                const value = r[day.toString()] ?? '-';
                let cellClass = '';
                if (value === 'OK') cellClass = 'bg-success text-white';
                else if (value === 'NG') cellClass = 'bg-danger text-white';

                html.push(`<td class="${cellClass}" style="text-align:center;">${value}</td>`);
            }

            html.push('</tr>');
        }

        html.push('</tbody></table></div>');
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
