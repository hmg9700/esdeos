<?php
require_once __DIR__ . '/../config/db.php';
$today_date = date('Y-m-d');
$lastmonth_date = date('Y-m-d', strtotime('-1 month'));
?>
<div class="container py-4">
  <h1 class="h4 mb-3">Audit Summary</h1>
  <form class="row g-2 mb-3" id="summary-filter">
    <div class="col-auto">
      <label class="form-label">From</label>
      <input type="date" class="form-control" name="from" value="<?php echo $lastmonth_date; ?>"/>
    </div>
    <div class="col-auto">
      <label class="form-label">To</label>
      <input type="date" class="form-control" name="to" value="<?php echo $today_date; ?>"/>
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-primary btn-sm" type="submit">Filter</button>
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
    const res = await fetch('api/result-summary.php?' + qs, { cache: 'no-store' });
    const data = await res.json();
    const rows = data.rows || [];
    const html = ['<table class="table table-sm"><thead><tr><th>Day</th><th>Line</th><th>Title</th><th>Action</th></tr></thead><tbody>'];
    for (const r of rows) {
      html.push(`<tr>
        <td>${r.day}</td>
        <td>${r.line ?? ''}</td>
        <td>${r.title ?? ''}</td>
        <td><a class="btn btn-outline-primary btn-sm" href="?page=audit-view&group=${encodeURIComponent(r.group_id)}">View</a></td>
      </tr>`);
    }
    html.push('</tbody></table>');
    host.innerHTML = html.join('');
  }

  form.addEventListener('submit', (e) => { e.preventDefault(); load(); });
  load();
})();
</script>
