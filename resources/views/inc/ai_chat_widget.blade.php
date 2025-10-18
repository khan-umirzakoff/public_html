<link rel="stylesheet" href="{{ asset('css/ai_chat_widget.css') }}?v=20241018">
<style>
    .sources-container {
        margin-top: 12px;
        border-top: 1px solid #e5e5ea;
        padding-top: 8px;
    }
    .sources-button {
        background: #f0f0f0;
        border: none;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #555;
    }
    .sources-button:hover {
        background: #e0e0e0;
    }
    .sources-list {
        margin-top: 8px;
        padding-left: 15px;
    }
    .sources-list a {
        display: block;
        font-size: 13px;
        color: #0d2d62;
        text-decoration: none;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sources-list a:hover {
        text-decoration: underline;
    }
</style>

<div id="ai-chat-widget">
    <button id="chat-icon" aria-label="Open Chat">
        <img src="{{ asset('upl/1111.png') }}" alt="Open Chat">
        <div class="chat-icon-text">
            <span class="chat-icon-line1">JobCare</span>
            <span class="chat-icon-line2">Assistant</span>
        </div>
    </button>
    <div id="chat-window" role="dialog" aria-modal="true" aria-labelledby="chat-header-title">
        <div id="chat-header">
            <div class="chat-title">
                <img src="{{ asset('upl/1111.png') }}" alt="JobCare Logo" class="chat-logo">
                <div class="brand-text">
                    <h2 id="chat-header-title" class="ai-title">JobCare Assistant</h2>
                </div>
            </div>
            <button id="clear-chat" aria-label="New Chat" style="margin-right: 10px;" title="Start New Chat">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                </svg>
            </button>
            <button id="close-chat" aria-label="Close Chat">&times;</button>
        </div>
        <div id="chat-messages" role="log" aria-live="polite">
           <div class="bot-message message">
                Hello! I'm JobCare AI Assistant. How can I help you?
            </div>
        </div>
        
        <!-- New Chat Modal -->
        <div id="new-chat-modal" class="new-chat-modal">
            <div class="modal-content-new">
                <div class="modal-header">
                    <h3>Start New Chat</h3>
                    <p>Current chat history will be deleted</p>
                </div>
                <div class="modal-buttons">
                    <button class="btn-new-chat" id="confirm-new-chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        New Chat
                    </button>
                    <button class="btn-cancel" id="cancel-new-chat">Cancel</button>
                </div>
            </div>
        </div>

        <div id="chat-input">
            <div id="image-previews"></div>
            <div class="chat-input-container">
                <input type="file" id="chat-image-input" accept="image/*" multiple style="display:none;">
                <input type="text" id="chat-input-field" placeholder="Type a message..." aria-label="Message input field">
                <button id="chat-image-button" aria-label="Send Image" title="Upload Image">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.7" d="M17 9.00195C19.175 9.01406 20.3529 9.11051 21.1213 9.8789C22 10.7576 22 12.1718 22 15.0002V16.0002C22 18.8286 22 20.2429 21.1213 21.1215C20.2426 22.0002 18.8284 22.0002 16 22.0002H8C5.17157 22.0002 3.75736 22.0002 2.87868 21.1215C2 20.2429 2 18.8286 2 16.0002L2 15.0002C2 12.1718 2 10.7576 2.87868 9.87889C3.64706 9.11051 4.82497 9.01406 7 9.00195" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12 15L12 2M12 2L15 5.5M12 2L9 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button id="chat-send-button" aria-label="Send Message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="chat-toast" style="position:fixed;top:20px;right:20px;background:#333;color:#fff;padding:12px 16px;border-radius:8px;font-size:14px;z-index:10000;display:none;max-width:300px;word-wrap:break-word;">
    <span id="toast-message"></span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatIcon = document.getElementById('chat-icon');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const clearChat = document.getElementById('clear-chat');
    const chatInputField = document.getElementById('chat-input-field');
    const chatSendButton = document.getElementById('chat-send-button');
    const chatImageButton = document.getElementById('chat-image-button');
    const chatImageInput = document.getElementById('chat-image-input');
    const chatMessages = document.getElementById('chat-messages');

    if (chatIcon && chatWindow && closeChat) {
        // Main button click to open/close window
        chatIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            const isOpening = !chatWindow.classList.contains('open');
            chatWindow.classList.toggle('open');
            
            // Prevent body scroll on mobile when chat is open
            if (window.innerWidth <= 480) {
                if (isOpening) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
        });

        // Close button click to close window
        closeChat.addEventListener('click', function() {
            chatWindow.classList.remove('open');
            
            // Restore body scroll
            if (window.innerWidth <= 480) {
                document.body.style.overflow = '';
            }
        });

        // New chat modal
        const newChatModal = document.getElementById('new-chat-modal');
        const confirmNewChat = document.getElementById('confirm-new-chat');
        const cancelNewChat = document.getElementById('cancel-new-chat');

        if (clearChat) {
            clearChat.addEventListener('click', function() {
                newChatModal.style.display = 'flex';
            });
        }

        if (confirmNewChat) {
            confirmNewChat.addEventListener('click', function() {
                clearChatHistory();
                newChatModal.style.display = 'none';
            });
        }

        if (cancelNewChat) {
            cancelNewChat.addEventListener('click', function() {
                newChatModal.style.display = 'none';
            });
        }

        // User scroll detection
        chatMessages.addEventListener('scroll', function() {
            const isAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 50;
            
            if (!isAtBottom) {
                isUserScrolling = true;
                
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    isUserScrolling = false;
                }, 2000);
            } else {
                isUserScrolling = false;
            }
        });

        // Prevent body scroll when scrolling chat messages
        chatMessages.addEventListener('touchmove', function(e) {
            e.stopPropagation();
        }, { passive: true });

        chatMessages.addEventListener('wheel', function(e) {
            const isScrollable = chatMessages.scrollHeight > chatMessages.clientHeight;
            const isAtTop = chatMessages.scrollTop === 0;
            const isAtBottom = chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 1;
            
            if (isScrollable) {
                if ((isAtTop && e.deltaY < 0) || (isAtBottom && e.deltaY > 0)) {
                    e.preventDefault();
                }
                e.stopPropagation();
            }
        }, { passive: false });

        // Image upload
        if (chatImageButton && chatImageInput) {
            chatImageButton.addEventListener('click', () => {
                chatImageInput.click();
            });

            chatImageInput.addEventListener('change', (e) => {
                const files = e.target.files;
                if (!files || files.length === 0) return;

                const existingPreviews = document.querySelectorAll('.image-preview-msg').length;
                const totalFiles = existingPreviews + files.length;

                if (totalFiles > 3) {
                    showToast('Maximum 3 images can be sent.');
                    e.target.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Check file size (max 5MB)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        showToast('Image size is too large! Maximum 5MB.');
                        e.target.value = '';
                        return;
                    }

                    // Image preview - show small preview above input
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const imageContainer = document.getElementById('image-previews');
                        imageContainer.style.display = 'flex';
                        const imageWrapper = document.createElement('div');
                        imageWrapper.className = 'image-preview-wrapper';
                        imageWrapper.style.position = 'relative';
                        imageWrapper.style.display = 'inline-block';
                        imageWrapper.innerHTML = `
                            <img src="${event.target.result}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;">
                            <button onclick="this.parentElement.remove(); updateSendButton();" 
                                style="position:absolute;top:-6px;right:-6px;background:#ff4757;color:white;border:none;border-radius:50%;width:16px;height:16px;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:bold;">×</button>
                        `;
                        imageContainer.appendChild(imageWrapper);
                        updateSendButton();
                    };
                    reader.readAsDataURL(file);
                }

                // Clear file input
                e.target.value = '';
            });
        }
    }

    // Smart scroll - only when the user is not scrolling (throttled)
    function smartScroll() {
        scrollCounter++;
        // Scroll every 5th chunk (slower)
        if (!isUserScrolling && scrollCounter % 5 === 0) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Force scroll (when message completes)
    function forceScroll() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Show toast notification (use global if available, fallback to local)
    function showToast(message) {
        // Try to use global toast notification system
        if (typeof window.showToast === 'function') {
            window.showToast(message, 'error');
            return;
        }
        
        // Fallback to local toast
        const toast = document.getElementById('chat-toast');
        if (toast) {
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    }

    // Update send button state
    function updateSendButton() {
        const imagePreviews = document.querySelectorAll('.image-preview-wrapper');
        const imageContainer = document.getElementById('image-previews');
        const hasImage = imagePreviews.length > 0;
        const hasText = chatInputField.value.trim() !== '';
        
        // Show/hide image container
        if (hasImage) {
            imageContainer.style.display = 'flex';
        } else {
            imageContainer.style.display = 'none';
        }
        
        chatSendButton.disabled = !(hasText || hasImage);
    }

    // Chat history save (load from LocalStorage)
    let chatHistory = loadChatHistory();
    
    // Auto-scroll state
    let isUserScrolling = false;
    let scrollTimeout = null;
    let scrollCounter = 0;

    // LocalStorage functions
    function saveChatHistory() {
        try {
            localStorage.setItem('jobcare_ai_history', JSON.stringify(chatHistory));
        } catch (e) {
            console.error('LocalStorage error:', e);
        }
    }

    function loadChatHistory() {
        try {
            const saved = localStorage.getItem('jobcare_ai_history');
            if (saved) {
                const history = JSON.parse(saved);

                // Reload old messages to UI
                setTimeout(() => {
                    history.forEach(msg => {
                        if (msg.text || msg.images) {
                            const textDiv = document.createElement('div');
                            textDiv.className = msg.role === 'user' ? 'user-message message' : 'bot-message message';
                            let content = '';
                            if (msg.images && msg.images.length > 0) {
                                content += '<div style="display:flex;gap:5px;margin-bottom:5px;flex-wrap:wrap;">';
                                msg.images.forEach(imageBase64 => {
                                    content += `<img src="data:image/jpeg;base64,${imageBase64}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;margin:2px;">`;
                                });
                                content += '</div>';
                            }
                            if (msg.text) {
                                content += msg.role === 'user' ? msg.text : formatMarkdown(msg.text);
                            }
                            textDiv.innerHTML = content;
                            chatMessages.appendChild(textDiv);
                        }
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 100);

                return history;
            }
        } catch (e) {
            console.error('LocalStorage load error:', e);
        }
        return [];
    }

    function clearChatHistory() {
        chatHistory = [];
        localStorage.removeItem('jobcare_ai_history');
        
        // Remove from UI as well (except initial message)
        const messages = chatMessages.querySelectorAll('.message');
        messages.forEach((msg, index) => {
            if (index > 0) msg.remove(); // Keep first bot message
        });
    }

    // Markdown to HTML converter (Full Support)
    function formatMarkdown(text) {
        if (!text) return '';
        
        text = text.replace(/```([\s\S]+?)```/g, '<pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:6px;overflow-x:auto;margin:8px 0;"><code>$1</code></pre>');
        text = text.replace(/`(.+?)`/g, '<code style="background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;color:#e83e8c;">$1</code>');
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        text = text.replace(/^### (.+)$/gm, '<h3 style="font-size:1.1rem;font-weight:600;margin:12px 0 8px 0;">$1</h3>');
        text = text.replace(/^## (.+)$/gm, '<h2 style="font-size:1.2rem;font-weight:600;margin:14px 0 10px 0;">$1</h2>');
        text = text.replace(/^# (.+)$/gm, '<h1 style="font-size:1.3rem;font-weight:600;margin:16px 0 12px 0;">$1</h1>');
        text = text.replace(/^- (.+)$/gm, '<li style="margin-left:20px;">$1</li>');
        text = text.replace(/^(\d+)\. (.+)$/gm, '<li style="margin-left:20px;list-style-type:decimal;">$2</li>');
        text = text.replace(/^> (.+)$/gm, '<blockquote style="border-left:3px solid #667eea;padding-left:12px;margin:8px 0;color:#666;">$1</blockquote>');
        text = text.replace(/^---$/gm, '<hr style="border:none;border-top:1px solid #e0e0e0;margin:12px 0;">');
        text = text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank" style="color:#667eea;text-decoration:underline;">$1</a>');
        text = text.replace(/\n/g, '<br>');
        
        return text;
    }

    function showUserMessageError(userMessageDiv, text) {
        let errorDiv = userMessageDiv.querySelector('.message-error-text');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'message-error-text';
            errorDiv.style.color = '#ff4757';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '4px';
            errorDiv.style.textAlign = 'right';
            userMessageDiv.appendChild(errorDiv);
        }
        errorDiv.textContent = text;
    }

    async function sendMessage() {
        const message = chatInputField.value.trim();
        const imagePreviews = document.querySelectorAll('.image-preview-wrapper');

        if (message === '' && imagePreviews.length === 0) return;

        if (imagePreviews.length > 0 && message === '') {
            showToast('Text must be entered to send image.');
            return;
        }

        const images = [];
        for (let preview of imagePreviews) {
            const img = preview.querySelector('img');
            if (img && img.src.startsWith('data:image/')) {
                const base64 = img.src.split(',')[1];
                images.push(base64);
            }
        }

        document.getElementById('image-previews').innerHTML = '';
        updateSendButton();

        const userMessageDiv = document.createElement('div');
        userMessageDiv.className = 'user-message message';
        let content = '';
        if (images.length > 0) {
            content += '<div style="display:flex;gap:5px;margin-bottom:5px;flex-wrap:wrap;">';
            images.forEach(imageBase64 => {
                content += `<img src="data:image/jpeg;base64,${imageBase64}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;margin:2px;">`;
            });
            content += '</div>';
        }
        content += message;
        userMessageDiv.innerHTML = content;
        chatMessages.appendChild(userMessageDiv);
        forceScroll();

        chatInputField.value = '';
        updateSendButton();

        if (!navigator.onLine) {
            showUserMessageError(userMessageDiv, 'Please check your internet connection.');
            return;
        }

        chatInputField.disabled = true;
        chatSendButton.disabled = true;
        chatImageButton.disabled = true;

        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'bot-message message loading';
        loadingDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        chatMessages.appendChild(loadingDiv);
        forceScroll();

        chatHistory.push({ role: 'user', text: message, images: images });
        saveChatHistory();

        try {
            const response = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    message: message,
                    history: chatHistory.slice(-10),
                    stream: true,
                    images: images
                })
            });

            if (loadingDiv.parentElement) loadingDiv.remove();

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ error: 'Server connection error.' }));
                throw new Error(errorData.error || 'Network response was not ok');
            }

            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'bot-message message';
            chatMessages.appendChild(botMessageDiv);

            let fullText = '';
            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value, { stream: true });
                const lines = chunk.split('\n');

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        try {
                            const dataStr = line.slice(6);
                            if (!dataStr) continue;
                            const data = JSON.parse(dataStr);

                            if (data.thinking === true) {
                                let thinkDiv = document.getElementById('think-indicator');
                                if (!thinkDiv) {
                                    thinkDiv = document.createElement('div');
                                    thinkDiv.className = 'thinking-indicator';
                                    thinkDiv.id = 'think-indicator';
                                    thinkDiv.innerHTML = `<span class="thinking-text">thinking</span><span class="dots"><span>.</span><span>.</span><span>.</span></span>`;
                                    botMessageDiv.before(thinkDiv);
                                    forceScroll();
                                }
                            }
                            
                            if (data.thinking === false) {
                                const thinkDiv = document.getElementById('think-indicator');
                                if (thinkDiv) thinkDiv.remove();
                            }

                            if (data.chunk) {
                                fullText += data.chunk;
                                botMessageDiv.innerHTML = formatMarkdown(fullText);
                                smartScroll();
                            }

                            if (data.done) {
                                // Add sources if available
                                if (data.sources && data.sources.length > 0) {
                                    const sourcesContainer = document.createElement('div');
                                    sourcesContainer.className = 'sources-container';

                                    const sourcesButton = document.createElement('button');
                                    sourcesButton.className = 'sources-button';
                                    sourcesButton.innerHTML = `
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        Sources
                                    `;

                                    const sourcesList = document.createElement('div');
                                    sourcesList.className = 'sources-list';
                                    sourcesList.style.display = 'none';

                                    data.sources.forEach(source => {
                                        // Skip sources without valid URLs
                                        if (!source.url || source.url === 'null' || source.url === null) {
                                            return;
                                        }
                                        const link = document.createElement('a');
                                        link.href = source.url;
                                        link.textContent = source.title || 'Details';
                                        link.target = '_blank';
                                        sourcesList.appendChild(link);
                                    });

                                    sourcesButton.addEventListener('click', () => {
                                        sourcesList.style.display = sourcesList.style.display === 'none' ? 'block' : 'none';
                                        forceScroll();
                                    });

                                    sourcesContainer.appendChild(sourcesButton);
                                    sourcesContainer.appendChild(sourcesList);
                                    botMessageDiv.appendChild(sourcesContainer);
                                }

                                chatHistory.push({ role: 'model', text: fullText, sources: data.sources || [] });
                                saveChatHistory();
                                forceScroll();
                                scrollCounter = 0;
                            }

                            if (data.error) throw new Error(data.error);

                        } catch (e) {
                            console.error('JSON parse error:', e, 'line:', line);
                        }
                    }
                }
            }

        } catch (error) {
            console.error('AI Chat Error:', error);

            if (loadingDiv.parentElement) loadingDiv.remove();
            const thinkDiv = document.getElementById('think-indicator');
            if (thinkDiv) thinkDiv.remove();

            const errorDiv = document.createElement('div');
            errorDiv.className = 'bot-message message error-message';
            if (!navigator.onLine) {
                errorDiv.textContent = 'Internet connection lost. Please reconnect and try again.';
            } else {
                errorDiv.textContent = 'Sorry, an error occurred. Please try again.';
            }
            chatMessages.appendChild(errorDiv);
        } finally {
            chatInputField.disabled = false;
            chatSendButton.disabled = false;
            chatImageButton.disabled = false;
            chatInputField.focus();
            forceScroll();
        }
    }

    // Update send button when input field changes
    if (chatInputField) {
        chatInputField.addEventListener('input', updateSendButton);

        chatInputField.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    }

    // Send message when send button is clicked
    if (chatSendButton) {
        chatSendButton.addEventListener('click', function() {
            sendMessage();
        });
    }

    // Resize functionality - Bottom Left Corner
    let isResizing = false;
    let startX, startY, startWidth, startHeight;

    chatWindow.addEventListener('mousedown', function(e) {
        // Check if click is in bottom-left corner (resize handle area)
        const rect = chatWindow.getBoundingClientRect();
        const isInResizeZone = (
            e.clientX < rect.left + 30 &&
            e.clientY > rect.bottom - 30
        );

        if (isInResizeZone && chatWindow.classList.contains('open')) {
            isResizing = true;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = parseInt(getComputedStyle(chatWindow).width, 10);
            startHeight = parseInt(getComputedStyle(chatWindow).height, 10);
            
            e.preventDefault();
            e.stopPropagation();
            
            document.body.style.cursor = 'nesw-resize';
            document.body.style.userSelect = 'none';
        }
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;

        const deltaX = startX - e.clientX;
        const deltaY = startY - e.clientY;
        
        const newWidth = startWidth + deltaX;
        const newHeight = startHeight + deltaY;

        // Apply constraints
        const minWidth = 350;
        const maxWidth = 1200;
        const minHeight = 450;
        const maxHeight = window.innerHeight - 120;

        if (newWidth >= minWidth && newWidth <= maxWidth) {
            chatWindow.style.width = newWidth + 'px';
        }
        
        if (newHeight >= minHeight && newHeight <= maxHeight) {
            chatWindow.style.height = newHeight + 'px';
        }
    });

    document.addEventListener('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            
            // Save size to localStorage
            try {
                localStorage.setItem('jobcare_chat_width', chatWindow.style.width);
                localStorage.setItem('jobcare_chat_height', chatWindow.style.height);
            } catch (e) {
                console.error('Failed to save chat size:', e);
            }
        }
    });

    // Load saved size on page load
    try {
        const savedWidth = localStorage.getItem('jobcare_chat_width');
        const savedHeight = localStorage.getItem('jobcare_chat_height');
        
        if (savedWidth) chatWindow.style.width = savedWidth;
        if (savedHeight) chatWindow.style.height = savedHeight;
    } catch (e) {
        console.error('Failed to load chat size:', e);
    }

    // Update cursor when hovering over resize area
    chatWindow.addEventListener('mousemove', function(e) {
        if (!chatWindow.classList.contains('open') || isResizing) return;
        
        const rect = chatWindow.getBoundingClientRect();
        const isInResizeZone = (
            e.clientX < rect.left + 30 &&
            e.clientY > rect.bottom - 30
        );
        
        chatWindow.style.cursor = isInResizeZone ? 'nesw-resize' : '';
    });
});
</script>