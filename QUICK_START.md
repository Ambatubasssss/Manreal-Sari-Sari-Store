# 🚀 Quick Start Guide - Manreal Store POS System

Get your POS system up and running in 10 minutes!

## ⚡ Prerequisites Check

Before starting, ensure you have:
- ✅ PHP 8.0+ installed
- ✅ MySQL/MariaDB server running
- ✅ Web server (Apache/Nginx) configured
- ✅ CodeIgniter 4 framework files

## 🎯 Step-by-Step Setup

### Step 1: Database Creation
```sql
CREATE DATABASE manreal_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 2: Run Setup Script
1. Open your browser and navigate to: `http://localhost/Manrealstore/setup.php`
2. Fill in the configuration form:
   - **Base URL**: `http://localhost/Manrealstore/`
   - **Database Host**: `localhost`
   - **Database Name**: `manreal_pos`
   - **Username**: `root` (or your MySQL username)
   - **Password**: (your MySQL password)
3. Click "Configure System"

### Step 3: Run Database Migrations
```bash
cd /path/to/Manrealstore
php spark migrate
```

### Step 4: Seed Initial Data
```bash
php spark db:seed UserSeeder
php spark db:seed ProductSeeder
```

### Step 5: Access Your POS System
Navigate to: `http://localhost/Manrealstore/`

## 🔐 Login Credentials

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Admin** | `admin` | `admin123` | Full system access |
| **Cashier** | `cashier1` | `cashier123` | POS and sales only |

## 🎉 You're Ready!

Your POS system is now configured with:
- ✅ User authentication system
- ✅ Sample products (cigarettes, beverages, snacks, etc.)
- ✅ Admin and cashier accounts
- ✅ Database structure ready
- ✅ File upload directories created

## 🚨 Important Security Notes

1. **Change Default Passwords** immediately after first login
2. **Delete setup.php** file: `rm setup.php`
3. **Update encryption key** in production
4. **Configure HTTPS** for production use

## 🔧 First Time Configuration

### 1. Admin Setup
1. Login as `admin` / `admin123`
2. Navigate to Products → Add your inventory
3. Set minimum stock levels
4. Configure store information

### 2. Cashier Setup
1. Login as `cashier1` / `cashier123`
2. Test POS interface
3. Process sample sales
4. Verify inventory updates

### 3. System Configuration
1. Update store branding
2. Configure tax rates
3. Set payment methods
4. Customize receipt format

## 📱 Testing Your System

### Test POS Flow
1. **Start Sale**: Click "New Sale" from dashboard
2. **Add Products**: Search and select items
3. **Process Payment**: Enter payment details
4. **Generate Receipt**: Complete the sale
5. **Verify Inventory**: Check stock levels updated

### Test Reports
1. **Sales Report**: View daily sales data
2. **Inventory Report**: Check stock levels
3. **Dashboard**: Monitor key metrics

## 🚨 Troubleshooting Quick Fixes

### Database Connection Issues
```bash
# Check MySQL service
sudo systemctl status mysql

# Test connection
mysql -u root -p -h localhost
```

### File Permission Issues
```bash
# Fix upload directory permissions
chmod 755 public/uploads/products
chmod 755 writable/
```

### URL Rewriting Issues
```bash
# Enable Apache mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Migration Errors
```bash
# Reset migrations
php spark migrate:rollback
php spark migrate
```

## 📞 Need Help?

- **Documentation**: Check README.md for detailed information
- **CodeIgniter Docs**: [https://codeigniter.com/user_guide/](https://codeigniter.com/user_guide/)
- **Common Issues**: Check troubleshooting section in README

## 🎯 Next Steps

After successful setup:
1. **Customize** store branding and settings
2. **Add Products** to your inventory
3. **Train Staff** on POS operations
4. **Configure** backup procedures
5. **Monitor** system performance

---

**🎉 Congratulations!** Your Manreal Store POS System is now ready for business!

**Pro Tip**: Bookmark this guide for future reference and team training.
