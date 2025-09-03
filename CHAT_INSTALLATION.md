# Chat System Installation Guide

This guide will help you install and set up the real-time chat system for your CodeIgniter 4 Manreal Store project.

## Prerequisites

- CodeIgniter 4 project running
- MySQL database configured
- User authentication system working
- Users table with admin and cashier roles

## Installation Steps

### 1. Run Database Migrations

First, run the migrations to create the necessary database tables:

```bash
php spark migrate
```

This will create:
- `messages` table for storing chat messages
- Add `last_activity` field to the `users` table

### 2. Seed Sample Data (Optional)

To test the chat system with sample messages, run:

```bash
php spark db:seed MessageSeeder
```

### 3. Verify File Structure

Ensure these files are created in your project:

```
app/
├── Controllers/
│   └── ChatController.php
├── Models/
│   └── MessageModel.php
├── Views/
│   └── chat/
│       └── index.php
└── Database/
    ├── Migrations/
    │   ├── 2024-01-01-000001_CreateMessagesTable.php
    │   └── 2024-01-01-000002_AddLastActivityToUsers.php
    └── Seeds/
        └── MessageSeeder.php
```

### 4. Check Routes

Verify that chat routes are added to `app/Config/Routes.php`:

```php
// Chat routes
$routes->group('chat', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ChatController::index');
    
    // AJAX routes
    $routes->post('send', 'ChatController::sendMessage');
    $routes->get('fetch', 'ChatController::fetchMessages');
    $routes->get('search-users', 'ChatController::searchUsers');
    $routes->get('unread-count', 'ChatController::getUnreadCount');
    $routes->get('online-users', 'ChatController::getOnlineUsers');
    $routes->post('update-activity', 'ChatController::updateActivity');
});
```

### 5. Verify Navigation

Check that the chat link is added to your main layout (`app/Views/layouts/main.php`):

```php
<a class="nav-link <?= strpos($current_url, 'chat') !== false ? 'active' : '' ?>" href="<?= base_url('chat') ?>" id="chatNavLink">
    <i class="bi bi-chat-dots"></i> Chat
</a>
```

## Testing the Chat System

### 1. Access Chat

- Login as admin or cashier
- Navigate to `/chat` or click the Chat link in the sidebar
- You should see the chat interface

### 2. Test Features

- **User Search**: Type in the search box to find users
- **Start Conversation**: Click on a user to start chatting
- **Send Messages**: Type and send messages
- **Real-time Updates**: Messages should update every 3 seconds

### 3. Test with Multiple Users

- Open chat in two different browser windows
- Login as different users (admin and cashier)
- Send messages between them
- Verify real-time updates

## Features

### ✅ Implemented Features

- **Real-time Chat**: Messages update every 3 seconds via AJAX polling
- **User Search**: Search for users by name or email
- **Online Status**: Shows users active in the last 5 minutes
- **Recent Conversations**: Displays chat history
- **Unread Messages**: Tracks unread message count
- **User Activity Tracking**: Updates user's last activity
- **Responsive Design**: Works on desktop and mobile
- **Role-based Access**: Only authenticated users can access chat

### 🔧 Technical Features

- **CodeIgniter 4 MVC**: Proper separation of concerns
- **AJAX Integration**: Real-time updates without page refresh
- **Database Optimization**: Efficient queries with proper indexing
- **Security**: Authentication required for all chat operations
- **Error Handling**: Proper error messages and validation

## Database Schema

### Messages Table

```sql
CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) unsigned NOT NULL,
  `receiver_id` int(11) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
```

### Users Table (Updated)

The `users` table now includes:
- `last_activity` - Tracks when user was last active

## Troubleshooting

### Common Issues

1. **"Class 'MessageModel' not found"**
   - Ensure `MessageModel.php` is in `app/Models/` directory
   - Check namespace is correct: `namespace App\Models;`

2. **"Table 'messages' doesn't exist"**
   - Run migrations: `php spark migrate`
   - Check database connection in `.env` file

3. **Chat not loading**
   - Check browser console for JavaScript errors
   - Verify jQuery is loaded
   - Check if user is authenticated

4. **Messages not sending**
   - Check AJAX requests in browser Network tab
   - Verify CSRF token if enabled
   - Check database permissions

5. **Real-time updates not working**
   - Check if AJAX polling is working
   - Verify `updateActivity()` endpoint is accessible
   - Check browser console for errors

### Debug Mode

Enable debug mode in `.env`:

```env
CI_ENVIRONMENT = development
```

This will show detailed error messages and help troubleshoot issues.

## Customization

### Styling

The chat interface uses Bootstrap 5 and custom CSS. You can customize:

- Colors in `app/Views/chat/index.php` styles section
- Layout in the main view file
- Icons using Bootstrap Icons

### Polling Frequency

Change the message update frequency in the JavaScript:

```javascript
// In app/Views/chat/index.php
setInterval(function() {
    if (currentChatUser) {
        loadMessages(currentChatUser);
    }
}, 3000); // Change 3000 to desired milliseconds
```

### Message Display

Customize message appearance by modifying the CSS classes in the styles section.

## Security Considerations

- All chat endpoints require authentication
- Users can only see messages they're involved in
- Input validation and sanitization implemented
- SQL injection protection via CodeIgniter's query builder
- XSS protection via proper output escaping

## Performance

- Messages are loaded with pagination support
- Efficient database queries with proper indexing
- AJAX polling optimized for real-time updates
- User activity updates are batched

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review browser console for JavaScript errors
3. Check CodeIgniter logs in `writable/logs/`
4. Verify database migrations ran successfully
5. Test with a fresh browser session

## Future Enhancements

Potential improvements you could add:

- **Push Notifications**: Browser notifications for new messages
- **File Sharing**: Send images and documents
- **Group Chats**: Multiple user conversations
- **Message Encryption**: End-to-end encryption
- **Voice Messages**: Audio recording and playback
- **Message Reactions**: Like, heart, etc.
- **Typing Indicators**: Show when someone is typing
- **Message Search**: Search through conversation history


