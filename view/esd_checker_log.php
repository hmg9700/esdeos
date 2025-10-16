<?php
require_once __DIR__ . '/../config/db.php';

$today_date = date('Y-m-d');

// --- 1. Fetch Unique Lines from the 'log' table ---
// Added WHERE to exclude empty machine_location values
$line_sql = "SELECT DISTINCT machine_location FROM log where machine_location <> '' ORDER BY machine_location ASC";
$line_stmt = $pdo->query($line_sql);
$lines = $line_stmt->fetchAll(PDO::FETCH_COLUMN);
// ----------------------------------------------------
?>
<div class="container py-4">
    <h1 class="h4 mb-3">ESD Checker Log</h1>
    <form class="row g-2 mb-3" id="summary-filter">
        <div class="col-auto">
            <label class="form-label">From</label>
            <input type="date" class="form-control" name="from" value="<?php echo $today_date; ?>" />
        </div>

        <div class="col-auto">
            <label class="form-label">Line / Machine</label>
            <select class="form-select" name="line">
                <option value="%%">All Line</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?php echo htmlspecialchars($line); ?>">
                        <?php echo htmlspecialchars($line); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <label class="form-label">Section</label>
            <select class="form-select" name="section">
                <option value="%%">All Section</option>
                <option value="1">Pagi (1)</option>
                <option value="2">Siang (2)</option>
                <option value="0">Other (0)</option>
            </select>
        </div>

        <div class="col-auto">
            <label class="form-label">Result</label>
            <select class="form-select" name="result">
                <option value="%%">All Result</option>
                <option value="OK">OK</option>
                <option value="NG">NG</option>
            </select>
        </div>

        <div class="col-auto align-self-end">
            <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
        </div>
    </form>
    <div id="summary-host" class="table-responsive"></div>
</div>
<script>
(async () => {
    const host = document.getElementById('summary-host');
    const form = document.getElementById('summary-filter');

    async function load() {
        const fd = new FormData(form);
        const qs = new URLSearchParams(fd).toString();
        const res = await fetch('api/rfid-log.php?' + qs, { cache: 'no-store' });
        const data = await res.json();
        const rows = data.rows || [];
        const html = ['<table class="table table-sm"><thead><tr><th style="text-align:center;">Location</th><th style="text-align:center;">System</th><th style="text-align:center;">Name</th><th style="text-align:center;">Nik</th><th style="text-align:center;">Resistance (Spec:1MΩ~35MΩ)</th><th style="text-align:center;">Result</th><th style="text-align:center;">Section</th><th style="text-align:center;">Timestamp</th></tr></thead><tbody>'];

        for (const r of rows) {
            
            // --- MODIFICATION 1: Conditional Result Badge ---
            let resultHtml = '';
            if (r.result === 'OK') {
                resultHtml = `<span class="badge bg-success">OK</span>`;
            } else if (r.result === 'NG') {
                resultHtml = `<span class="badge bg-danger">NG</span>`;
            } else {
                resultHtml = r.result ?? '';
            }
            // ----------------------------------------------------

            // --- MODIFICATION 2: Section Value Mapping ---
            let sectionText = '';
            switch (r.section) {
                case 1:
                case '1':
                    sectionText = 'Pagi';
                    break;
                case 2:
                case '2':
                    sectionText = 'Siang';
                    break;
                case 0:
                case '0':
                    sectionText = '-';
                    break;
                default:
                    sectionText = r.section ?? ''; // Use original value if not 0, 1, or 2
            }
            // ---------------------------------------------
            
            html.push(`<tr>
               
                <td style="text-align:center;">${r.machine_location ?? ''}</td>
                <td style="text-align:center;">${r.system_type ?? ''}</td>
                <td style="text-align:center;">${r.name ?? ''}</td>
                <td style="text-align:center;">${r.nik ?? ''}</td>
                <td style="text-align:center;">${r.resistance ? `${r.resistance} MΩ` : '-'}</td>
                <td style="text-align:center;">${resultHtml}</td> 
                <td style="text-align:center;">${sectionText}</td> 
                <td style="text-align:center;">${r.timestamp ?? ''}</td>
            </tr>`);
        }
        html.push('</tbody></table>');
        host.innerHTML = html.join('');
    }

    // This ensures the initial load happens with today's date and "All" filters
    form.addEventListener('submit', (e) => { e.preventDefault(); load(); });
    load();
})();
</script>