# MongoDB Setup Instructions for Manreal Store

## Overview
The Manrealstore application has been modified to use MongoDB instead of MySQL. The database name is "manreal".

## Dependencies Added
- mongodb/mongodb: PHP MongoDB library
- ext-mongodb: PHP extension requirement

## Files Modified/Created
1. `composer.json` - Added MongoDB dependencies
2. `.env` - Added MongoDB configuration
3. `app/Libraries/MongoDB.php` - MongoDB connection and operations wrapper
4. `app/Models/UserModel.php` - Updated to use MongoDB
5. `app/Commands/MongoDBSetup.php` - Setup command for collections and indexes

## Installation Steps

### Step 1: Install PHP MongoDB Extension
Since you're using XAMPP on Windows, you need to install the MongoDB PHP extension:

1. **Check your PHP version**: Run `php --version` to confirm your PHP version (you have PHP 8.2.12)
2. **Download the correct DLL**:
   - For PHP 8.2 Thread Safe (TS) x64: Go to https://pecl.php.net/package/mongodb
   - Download version 1.17.3 for PHP 8.2 TS x64
   - Look for: `php_mongodb-1.17.3-8.2-ts-vs16-x64.zip`
3. **Extract and copy**: Extract `php_mongodb.dll` to `C:\xampp\php\ext\`
4. **Edit php.ini**: Add this line to your php.ini: `extension=php_mongodb.dll`
5. **Restart Apache**: Restart XAMPP Apache

**Troubleshooting:**
- If you get "The specified module could not be found", make sure you downloaded the correct version (8.2 TS x64, not 8.1)
- Run `php -m | findstr mongo` to verify the extension is loaded
- Make sure MongoDB service is running: `net start MongoDB` or check Services.msc

### Step 2: Install MongoDB
1. Download and install MongoDB from: https://www.mongodb.com/try/download/community
2. Start MongoDB service
3. MongoDB should be running on localhost:27017

### Step 3: Install Dependencies
```bash
composer update
```

### Step 4: Setup Database
Run the setup command to create collections and indexes:
```bash
php spark mongodb:setup
```

### Step 5: Seed Default Data
Run the seeding command to insert default users:
```bash
php spark mongodb:seed
```

**Default Users Created:**
- **Admin**: username `admin`, password `admin123`
- **Staff**: username `staff`, password `staff123`
- **Sample**: username `john_doe`, password `password`

## Database Schema (Collections)

### users
- _id (ObjectId, auto-generated)
- username (string, unique)
- email (string, unique)
- password (string)
- full_name (string)
- contact_number (string, optional)
- role (string: 'admin'|'cashier')
- is_active (boolean)
- last_login (datetime, optional)
- last_activity (datetime, optional)
- created_at (datetime)
- updated_at (datetime)

### messages
- _id (ObjectId, auto-generated)
- sender_id (string, ObjectId reference)
- receiver_id (string, ObjectId reference)
- message (string)
- is_read (boolean)
- created_at (datetime)
- updated_at (datetime)

### products
- _id (ObjectId, auto-generated)
- product_code (string, unique)
- name (string)
- description (string, optional)
- category (string)
- price (decimal)
- cost_price (decimal)
- quantity (integer)
- min_stock (integer)
- image (string, optional)
- is_active (boolean)
- created_at (datetime)
- updated_at (datetime)

### sales
- _id (ObjectId, auto-generated)
- sale_number (string, unique)
- user_id (string, ObjectId reference)
- customer_name (string, optional)
- subtotal (decimal)
- discount (decimal)
- tax (decimal)
- total_amount (decimal)
- cash_received (decimal)
- change_amount (decimal)
- payment_method (string: 'cash'|'card'|'gcash'|'maya')
- status (string: 'completed'|'cancelled'|'refunded')
- notes (string, optional)
- created_at (datetime)
- updated_at (datetime)

### sale_items
- _id (ObjectId, auto-generated)
- sale_id (string, ObjectId reference)
- product_id (string, ObjectId reference)
- product_code (string)
- product_name (string)
- quantity (integer)
- unit_price (decimal)
- total_price (decimal)
- created_at (datetime)

### inventory_logs
- _id (ObjectId, auto-generated)
- product_id (string, ObjectId reference)
- user_id (string, ObjectId reference)
- action_type (string: 'sale'|'restock'|'adjustment'|'damaged'|'return')
- quantity_change (integer)
- previous_quantity (integer)
- new_quantity (integer)
- reference_id (string, optional, ObjectId reference)
- reference_type (string, optional)
- notes (string, optional)
- created_at (datetime)

### password_resets
- _id (ObjectId, auto-generated)
- email (string)
- token (string, unique)
- created_at (datetime)

## Configuration
Update your `.env` file with MongoDB settings:
```
# MONGODB CONFIGURATION
MONGODB_HOST = localhost
MONGODB_PORT = 27017
MONGODB_DATABASE = manreal
MONGODB_USERNAME =
MONGODB_PASSWORD =
```

## Next Steps
1. Update remaining models (ProductModel, SaleModel, etc.) to use MongoDB
2. Update controllers to work with the new data structure
3. Test the application with MongoDB

## Migration from MySQL to MongoDB
If you have existing data in MySQL, you may need to create a migration script to transfer the data to MongoDB format.
