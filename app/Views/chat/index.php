<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Chat Management -->
    <div class="row">
    <div class="col-md-4">
            <div class="card">
            <div class="card-header">
                    <h5 class="mb-0">
                    <i class="bi bi-people-fill me-2"></i>
                    Online Users
                    </h5>
                </div>
                <div class="card-body p-0">
                <div class="list-group list-group-flush" id="usersList">
                    <?php foreach ($users as $user): ?>
                    <div class="list-group-item list-group-item-action user-item" 
                         data-user-id="<?= $user['id'] ?>" 
                         data-user-name="<?= htmlspecialchars($user['full_name']) ?>"
                         data-user-role="<?= $user['role'] ?>"
                         onclick="loadMessages(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>', '<?= $user['role'] ?>')">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                                        </div>
                                        <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h6>
                                            <small class="text-muted"><?= ucfirst($user['role']) ?></small>
                                        </div>
                            <div class="status-indicator">
                                <span class="badge bg-success">Online</span>
                                    </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    <div class="col-md-8">
            <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-chat-dots me-2"></i>
                    <span id="chatTitle">Select a user to start chatting</span>
                </h5>
                <div class="chat-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshMessages()" id="refreshBtn" title="Refresh Messages">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <small class="text-muted ms-2" id="lastRefresh">Auto-refresh every 2s</small>
                        </div>
                        </div>
            <div class="card-body p-0">
                <div id="messages" class="messages-container">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3">Click on a user to start a conversation</p>
                    </div>
                </div>
                        </div>
            <div class="card-footer" id="messageInputContainer" style="display: none;">
                        <div class="input-group">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type your message..." 
                           onkeypress="handleKeyPress(event)">
                    <button class="btn btn-primary" type="button" onclick="sendMessage()" id="sendBtn">
                        <i class="bi bi-send"></i>
                            </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.messages-container {
    height: 500px;
    overflow-y: auto;
    background-color: #f8f9fa;
    padding: 1rem;
}

.message {
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
}

.message.sent {
    justify-content: flex-end;
}

.message.received {
    justify-content: flex-start;
}

.message-content {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    position: relative;
}

.message.sent .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message.received .message-content {
    background: white;
    border: 1px solid #dee2e6;
    border-bottom-left-radius: 0.25rem;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-top: 0.25rem;
}

.user-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border: none !important;
}

.user-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(5px);
}

.user-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white;
}

.user-item.active .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.user-item.active .badge {
    background-color: rgba(255,255,255,0.2) !important;
    color: white !important;
}

.status-indicator .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.avatar {
    position: relative;
}

.typing-indicator {
    font-style: italic;
    color: #6c757d;
    font-size: 0.9rem;
}

#messageInput:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}
</style>

<!-- JavaScript -->
<script>
let currentChatUser = null;
let currentChatUserName = null;
let currentChatUserRole = null;

function loadMessages(userId, userName, userRole) {
    // Update active user
    $('.user-item').removeClass('active');
    $(`.user-item[data-user-id="${userId}"]`).addClass('active');
    
    currentChatUser = userId;
    currentChatUserName = userName;
    currentChatUserRole = userRole;
    
    // Update chat title
    $('#chatTitle').html(`<i class="bi bi-person-circle me-2"></i>${userName} (${userRole})`);
    
    // Show message input
    $('#messageInputContainer').show();
    $('#refreshBtn').prop('disabled', false);
    
    // Update refresh status
    $('#lastRefresh').text('Auto-refresh every 2s');
    
    // Load messages
    $.ajax({
        url: '<?= base_url('chat/fetch') ?>?user_id=' + userId,
        method: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(data) {
            console.log('Load messages success:', data);
            if (data.success && data.messages) {
                // Initialize message count for new message detection
                lastMessageCount = data.messages.length;
                displayMessages(data.messages);
            } else {
                showError('Error: ' + (data.error || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.log('Load messages error - XHR:', xhr);
            console.log('Load messages error - Status:', status);
            console.log('Load messages error - Error:', error);
            console.log('Load messages error - Response:', xhr.responseText);
            
            // Try to parse the response even if status is 500
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success && response.messages) {
                    displayMessages(response.messages);
                } else {
                    showError('Error: ' + (response.error || error));
                }
            } catch (e) {
                showError('Error loading messages: ' + error);
            }
        }
    });
}

function displayMessages(messages) {
    let html = '';
    
    if (messages && messages.length > 0) {
        messages.forEach(function(msg) {
            const isSent = msg.sender_id == <?= $current_user_id ?>;
        const messageClass = isSent ? 'sent' : 'received';
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        html += `
            <div class="message ${messageClass}">
                <div class="message-content">
                        <div>${msg.message}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
    });
    } else {
        html = '<div class="text-center text-muted py-3"><i class="bi bi-chat-dots me-2"></i>No messages yet. Start the conversation!</div>';
    }
    
    $('#messages').html(html);
    scrollToBottom();
}

// Track last message count to detect new messages
let lastMessageCount = 0;

function checkForNewMessages(messages) {
    if (messages && messages.length > lastMessageCount && lastMessageCount > 0) {
        // New message detected - show notification
        showNewMessageNotification();
    }
    lastMessageCount = messages ? messages.length : 0;
}

function showNewMessageNotification() {
    // Create a subtle notification
    const notification = $('<div class="alert alert-info alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
        '<i class="bi bi-chat-dots me-2"></i>New message received!' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 3000);
}

function sendMessage() {
    if (!currentChatUser) return;
    
    const message = $('#messageInput').val().trim();
    if (!message) return;
    
    // Disable send button to prevent double-sending
    $('#sendBtn').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('chat/send') ?>',
        method: 'POST',
        data: {
            receiver_id: currentChatUser,
            message: message
        },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Send response:', response);
            if (response && response.success) {
                $('#messageInput').val('');
                // Reload messages to show the new message
                loadMessages(currentChatUser, currentChatUserName, currentChatUserRole);
            } else {
                const errorMsg = (response && response.error) ? response.error : 'Unknown error occurred';
                showError('Error: ' + errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.log('Send error - Status:', xhr.status);
            console.log('Send error - Response:', xhr.responseText);
            
            // Try to parse error response
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.success) {
                    // If it's actually a success response but came through error handler
                    $('#messageInput').val('');
                    loadMessages(currentChatUser, currentChatUserName, currentChatUserRole);
                } else {
                    showError('Error: ' + (errorResponse.error || 'Unknown error'));
                }
            } catch (e) {
                // If we can't parse JSON, check if it's a 500 error but message was sent
                if (xhr.status === 500) {
                    // Try to reload messages anyway, as the message might have been sent
                    $('#messageInput').val('');
                    loadMessages(currentChatUser, currentChatUserName, currentChatUserRole);
                } else {
                    showError('Error sending message: ' + (xhr.statusText || error));
                }
            }
        },
        complete: function() {
            // Re-enable send button
            $('#sendBtn').prop('disabled', false);
        }
    });
}

function handleKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function refreshMessages() {
        if (currentChatUser) {
        console.log('Refreshing messages for user:', currentChatUser);
        
        // Load messages silently (without console logs for auto-refresh)
        $.ajax({
            url: '<?= base_url('chat/fetch') ?>?user_id=' + currentChatUser,
            method: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                console.log('Refresh success:', data);
                if (data.success && data.messages) {
                    // Check for new messages before displaying
                    checkForNewMessages(data.messages);
                    displayMessages(data.messages);
                }
                // Update last refresh time
                const now = new Date();
                $('#lastRefresh').text('Last updated: ' + now.toLocaleTimeString());
            },
            error: function(xhr, status, error) {
                console.log('Refresh error:', xhr.responseText);
                
                // Try to parse the response even if status is 500
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && response.messages) {
                        checkForNewMessages(response.messages);
                        displayMessages(response.messages);
                    }
                } catch (e) {
                    console.log('Failed to parse refresh response:', e);
                }
            }
        });
    } else {
        console.log('No currentChatUser set, skipping refresh');
    }
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showError(message) {
    $('#messages').html(`
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        </div>
    `);
}

// Auto-refresh messages every 2 seconds for real-time updates
setInterval(function() {
    if (currentChatUser) {
        console.log('Auto-refreshing messages for user:', currentChatUser);
        refreshMessages();
    }
}, 2000);

// Also refresh when the page becomes visible (user switches back to tab)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && currentChatUser) {
        refreshMessages();
    }
});

// Refresh when user focuses on the window
window.addEventListener('focus', function() {
    if (currentChatUser) {
        refreshMessages();
    }
});
</script>

<?= $this->endSection() ?>