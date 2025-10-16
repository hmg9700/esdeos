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

        <!-- Section -->
        <div class="col-auto">
            <label class="form-label">Section</label>
            <select class="form-select" name="section">
                <option value="%%">All Section</option>
                <option value="1">Pagi (1)</option>
                <option value="2">Siang (2)</option>
                <option value="0">Other (0)</option>
            </select>
        </div>

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

    async function load() {
        const fd = new FormData(form);
        const qs = new URLSearchParams(fd).toString();
        const res = await fetch('api/rfid-summary.php?' + qs, { cache: 'no-store' });
        const data = await res.json();
        const rows = data.rows || [];

        if (!rows.length) {
            host.innerHTML = `<div class="alert alert-warning">No data found.</div>`;
            return;
        }

        // Build Table Header
        let html = ['<table class="table table-bordered table-sm table-striped">'];
        html.push('<thead><tr><th>Machine</th><th>Name</th><th>NIK</th>');

        // Days 1 to 31
        for (let day = 1; day <= 31; day++) {
            html.push(`<th>${day}</th>`);
        }

        html.push('</tr></thead><tbody>');

        // Build Table Rows
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
        host.innerHTML = html.join('');
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        load();
    });

    // Initial load
    load();
})();
</script>
