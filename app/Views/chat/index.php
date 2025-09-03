<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar - Users and Conversations -->
        <div class="col-md-4 col-lg-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots me-2"></i>Chat
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Search Users -->
                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <input type="text" id="searchUsers" class="form-control" placeholder="Search users...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Online Users -->
                    <div class="p-3 border-bottom">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-circle-fill text-success me-1"></i>Online Users
                        </h6>
                        <div id="onlineUsers">
                            <?php if (!empty($online_users)): ?>
                                <?php foreach ($online_users as $user): ?>
                                    <div class="d-flex align-items-center mb-2 user-item" data-user-id="<?= $user['id'] ?>">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold"><?= $user['full_name'] ?></div>
                                            <small class="text-muted"><?= ucfirst($user['role']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No users online</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Conversations -->
                    <div class="p-3">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-clock-history me-1"></i>Recent Conversations
                        </h6>
                        <div id="conversations">
                            <?php if (!empty($conversations)): ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="d-flex align-items-center mb-2 conversation-item" data-user-id="<?= $conv['other_user_id'] ?>">
                                        <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <?= strtoupper(substr($conv['other_user_name'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold"><?= $conv['other_user_name'] ?></div>
                                            <small class="text-muted"><?= ucfirst($conv['other_user_role']) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted"><?= date('M j', strtotime($conv['last_message_time'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No recent conversations</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 col-lg-9">
            <div class="card">
                <div class="card-header bg-light">
                    <div id="chatHeader" class="d-flex align-items-center">
                        <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Select a user to start chatting</h6>
                            <small class="text-muted">Choose from the list on the left</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- Chat Messages Area -->
                    <div id="chatMessages" class="chat-messages p-3" style="height: 400px; overflow-y: auto;">
                        <div class="text-center text-muted mt-5">
                            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                            <p class="mt-3">Select a user to start chatting</p>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="border-top p-3">
                        <div class="input-group">
                            <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." disabled>
                            <button class="btn btn-primary" type="button" id="sendBtn" disabled>
                                <i class="bi bi-send"></i> Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Search Results Modal -->
<div class="modal fade" id="searchResultsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="searchResultsBody">
                <!-- Search results will be populated here -->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.chat-messages {
    background-color: #f8f9fa;
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
    word-wrap: break-word;
}

.message.sent .message-content {
    background-color: #007bff;
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message.received .message-content {
    background-color: white;
    color: #212529;
    border: 1px solid #dee2e6;
    border-bottom-left-radius: 0.25rem;
}

.message-time {
    font-size: 0.75rem;
    margin-top: 0.25rem;
    opacity: 0.7;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
}

.user-item, .conversation-item {
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.2s;
}

.user-item:hover, .conversation-item:hover {
    background-color: #f8f9fa;
}

.user-item.active, .conversation-item.active {
    background-color: #e3f2fd;
}

.unread-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    min-width: 1.5rem;
    text-align: center;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentChatUser = null;
let messagePollingInterval = null;

$(document).ready(function() {
    // Initialize chat functionality
    initializeChat();
    
    // Start activity updates
    startActivityUpdates();
    
    // Start unread count updates
    startUnreadCountUpdates();
});

function initializeChat() {
    // User search functionality
    $('#searchBtn').click(function() {
        searchUsers();
    });
    
    $('#searchUsers').keypress(function(e) {
        if (e.which === 13) {
            searchUsers();
        }
    });
    
    // Send message functionality
    $('#sendBtn').click(function() {
        sendMessage();
    });
    
    $('#messageInput').keypress(function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });
    
    // User selection
    $(document).on('click', '.user-item, .conversation-item', function() {
        const userId = $(this).data('user-id');
        selectUser(userId);
    });
}

function searchUsers() {
    const searchTerm = $('#searchUsers').val().trim();
    if (searchTerm.length < 2) {
        alert('Please enter at least 2 characters to search');
        return;
    }
    
    $.ajax({
        url: '<?= base_url('chat/search-users') ?>',
        method: 'GET',
        data: { search: searchTerm },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.users);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function() {
            alert('Error searching users');
        }
    });
}

function displaySearchResults(users) {
    let html = '';
    if (users.length > 0) {
        users.forEach(function(user) {
            html += `
                <div class="d-flex align-items-center mb-2 user-item" data-user-id="${user.id}">
                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                        ${user.full_name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${user.full_name}</div>
                        <small class="text-muted">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</small>
                    </div>
                </div>
            `;
        });
    } else {
        html = '<p class="text-muted">No users found</p>';
    }
    
    $('#searchResultsBody').html(html);
    $('#searchResultsModal').modal('show');
    
    // Add click handler for search results
    $('#searchResultsModal .user-item').click(function() {
        const userId = $(this).data('user-id');
        selectUser(userId);
        $('#searchResultsModal').modal('hide');
    });
}

function selectUser(userId) {
    // Update active state
    $('.user-item, .conversation-item').removeClass('active');
    $(`.user-item[data-user-id="${userId}"], .conversation-item[data-user-id="${userId}"]`).addClass('active');
    
    currentChatUser = userId;
    
    // Enable message input
    $('#messageInput').prop('disabled', false);
    $('#sendBtn').prop('disabled', false);
    
    // Load chat header
    loadChatHeader(userId);
    
    // Load messages
    loadMessages(userId);
    
    // Start message polling
    startMessagePolling(userId);
}

function loadChatHeader(userId) {
    // Find user info from existing data
    let user = null;
    
    // Check online users
    $('.user-item').each(function() {
        if ($(this).data('user-id') == userId) {
            const name = $(this).find('.fw-bold').text();
            const role = $(this).find('.text-muted').text();
            user = { full_name: name, role: role };
            return false;
        }
    });
    
    // Check conversations
    if (!user) {
        $('.conversation-item').each(function() {
            if ($(this).data('user-id') == userId) {
                const name = $(this).find('.fw-bold').text();
                const role = $(this).find('.text-muted').text();
                user = { full_name: name, role: role };
                return false;
            }
        });
    }
    
    if (user) {
        $('#chatHeader').html(`
            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                ${user.full_name.charAt(0).toUpperCase()}
            </div>
            <div>
                <h6 class="mb-0">${user.full_name}</h6>
                <small class="text-muted">${user.role}</small>
            </div>
        `);
    }
}

function loadMessages(userId) {
    $.ajax({
        url: '<?= base_url('chat/fetch') ?>',
        method: 'GET',
        data: { user_id: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayMessages(response.messages);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            alert('Error loading messages: ' + xhr.responseText);
        }
    });
}

function displayMessages(messages) {
    let html = '';
    const currentUserId = <?= session()->get('user_id') ?>;
    
    messages.forEach(function(message) {
        const isSent = message.sender_id == currentUserId;
        const messageClass = isSent ? 'sent' : 'received';
        const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        html += `
            <div class="message ${messageClass}">
                <div class="message-content">
                    <div>${message.message}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
    });
    
    $('#chatMessages').html(html);
    scrollToBottom();
}

function sendMessage() {
    if (!currentChatUser) return;
    
    const message = $('#messageInput').val().trim();
    if (!message) return;
    
    $.ajax({
        url: '<?= base_url('chat/send') ?>',
        method: 'POST',
        data: {
            receiver_id: currentChatUser,
            message: message
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#messageInput').val('');
                // Reload messages to show the new message
                loadMessages(currentChatUser);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function() {
            alert('Error sending message');
        }
    });
}

function startMessagePolling(userId) {
    // Clear existing interval
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    // Start new polling
    messagePollingInterval = setInterval(function() {
        if (currentChatUser) {
            loadMessages(currentChatUser);
        }
    }, 3000); // Poll every 3 seconds
}

function startActivityUpdates() {
    // Update user activity every 30 seconds
    setInterval(function() {
        $.ajax({
            url: '<?= base_url('chat/update-activity') ?>',
            method: 'POST',
            dataType: 'json',
            error: function() {
                // Silently fail for activity updates
            }
        });
    }, 30000);
}

function startUnreadCountUpdates() {
    // Update unread count every 10 seconds
    setInterval(function() {
        $.ajax({
            url: '<?= base_url('chat/unread-count') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateUnreadCount(response.unread_count);
                }
            }
        });
    }, 10000);
}

function updateUnreadCount(count) {
    // Update unread count in navigation or header
    // You can customize this based on your layout
    if (count > 0) {
        // Add or update unread badge
        if ($('#chatUnreadBadge').length === 0) {
            $('<span id="chatUnreadBadge" class="unread-badge ms-2">' + count + '</span>').appendTo('#chatNavLink');
        } else {
            $('#chatUnreadBadge').text(count);
        }
    } else {
        // Remove unread badge
        $('#chatUnreadBadge').remove();
    }
}

function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Clean up on page unload
$(window).on('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});
</script>
<?= $this->endSection() ?>


