@extends('layouts.main')

@section('container')
<div class="page-heading">
    <div class="page-title">
        <h3>Stock Replenish</h3>
        <p class="text-subtitle text-muted">List Data Stock Replenish.</p>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="mb-4 d-flex gap-2">
                <div class="flex-grow-1">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="text" name="kode_item" id="kode_item" class="form-control" style="width: 100%;" />
                </div>

                <div class="flex-grow-1">
                    <label for="nama_toko" class="form-label">Nama Toko</label>
                    <input type="text" name="nama_toko" id="nama_toko" class="form-control" style="width: 100%;" />
                </div>
            </div>

            <div class="mb-5 d-flex gap-2">
                <button id="exportExcel" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                </button>
                <button id="searchButton" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i> Cari
                </button>
            </div>

            <div id="loadingContainer" class="text-center" style="display: none;">
                <span id="loadingSpinner" class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>
            </div>

            <div class="table-responsive" style="max-width: 100%; overflow-x: auto;">
                <table class="table" id="report_replenish">
                    <thead>
                        <tr>
                            <th>Kode Item</th>
                            <th>Nama Item</th>
                            <th>Toko</th>
                            <th>P3M</th>
                            <th>RPP3M</th>
                            <th>Omset Toko</th>
                            <th>Kontribusi</th>
                            <th>Skala Prioritas</th>
                            <th>Stock Gudang</th>
                            <th>Kebutuhan Replenishment</th>
                            <th>Replenished</th>
                            <th>Edit Replenished</th>
                            <th>Tanggal Edit Replenish</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Edit Replenished Modal -->
<div class="modal fade" id="editReplenishModal" tabindex="-1" aria-labelledby="editReplenishModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReplenishModalLabel">Edit Replenished</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="hidden_toko" name="hidden_toko">
                <input type="hidden" id="hidden_item" name="hidden_item">
                <div class="mb-3">
                    <label for="editReplenish" class="form-label">Replenished</label>
                    <input type="number" class="form-control" id="editReplenish" name="editReplenish" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveReplenishButton">Save changes</button>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable


        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const table = $('#report_replenish').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/reports/reports_replenish',
            columns: [
                { data: 'kode_item' },
                { data: 'name_item' },
                { data: 'toko' },
                { data: 'p3m' },
                { data: 'rpp3m' },
                { data: 'omset_toko' },
                { data: 'kontribusi' },
                { data: 'skala_prioritas' },
                { data: 'stock_gudang' },
                { data: 'kebutuhan_replenishment' },
                { data: 'replenished' },
                { data: 'edit_replenish'},
                { data: 'tanggalreplenish'},
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-primary edit-btn" data-item="${row.kode_item}" data-toko="${row.toko}" data-replenish = "${row.edit_replenish}" ><i class="bi bi-pencil-square"></i></button>`;
                    }
                }
                
            ]
        });

        $('#exportExcel').on('click', function() {
            const kodeItem = $('#kode_item').val();
            const namaToko = $('#nama_toko').val();
            const url = `/reports/exportExcelReportReplenish?kode_item=${encodeURIComponent(kodeItem)}&nama_toko=${encodeURIComponent(namaToko)}`;
            window.location.href = url;
        });

        // Search event handler
        $('#searchButton').on('click', function() {
            const kodeItem = $('#kode_item').val();
            const namaToko = $('#nama_toko').val();

            table.columns(0).search(kodeItem); // Search Kode Item (Column 0)
            table.columns(2).search(namaToko); // Search Nama Toko (Column 2)
            table.draw(); // Redraw the table with updated filters
        });


        $(document).on('click', '.edit-btn', function() {

            const item = $(this).data('item');
            const toko = $(this).data('toko');
            const edit_replenish = $(this).data('replenish')

            $('#hidden_toko').val(toko);
            $('#hidden_item').val(item);
            $("#editReplenish").val(edit_replenish);
            
            $('#editReplenishModal').modal('show');
            
        });


        $('#saveReplenishButton').on('click', function() {
            const toko = $("#hidden_toko").val();
            const item = $("#hidden_item").val();
            const edit_replenish = $("#editReplenish").val();

            $.ajax({
                url: `/uploadreplenishedit/updateReplenish`,
                method: 'POST',
                data: { toko: toko, item : item , edit_replenish : edit_replenish},
                success: function(response) {
                    
                    $('#editReplenishModal').modal('hide');
                    if (response.message) {
                        // Success response handling with SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload() // Reload the table
                            }
                        });
                    } else {
                        // Failure response handling with SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update Replenished value.',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Catching errors if the AJAX call itself fails
                    
                    $('#editReplenishModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while saving the data. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });


    });
</script>
@endpush
