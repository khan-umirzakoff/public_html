@extends("admin.main")

@section('content')
<div class="container-fluid ai-section-body">

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <svg class="ai-icon" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <svg class="ai-icon" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <svg class="ai-icon" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <strong>Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Statistics & Actions Column --}}
        <div class="col-lg-4">
            {{-- Embedding Statistics --}}
            <div class="ai-card">
                <div class="ai-card-header">
                    <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <h5 class="ai-card-title">Embedding Statistics</h5>
                </div>
                <div class="ai-card-body">
                    @forelse($stats as $table => $stat)
                        @php
                            $total = $stat['total'];
                            $embedded = $stat['with_embedding'];
                            $percentage = $total > 0 ? round(($embedded / $total) * 100) : 0;
                        @endphp
                        <div class="ai-progress-bar-container">
                            <div class="ai-progress-bar-label">
                                <span>{{ ucfirst(str_replace('_', ' ', $table)) }}</span>
                                <span class="text-muted">{{ $embedded }} / {{ $total }}</span>
                            </div>
                            <div class="ai-progress-bar-bg">
                                <div class="ai-progress-bar-fill" style="width: {{ $percentage }}%;"></div>
                            </div>
                        </div>
                    @empty
                         <p class="text-muted text-center">No statistics to display yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Bulk Actions --}}
            <div class="ai-card">
                <div class="ai-card-header">
                     <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <h5 class="ai-card-title">Bulk Actions</h5>
                </div>
                <div class="ai-card-body">
                    <form method="POST" action="{{ route('ai-knowledge.generate-all-embeddings') }}" onsubmit="showBatchLoading()" class="mb-3">
                        @csrf
                        <button type="submit" class="ai-btn ai-btn-primary w-100">
                           <svg class="ai-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12C18,14.22 16.79,16.16 15,17.2V19.5C17.89,18.15 20,15.32 20,12C20,7.58 16.42,4 12,4V6M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10Z"/></svg>
                            Generate All Embeddings
                        </button>
                        <small class="ai-form-text text-center d-block">Generates 10 per category. Press again to continue.</small>
                    </form>
                    <form method="POST" action="{{ route('ai-knowledge.seed-default') }}" onsubmit="return confirm('Are you sure you want to load default data? This may overwrite existing default entries.')">
                        @csrf
                        <button type="submit" class="ai-btn ai-btn-secondary w-100">
                             <svg class="ai-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            Seed Default Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Knowledge Form Column --}}
        <div class="col-lg-8">
            <div class="ai-card">
                <div class="ai-card-header">
                    <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <h5 class="ai-card-title">Add New Knowledge</h5>
                </div>
                <form method="POST" action="{{ route('ai-knowledge.store') }}">
                    @csrf
                    <div class="ai-card-body">
                        <div class="row">
                            <div class="col-md-6 ai-form-group">
                                <label for="key" class="ai-form-label">Key (Short Name)</label>
                                <input type="text" name="key" id="key" class="ai-form-control" placeholder="e.g., Company Phone" value="{{ old('key') }}" required>
                            </div>
                            <div class="col-md-6 ai-form-group">
                                <label for="category" class="ai-form-label">Category</label>
                                <input list="category-list" name="category" id="category" class="ai-form-control" placeholder="Select or type new" value="{{ old('category') }}" required>
                                <datalist id="category-list">
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="ai-form-group">
                            <label for="value" class="ai-form-label">Value (Full Information)</label>
                            <textarea name="value" id="value" class="ai-form-control" rows="4" placeholder="The exact text the AI will provide." required>{{ old('value') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-8 ai-form-group">
                                <label for="description" class="ai-form-label">Description (Optional)</label>
                                <input type="text" name="description" id="description" class="ai-form-control" placeholder="Internal note for this data" value="{{ old('description') }}">
                            </div>
                            <div class="col-md-4 ai-form-group">
                                <label for="priority" class="ai-form-label">Priority (0-5)</label>
                                <input type="number" name="priority" id="priority" class="ai-form-control" min="0" max="5" value="{{ old('priority', 0) }}">
                            </div>
                        </div>
                    </div>
                    <div class="ai-card-footer">
                        <button type="submit" class="ai-btn ai-btn-primary">
                            <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Save Knowledge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Knowledge Table --}}
    <div class="ai-card">
        <div class="ai-card-header">
            <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 00.707.293H17a2 2 0 012 2v2"></path></svg>
            <h5 class="ai-card-title">Knowledge Base</h5>
        </div>
        <div class="ai-card-body p-0">
            <div class="table-responsive">
                <table class="table ai-table mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($knowledge as $item)
                        <tr>
                            <td><span class="badge badge-info">{{ $item->category }}</span></td>
                            <td><strong>{{ $item->key }}</strong><br><small class="text-muted">{{ $item->description }}</small></td>
                            <td style="white-space: pre-wrap; font-size: 14px;">{{ $item->value }}</td>
                            <td><span class="ai-badge ai-badge-warning">{{ $item->priority }}</span></td>
                            <td>
                                @if($item->embedding && $item->embedding != '[]' && $item->embedding != '')
                                    <span class="ai-badge ai-badge-success">Embedded</span>
                                @else
                                    <span class="ai-badge ai-badge-danger">Not Embedded</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex">
                                    <button class="btn btn-sm btn-light mr-2" data-toggle="modal" data-target="#editModal{{ $item->id }}" title="Edit">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg>
                                    </button>
                                    @if(!$item->embedding || $item->embedding == '[]' || $item->embedding == '')
                                    <form method="POST" action="{{ route('ai-knowledge.generate-embedding', $item->id) }}" style="display:inline" onsubmit="return confirm('Generate embedding for this item?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning mr-2" title="Generate Embedding">
                                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12C18,14.22 16.79,16.16 15,17.2V19.5C17.89,18.15 20,15.32 20,12C20,7.58 16.42,4 12,4V6M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10Z"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('ai-knowledge.delete', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editModal{{ $item->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('ai-knowledge.update', $item->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Knowledge</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 form-group"><label for="key{{ $item->id }}">Key</label><input type="text" name="key" id="key{{ $item->id }}" class="form-control" value="{{ $item->key }}" required></div>
                                                <div class="col-md-6 form-group"><label for="category_edit{{ $item->id }}">Category</label><input list="category-list" name="category" id="category_edit{{ $item->id }}" class="form-control" value="{{ $item->category }}" required></div>
                                            </div>
                                            <div class="form-group"><label for="value{{ $item->id }}">Value</label><textarea name="value" id="value{{ $item->id }}" class="form-control" rows="4" required>{{ $item->value }}</textarea></div>
                                            <div class="row">
                                                <div class="col-md-8 form-group"><label for="description{{ $item->id }}">Description</label><input type="text" name="description" id="description{{ $item->id }}" class="form-control" value="{{ $item->description }}"></div>
                                                <div class="col-md-4 form-group"><label for="priority{{ $item->id }}">Priority</label><input type="number" name="priority" id="priority{{ $item->id }}" class="form-control" min="0" max="5" value="{{ $item->priority ?? 0 }}"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-5">
                                <svg class="mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 48px; height: 48px; color: #ccc;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-muted">No knowledge base items yet. Add one using the form above.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($knowledge->hasPages())
        <div class="ai-card-footer">
             {{ $knowledge->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Loading Overlay for Batch Embedding --}}
<div id="batch-loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div class="text-center text-white">
        <div class="spinner-border text-warning" style="width: 4rem; height: 4rem;" role="status"></div>
        <h3 class="mt-4">Generating Embeddings...</h3>
        <p class="text-muted">Please wait. This may take a few minutes.</p>
        <p class="text-warning"><small>Do not close or refresh this page!</small></p>
    </div>
</div>

<script>
function showBatchLoading() {
    document.getElementById('batch-loading-overlay').style.display = 'flex';
    return true; // Allow form submission
}
</script>
@endsection