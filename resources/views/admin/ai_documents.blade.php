@extends('admin.layouts.app')

@section('title', 'AI Documents Management')

@section('content')
<div class=\"container-fluid\">
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h3 class=\"card-title\">AI Knowledge Base - Documents</h3>
                    <button class=\"btn btn-primary btn-sm float-right\" data-toggle=\"modal\" data-target=\"#uploadModal\">
                        <i class=\"fas fa-plus\"></i> New Document
                    </button>
                </div>
                <div class=\"card-body\">
                    <div class=\"table-responsive\">
                        <table class=\"table table-bordered table-striped\" id=\"documentsTable\">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>File</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class=\"modal fade\" id=\"uploadModal\" tabindex=\"-1\" role=\"dialog\">
    <div class=\"modal-dialog modal-lg\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\">Upload New Document</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">
                    <span>&times;</span>
                </button>
            </div>
            <form id=\"uploadForm\" enctype=\"multipart/form-data\">
                <div class=\"modal-body\">
                    <div class=\"form-group\">
                        <label for=\"title\">Title *</label>
                        <input type=\"text\" class=\"form-control\" id=\"title\" name=\"title\" required>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"category\">Category</label>
                        <select class=\"form-control\" id=\"category\" name=\"category\">
                            <option value=\"\">Select Category</option>
                            <option value=\"guide\">Guide</option>
                            <option value=\"policy\">Policy</option>
                            <option value=\"faq\">FAQ</option>
                            <option value=\"training\">Training Material</option>
                            <option value=\"procedure\">Procedure</option>
                            <option value=\"other\">Other</option>
                        </select>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"description\">Description</label>
                        <textarea class=\"form-control\" id=\"description\" name=\"description\" rows=\"3\"></textarea>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"file\">File *</label>
                        <input type=\"file\" class=\"form-control-file\" id=\"file\" name=\"file\" 
                               accept=\".txt,.md,.pdf,.doc,.docx\" required>
                        <small class=\"form-text text-muted\">
                            Supported formats: TXT, MD, PDF, DOC, DOCX (max 10MB)
                        </small>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"content\">Text Content (optional)</label>
                        <textarea class=\"form-control\" id=\"content\" name=\"content\" rows=\"5\" 
                                placeholder=\"If file text cannot be read, enter it manually here...\"></textarea>
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Cancel</button>
                    <button type=\"submit\" class=\"btn btn-primary\">
                        <i class=\"fas fa-upload\"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Load documents on page load
    loadDocuments();
    
    // Upload form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route(\"ai-documents.upload\") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#uploadModal').modal('hide');
                    $('#uploadForm')[0].reset();
                    loadDocuments();
                    toastr.success('Document uploaded successfully!');
                } else {
                    toastr.error(response.error || 'An error occurred');
                }
            },
            error: function(xhr) {
                var error = 'Upload error';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    error = xhr.responseJSON.error;
                }
                toastr.error(error);
            }
        });
    });
    
    function loadDocuments() {
        $.get('{{ route(\"ai-documents.list\") }}', function(response) {
            if (response.success) {
                var tbody = $('#documentsTable tbody');
                tbody.empty();
                
                response.documents.forEach(function(doc) {
                    var row = `
                        <tr>
                            <td>${doc.id}</td>
                            <td><strong>${doc.title}</strong></td>
                            <td>${doc.category || '-'}</td>
                            <td>${doc.description ? doc.description.substring(0, 100) + '...' : '-'}</td>
                            <td>
                                <small>${doc.file_name}</small><br>
                                <span class=\"badge badge-info\">${formatFileSize(doc.file_size)}</span>
                            </td>
                            <td>${new Date(doc.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class=\"btn btn-danger btn-sm\" onclick=\"deleteDocument(${doc.id})\">
                                    <i class=\"fas fa-trash\"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        });
    }
    
    window.deleteDocument = function(id) {
        if (confirm("Are you sure you want to delete this document?")) {
            $.ajax({
                url: `/admin/ai-documents/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadDocuments();
                        toastr.success("Document deleted");
                    } else {
                        toastr.error(response.error || 'An error occurred');
                    }
                },
                error: function() {
                    toastr.error("Error during deletion");
                }
            });
        }
    };
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection