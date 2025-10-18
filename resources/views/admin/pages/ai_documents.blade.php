@extends("admin.main")

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
#ai-doc-drop-zone {
    border: 2px dashed var(--ai-border-color);
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #fdfdff;
}
#ai-doc-drop-zone.dragover {
    border-color: var(--ai-primary-color);
    background-color: var(--ai-secondary-color);
}
#ai-doc-drop-zone .ai-icon {
    width: 48px;
    height: 48px;
    color: var(--ai-primary-color);
    margin-bottom: 15px;
}
</style>
@endsection

@section('content')
<div class="container-fluid ai-section-body">

    {{-- AJAX Messages --}}
    <div id="ajax-success-message" class="alert alert-success alert-dismissible fade show" style="display: none;"></div>
    <div id="ajax-error-message" class="alert alert-danger alert-dismissible fade show" style="display: none;"></div>

    {{-- Upload Document Card --}}
    <div class="ai-card">
        <div class="ai-card-header">
            <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-4-4V6a2 2 0 012-2h10a2 2 0 012 2v6a4 4 0 01-4 4H7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 16v1.5a2.5 2.5 0 005 0V16"></path></svg>
            <h5 class="ai-card-title">Upload AI Document</h5>
        </div>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="ai-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="ai-form-group">
                            <label class="ai-form-label">Document Title</label>
                            <input type="text" name="title" class="ai-form-control" placeholder="e.g., Company Policy" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ai-form-group">
                            <label class="ai-form-label">Category</label>
                            <input list="category-list-docs" name="category" class="ai-form-control" placeholder="e.g., HR, Marketing">
                            <datalist id="category-list-docs">
                                @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">
                                    @endforeach
                                @endif
                            </datalist>
                        </div>
                    </div>
                </div>
                <div class="ai-form-group">
                    <label class="ai-form-label">Description</label>
                    <textarea name="description" class="ai-form-control" rows="2" placeholder="A brief summary of the document's content"></textarea>
                </div>
                <div class="ai-form-group">
                    <label class="ai-form-label">File</label>
                    <div id="ai-doc-drop-zone">
                        <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 12h10M7 16H4a1 1 0 01-1-1V5a1 1 0 011-1h3m10 12h3a1 1 0 001-1V5a1 1 0 00-1-1h-3m-9 4l3 3m0 0l3-3m-3 3V4"></path></svg>
                        <h5>Drag & Drop File Here or Click to Select</h5>
                        <p class="text-muted">Supports: txt, md, pdf, doc, docx, epub. Max 100MB.</p>
                        <input type="file" name="file" id="file-input" accept=".txt,.md,.pdf,.doc,.docx,.epub" required style="display: none;">
                    </div>
                    <div id="file-info" class="mt-2" style="display: none;">
                        <small class="text-success">Selected File: <span id="file-name"></span> (<span id="file-size"></span>)</small>
                    </div>
                </div>
            </div>
            <div class="ai-card-footer">
                <button type="submit" class="ai-btn ai-btn-primary">
                    <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Upload & Process
                </button>
            </div>
        </form>
    </div>

    {{-- Documents Table Card --}}
    <div class="ai-card">
        <div class="ai-card-header">
             <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h5 class="ai-card-title">Uploaded Documents</h5>
        </div>
        <div class="ai-card-body p-0">
             <div class="table-responsive">
                <table class="table ai-table mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>{{ $doc->title }}</td>
                            <td><span class="badge badge-info">{{ $doc->category }}</span></td>
                            <td>{{ $doc->file_name }}</td>
                            <td>{{ number_format($doc->file_size / 1024, 1) }} KB</td>
                            <td>
                                @if($doc->embedding)
                                    <span class="ai-badge ai-badge-success">Embedded</span>
                                @else
                                    <span class="ai-badge ai-badge-danger">Not Embedded</span>
                                @endif
                            </td>
                            <td>{{ date('d.m.Y H:i', strtotime($doc->created_at)) }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteDocument({{ $doc->id }})" title="Delete">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-5">
                                <svg class="mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 48px; height: 48px; color: #ccc;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                                <p class="text-muted">No documents have been uploaded yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if($documents->hasPages())
        <div class="ai-card-footer">
             {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Loading Modal --}}
<div id="loading-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div class="text-center text-white">
        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"></div>
        <h4 class="mt-4">Generating Embeddings...</h4>
        <p class="text-muted">Please wait. This may take a few minutes.</p>
        <p class="text-warning font-weight-bold">⚠️ Do not close or refresh this page!</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dropZone = document.getElementById('ai-doc-drop-zone');
    const fileInput = document.getElementById('file-input');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const uploadForm = document.getElementById('uploadForm');
    let selectedFile = null;

    function showLoading() { document.getElementById('loading-modal').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loading-modal').style.display = 'none'; }

    function showMessage(type, message) {
        const el = document.getElementById(`ajax-${type}-message`);
        el.innerHTML = message + '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Drag and Drop handlers
    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFile(files[0]);
        }
    });

    // Form submission handler
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!uploadForm.title.value.trim()) return showMessage('error', 'Please enter a document title.');
        if (!selectedFile) return showMessage('error', 'Please select a file to upload.');

        showLoading();
        const formData = new FormData(uploadForm);

        fetch('{{ route("ai-documents.upload") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            hideLoading();
            if (ok) {
                showMessage('success', data.message || 'File uploaded successfully! Reloading page...');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showMessage('error', `Error: ${data.error || data.message || 'Unknown server error'}`);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Upload error:', error);
            showMessage('error', 'A network error occurred. Please check your connection or file size.');
        });
    });

    function handleFile(file) {
        if (file) {
            selectedFile = file;
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
            fileInfo.style.display = 'block';
        } else {
            selectedFile = null;
            fileInfo.style.display = 'none';
        }
    }

    // Make delete function global
    window.deleteDocument = function(id) {
        confirmAction({
            title: 'Delete Document',
            message: 'Are you sure you want to delete this document? This action cannot be undone.',
            confirmText: 'Yes, Delete',
            confirmClass: 'btn-danger',
            onConfirm: function() {
                fetch(`/admin/ai-documents/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (ok) {
                        showMessage('success', data.message || 'Document deleted. Reloading...');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showMessage('error', `Error: ${data.error || 'Could not delete document.'}`);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showMessage('error', 'A network error occurred during deletion.');
                });
            }
        });
    };
});
</script>
@endsection