@extends("admin.main")

@section('head')
<style>
    .provider-check-icon {
        visibility: hidden;
        width: 20px;
        height: 20px;
        color: var(--ai-success-color);
    }
    .ai-provider-dropdown-item.selected .provider-check-icon {
        visibility: visible;
    }
    /* This helps align the text and icon correctly */
    .ai-provider-dropdown-item > .provider-name-wrapper {
        display: flex;
        align-items: center;
    }
</style>
@endsection

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

    <form method="POST" action="{{ route('ai-settings.update') }}" id="settings-form">
        @csrf

        {{-- AI Provider Selection Card --}}
        <div class="ai-card">
            <div class="ai-card-header">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                <h5 class="ai-card-title">AI Provider Configuration</h5>
            </div>
            <div class="ai-card-body">
                <p class="text-muted mb-4">Select your AI provider and configure its API credentials. The selected provider will be used for all AI-powered features.</p>

                <div class="ai-form-group">
                    <label class="ai-form-label" for="ai_provider_dropdown">AI Provider</label>
                    <div class="ai-provider-dropdown">
                        <input type="hidden" name="ai_provider" id="ai_provider_input" value="{{ $currentProvider }}">
                        <button type="button" class="ai-provider-dropdown-btn" id="ai_provider_dropdown_btn">
                            <span id="selected_provider_text">
                                {{-- This will be populated by JS --}}
                            </span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="ai-provider-dropdown-content" id="ai_provider_dropdown_content">
                            <div class="ai-provider-dropdown-item" data-provider="openai">
                                <div class="provider-name-wrapper">
                                    <svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22.2819 9.8211C23.0545 10.4439 23.5164 11.3745 23.5164 12.3618C23.5164 13.3491 23.0545 14.2797 22.2819 14.9025L13.7454 21.4764C12.9728 22.0992 11.9636 22.4412 10.9273 22.4412C9.89091 22.4412 8.88182 22.0992 8.10909 21.4764L2.71818 17.1491C1.94545 16.5264 1.48364 15.5957 1.48364 14.6084C1.48364 13.6211 1.94545 12.6905 2.71818 12.0677L11.2545 5.49384C12.0273 4.87107 13.0364 4.52911 14.0727 4.52911C15.1091 4.52911 16.1182 4.87107 16.8909 5.49384L22.2819 9.8211ZM14.0727 2.52911C15.6545 2.52911 17.1636 3.15188 18.2819 4.27107L20.2819 6.27107C19.5636 5.73016 18.7182 5.38107 17.8364 5.25188C17.6 5.21643 17.3636 5.19384 17.1273 5.19384C16.0909 5.19384 15.0818 5.53579 14.3091 6.15857L5.77273 12.7325C5.53636 12.9157 5.31818 13.1325 5.12727 13.3691C5.08364 13.4234 5.04545 13.4777 5.00909 13.5325C4.72727 13.9325 4.50909 14.3839 4.37273 14.8689C4.30545 15.1234 4.26364 15.3857 4.24545 15.6491C4.23636 15.7684 4.23636 15.8877 4.23636 16.0084C4.23636 16.9957 4.69818 17.9264 5.47091 18.5491L10.8619 22.8764C9.28 22.8764 7.77091 22.2536 6.65273 21.1344L4.65273 19.1344C5.37091 19.6753 6.21636 20.0244 7.09818 20.1536C7.33455 20.1891 7.57091 20.2117 7.80727 20.2117C8.84364 20.2117 9.85273 19.8697 10.6255 19.247L19.1619 12.6731C19.3982 12.4899 19.6164 12.2731 19.8073 12.0365C19.8509 11.9822 19.8891 11.9279 19.9255 11.8731C20.2073 11.4731 20.4255 11.0217 20.5619 10.5367C20.6291 10.2822 20.6709 10.0199 20.6891 9.75652C20.6982 9.63725 20.6982 9.51797 20.6982 9.39725C20.6982 8.40997 20.2364 7.47934 19.4636 6.85652L14.0727 2.52911Z"/></svg>
                                    OpenAI
                                </div>
                                <svg class="provider-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="ai-provider-dropdown-item" data-provider="gemini">
                               <div class="provider-name-wrapper">
                                   <svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7V17L12 22L22 17V7L12 2ZM12 4.18L19.5 8L12 11.82L4.5 8L12 4.18ZM4 9.5L11 13V20.32L4 16.82V9.5ZM13 20.32V13L20 9.5V16.82L13 20.32Z"/></svg>
                                   Google Gemini
                               </div>
                               <svg class="provider-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OpenAI Config Section --}}
                <div id="openai-config" class="ai-config-section">
                    <div class="form-group">
                        <label class="ai-form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" name="openai_api_key" class="ai-form-control" value="{{ \App\AiSetting::get('openai_api_key', '') }}" placeholder="sk-...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-info" type="button" onclick="testConnection('openai', this)">Test</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Chat Model</label><input type="text" name="openai_model" class="ai-form-control" value="{{ \App\AiSetting::get('openai_model', 'gpt-4o') }}" placeholder="gpt-4o"></div></div>
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Embedding Model</label><input type="text" name="openai_embedding_model" class="ai-form-control" value="{{ \App\AiSetting::get('openai_embedding_model', 'text-embedding-3-small') }}" placeholder="text-embedding-3-small"></div></div>
                    </div>
                </div>

                {{-- Gemini Config Section --}}
                <div id="gemini-config" class="ai-config-section">
                    <div class="form-group">
                        <label class="ai-form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" name="gemini_api_key" class="ai-form-control" value="{{ \App\AiSetting::get('gemini_api_key', '') }}" placeholder="AIza...">
                             <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-info" type="button" onclick="testConnection('gemini', this)">Test</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Chat Model</label><input type="text" name="gemini_model" class="ai-form-control" value="{{ \App\AiSetting::get('gemini_model', 'gemini-2.0-flash-exp') }}" placeholder="gemini-2.0-flash-exp"></div></div>
                        <div class="col-md-6"><div class="ai-form-group"><label class="ai-form-label">Embedding Model</label><input type="text" name="gemini_embedding_model" class="ai-form-control" value="{{ \App\AiSetting::get('gemini_embedding_model', 'gemini-embedding-001') }}" placeholder="gemini-embedding-001"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Prompt Card --}}
        <div class="ai-card">
            <div class="ai-card-header">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                <h5 class="ai-card-title">System Prompt</h5>
            </div>
            <div class="ai-card-body">
                <p class="text-muted">Define the AI assistant's behavior and personality. This prompt sets the context for all conversations.</p>
                <div class="ai-form-group">
                    <textarea name="system_prompt" id="system_prompt" class="ai-form-control" rows="8" required>{{ $systemPrompt }}</textarea>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="text-right mb-4">
            <button type="submit" class="ai-btn ai-btn-primary">
                <svg class="ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Save Configuration
            </button>
        </div>
    </form>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', function () {
    const providerInput = document.getElementById('ai_provider_input');
    const dropdownButton = document.getElementById('ai_provider_dropdown_btn');
    const dropdownContent = document.getElementById('ai_provider_dropdown_content');
    const selectedProviderText = document.getElementById('selected_provider_text');
    const configSections = {
        openai: document.getElementById('openai-config'),
        gemini: document.getElementById('gemini-config'),
    };
    const providerItems = document.querySelectorAll('.ai-provider-dropdown-item');

    function selectProvider(provider) {
        providerInput.value = provider;
        const selectedItem = document.querySelector(`.ai-provider-dropdown-item[data-provider="${provider}"]`);

        // Update the button text with just the text content of the wrapper, not the checkmark
        selectedProviderText.innerHTML = selectedItem.querySelector('.provider-name-wrapper').innerHTML;

        // Update active status for checkmark
        providerItems.forEach(item => {
            item.classList.remove('selected');
        });
        selectedItem.classList.add('selected');

        for (const key in configSections) {
            configSections[key].classList.toggle('active', key === provider);
        }
        dropdownContent.style.display = 'none';
    }

    selectProvider(providerInput.value);

    dropdownButton.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
    });

    providerItems.forEach(item => {
        item.addEventListener('click', function () {
            selectProvider(this.getAttribute('data-provider'));
        });
    });

    window.addEventListener('click', (e) => {
        if (!dropdownButton.contains(e.target)) {
            dropdownContent.style.display = 'none';
        }
    });
});

function togglePassword(button) {
    const input = button.closest('.input-group').querySelector('input');
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testConnection(provider, button) {
    const apiKeyInput = document.querySelector(`input[name="${provider}_api_key"]`);
    const apiKey = apiKeyInput.value;

    if (!apiKey) {
        alert('Please enter an API key first.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

    fetch('{{ route("ai-settings.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ provider: provider, api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        if (data.success) {
            button.classList.remove('btn-info', 'btn-danger');
            button.classList.add('btn-success');
            button.innerHTML = '<i class="fas fa-check"></i> Connected';
            setTimeout(() => {
                button.classList.remove('btn-success');
                button.classList.add('btn-info');
                button.innerHTML = originalHtml;
            }, 3000);
        } else {
            button.classList.remove('btn-info');
            button.classList.add('btn-danger');
            button.innerHTML = '<i class="fas fa-times"></i> Failed';
            alert('Connection failed: ' + data.message);
            setTimeout(() => {
                button.classList.remove('btn-danger');
                button.classList.add('btn-info');
                button.innerHTML = originalHtml;
            }, 5000);
        }
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalHtml;
        alert('Connection test failed: ' + error.message);
    });
}
</script>
@endsection