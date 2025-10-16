<?php
require_once __DIR__ . '/../config/db.php';
$group = $_GET['group'] ?? '';
if ($group === '') {
  echo '<div class="container py-4"><div class="alert alert-warning">Missing group id.</div></div>';
  return;
}
?>
<div class="container py-4">
  <h1 class="h5">Audit Details</h1>
  <div id="audit-details" class="table-responsive mt-3"></div>
</div>
<script>
(async () => {
  const host = document.getElementById('audit-details');
  const res = await fetch('api/result-details.php?group=' + encodeURIComponent('<?=$group?>'), { cache: 'no-store' });
  const data = await res.json();
  const rows = data.rows || [];
  const photos = data.photos || {};
  const html = ['<table class="table table-sm"><thead><tr><th>#</th><th>Line</th><th>Process</th><th>Item</th><th>Spec</th><th>Result</th><th>Photos</th><th>When</th></tr></thead><tbody>'];
  rows.forEach((r, i) => {
    const imgs = (photos[r.id] || []).map(p => `<a href="${p.path}" target="_blank">Photo</a>`).join(' ');
    const badge = r.result === 'OK' ? 'bg-success' : 'bg-danger';
    html.push(`<tr>
      <td>${i+1}</td>
      <td>${r.line}</td>
      <td>${r.process}</td>
      <td>${r.item}</td>
      <td>${r.spec ?? ''}</td>
      <td><span class="badge ${badge}">${r.result}</span></td>
      <td>${imgs}</td>
      <td>${r.created_at}</td>
    </tr>`);
  });
  html.push('</tbody></table>');
  host.innerHTML = html.join('');
})();
</script>
