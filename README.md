# Manreal Store POS System

A complete Point of Sale (POS) and Inventory Management System built with CodeIgniter 4 for mini sari-sari stores and small retail businesses.

## 🚀 Features

### Authentication & User Management
- **Secure Login/Logout** with session-based authentication
- **Role-based Access Control**: Admin (full access) and Cashier (POS only)
- **Password Management** with secure hashing
- **User Activity Tracking** with last login timestamps

### Product Management
- **Complete CRUD Operations** for products
- **Product Categories** with organized grouping
- **Inventory Tracking** with real-time stock levels
- **Low Stock Alerts** when quantity < minimum threshold
- **Product Images** support (optional)
- **Barcode/Product Code** management
- **Cost and Selling Price** tracking

### Point of Sale (POS) System
- **Modern POS Interface** with product search and selection
- **Shopping Cart** with real-time calculations
- **Multiple Payment Methods**: Cash, Card, GCash, Maya
- **Automatic Calculations**: Subtotal, Tax, Discounts, Change
- **Real-time Inventory Updates** during sales
- **Customer Information** capture
- **Receipt Generation** with store branding

### Sales Management
- **Complete Sales History** with detailed records
- **Sale Status Tracking**: Completed, Cancelled, Refunded
- **Sales Cancellation** with inventory restoration
- **Payment Method Analysis**
- **Customer Transaction History**

### Inventory Management
- **Real-time Stock Tracking** with automatic updates
- **Inventory Movement Logs** for all transactions
- **Stock Adjustment** capabilities (restock, damaged, returns)
- **Inventory History** with detailed audit trail
- **Low Stock Monitoring** and alerts

### Reporting & Analytics
- **Daily, Weekly, Monthly Sales Reports**
- **Top-selling Products Analysis**
- **Revenue Trends** with Chart.js visualizations
- **Inventory Value Reports**
- **Export Capabilities** (Excel, PDF)
- **Real-time Dashboard** with key metrics

### Security Features
- **CSRF Protection** on all forms
- **Input Validation** and sanitization
- **Session Management** with secure handling
- **Role-based Access Control**
- **Audit Logging** for all critical operations

## 🛠️ Technical Requirements

- **PHP**: 8.0 or higher
- **CodeIgniter**: 4.x
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache/Nginx
- **Extensions**: PHP Extensions (mysqli, json, mbstring)

## 📦 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd Manrealstore
```

### 2. Database Setup
1. Create a new MySQL database named `manreal_pos`
2. Import the database structure using migrations
3. Update database configuration in `env` file

### 3. Environment Configuration
1. Copy `env` to `.env` (if not exists)
2. Update the following settings:
```env
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/Manrealstore/'
database.default.database = manreal_pos
database.default.username = your_username
database.default.password = your_password
encryption.key = 'your-secret-key-here'
```

### 4. Run Database Migrations
```bash
php spark migrate
```

### 5. Seed Initial Data
```bash
php spark db:seed UserSeeder
php spark db:seed ProductSeeder
```

### 6. Create Uploads Directory
```bash
mkdir public/uploads
mkdir public/uploads/products
chmod 755 public/uploads/products
```

### 7. Configure Web Server
#### Apache (.htaccess)
Ensure the `.htaccess` file in the `public` directory is properly configured to remove `index.php` from URLs.

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🔐 Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Full system access

### Cashier Account
- **Username**: `cashier1`
- **Password**: `cashier123`
- **Access**: POS and sales viewing only

## 🚀 Usage Guide

### 1. First Time Setup
1. Access the system: `http://localhost/Manrealstore/`
2. Login with admin credentials
3. Navigate to Products to add your inventory
4. Set minimum stock levels for low stock alerts

### 2. Daily Operations
1. **Cashier Login**: Use cashier account for daily sales
2. **POS Operations**: Process sales through the POS interface
3. **Inventory Check**: Monitor stock levels and low stock alerts
4. **End of Day**: Review daily sales reports

### 3. Administrative Tasks
1. **Product Management**: Add, edit, and manage products
2. **Inventory Control**: Adjust stock levels and track movements
3. **User Management**: Manage cashier accounts
4. **Reports Analysis**: Generate and analyze business reports

### 4. POS Workflow
1. **Start Sale**: Click "New Sale" from dashboard
2. **Add Products**: Search and select products
3. **Process Payment**: Enter payment details and calculate change
4. **Complete Sale**: Generate receipt and update inventory
5. **View History**: Access sales records and reports

## 📊 Database Schema

### Core Tables
- **users**: User accounts and authentication
- **products**: Product catalog and inventory
- **sales**: Sales transactions
- **sale_items**: Individual items in sales
- **inventory_logs**: Inventory movement tracking

### Key Relationships
- Sales → Users (cashier)
- Sales → Sale Items (products sold)
- Products → Inventory Logs (stock changes)
- Users → Inventory Logs (who made changes)

## 🔧 Configuration Options

### Customization
- **Store Information**: Update store name, address, phone in views
- **Currency**: Modify currency symbol in BaseController
- **Tax Rates**: Adjust tax calculations in SalesController
- **Payment Methods**: Add/remove payment options

### Security Settings
- **Session Timeout**: Configure in `app/Config/App.php`
- **Password Policy**: Modify validation rules in UserModel
- **CSRF Protection**: Enabled by default

## 📱 Responsive Design

The system is fully responsive and works on:
- **Desktop Computers**: Full feature access
- **Tablets**: Touch-friendly interface
- **Mobile Devices**: Optimized for small screens
- **POS Terminals**: Dedicated POS interface

## 🚨 Troubleshooting

### Common Issues

#### Database Connection
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database name exists

#### File Permissions
- Ensure `writable/` directory is writable
- Check uploads directory permissions
- Verify log file access

#### URL Rewriting
- Ensure `.htaccess` is properly configured
- Check Apache mod_rewrite is enabled
- Verify baseURL configuration

#### Session Issues
- Check session directory permissions
- Verify encryption key is set
- Clear browser cookies if needed

### Error Logs
- Check `writable/logs/` for detailed error information
- Review PHP error logs for server-level issues
- Monitor database connection logs

## 🔄 Updates and Maintenance

### Regular Maintenance
1. **Database Backups**: Regular database backups
2. **Log Rotation**: Monitor and rotate log files
3. **Security Updates**: Keep CodeIgniter updated
4. **Performance Monitoring**: Monitor system performance

### Backup Procedures
```bash
# Database backup
mysqldump -u username -p manreal_pos > backup_$(date +%Y%m%d).sql

# File backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz public/uploads/
```

## 📈 Performance Optimization

### Database Optimization
- Regular table optimization
- Index management
- Query optimization

### Caching
- Enable CodeIgniter caching
- Session optimization
- Static asset caching

## 🤝 Support and Contributing

### Getting Help
1. Check the troubleshooting section
2. Review CodeIgniter 4 documentation
3. Check GitHub issues for known problems

### Contributing
1. Fork the repository
2. Create a feature branch
3. Submit a pull request
4. Follow coding standards

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- **CodeIgniter Team** for the excellent framework
- **Bootstrap Team** for the responsive UI components
- **Chart.js** for data visualization
- **Bootstrap Icons** for the icon set

## 📞 Contact

For support or questions:
- **Email**: support@manrealstore.com
- **Documentation**: [Project Wiki]
- **Issues**: [GitHub Issues]

---

**Note**: This system is designed for small to medium retail businesses. For enterprise-level deployments, additional security measures and scalability considerations may be required.
