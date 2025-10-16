<?php
include("../config.php");
include("header.php");
include("sidebar.php");
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            ESD / EOS Log Dashboard
            <small>Monitor Absence and ESD Checking</small>
        </h1>
    </section>

    <section class="content">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Filter Log</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="filterDate">Date</label>
                        <input type="date" id="filterDate" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="filterLocation">Machine Location</label>
                        <select id="filterLocation" class="form-control select2">
                            <option value="">-- All Locations --</option>
                            <?php
                            $sql = "SELECT DISTINCT machine_location FROM log ORDER BY machine_location ASC";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['machine_location']) . '">' . htmlspecialchars($row['machine_location']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-primary" id="btnFilter">
                            <i class="fa fa-search"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="row" id="summaryBox" style="display:none;">
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">OK</span>
                        <span class="info-box-number" id="okCount">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fa fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">NG</span>
                        <span class="info-box-number" id="ngCount">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <table id="logTable" class="table table-bordered table-striped table-sm" style="width:100%;">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>DateTime</th>
                            <th>Name</th>
                            <th>NIK</th>
                            <th>Resistance</th>
                            <th>Result</th>
                            <th>Day</th>
                            <th>Keterangan</th>
                            <th>Machine Location</th>
                            <th>System Type</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
    loadData();

    $('#btnFilter').click(function() {
        $('#logTable').DataTable().destroy();
        loadData();
    });

    function loadData() {
        let date = $('#filterDate').val();
        let location = $('#filterLocation').val();

        $('#logTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '../rfidlog-list.php',
                type: 'POST',
                data: {
                    day: date,
                    machine_location: location
                },
                dataSrc: function(json) {
                    if (json.summary) {
                        $('#summaryBox').show();
                        $('#okCount').text(json.summary.ok);
                        $('#ngCount').text(json.summary.ng);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: null },
                { data: 'timestamp' },
                { data: 'name' },
                { data: 'nik' },
                { data: 'resistance' },
                { data: 'result' },
                { data: 'day' },
                { data: 'ket' },
                { data: 'machine_location' },
                { data: 'system_type' }
            ],
            order: [[1, 'desc']],
            responsive: true,
            autoWidth: false
        }).on('order.dt search.dt', function() {
            let table = $('#logTable').DataTable();
            table.column(0, { search:'applied', order:'applied' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    }
});
</script>

<?php include("footer.php"); ?>
