@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Banks</h1>
            <a href="#" onclick="openBankModal(); return false;" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Bank
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banks as $bank)
                                <tr>
                                    <td>{{ $bank->id }}</td>
                                    <td>
                                        @if($bank->logo)
                                            <img src="{{ asset('storage/' . $bank->logo) }}" alt="{{ $bank->name }}" height="20">
                                        @endif
                                        {{ $bank->name }}
                                    </td>
                                    <td>{{ $bank->short_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $bank->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($bank->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" onclick="openBankModal('{{ $bank->uuid }}')" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No banks found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Modal -->
    <div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bankModalLabel">Manage Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bankForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="uuid" name="uuid">

                        <div class="mb-3">
                            <label for="name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="short_name" class="form-label">Short Name</label>
                            <input type="text" class="form-control" id="short_name" name="short_name">
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo">
                            <small class="text-muted">Leave empty to keep current logo</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBankModal(uuid = null) {
            event.preventDefault(); // Prevent default link behavior
            let url = uuid ? "{{ url('/banks/edit') }}/" + uuid : "{{ url('/banks/edit') }}";

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('uuid').value = data.bank?.uuid || '';
                    document.getElementById('name').value = data.bank?.name || '';
                    document.getElementById('short_name').value = data.bank?.short_name || '';
                    document.getElementById('status').value = data.bank?.status || 'active';
                    document.getElementById('bankForm').action = url;

                    let bankModal = new bootstrap.Modal(document.getElementById('bankModal'));
                    bankModal.show();
                });
        }

        document.getElementById('bankForm').addEventListener('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let url = this.action;

            fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
@endsection