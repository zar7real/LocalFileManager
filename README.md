# 🚀 Modern PHP File Manager

> A sleek, responsive web-based file manager built with PHP, featuring drag-and-drop uploads, inline editing, and a beautiful modern UI.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Responsive](https://img.shields.io/badge/Responsive-Yes-brightgreen.svg)]()
[![Security](https://img.shields.io/badge/Security-Path%20Protection-orange.svg)]()

## ✨ Features

- 🎨 **Modern UI/UX** - Beautiful gradient design with glassmorphism effects
- 📱 **Fully Responsive** - Works perfectly on desktop, tablet, and mobile
- 🔐 **Secure Authentication** - Session-based login system
- 📁 **Complete File Operations** - Create, rename, delete, and edit files/folders
- ⬆️ **Drag & Drop Upload** - Intuitive file uploading with visual feedback
- ✏️ **Inline File Editing** - Edit text files directly in the browser
- 🛡️ **Path Protection** - Prevents directory traversal attacks
- 🔄 **AJAX Operations** - Smooth, no-refresh file operations
- 📊 **File Information** - Size, modification date, and type display
- 🎯 **Breadcrumb Navigation** - Easy directory navigation

## 🖼️ Screenshots

### Login Interface
Clean, modern login with gradient background and glassmorphism effects.

### File Manager Dashboard
Intuitive file browser with drag-and-drop upload and inline editing capabilities.

## 🚀 Quick Start

### Prerequisites

- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Write permissions for the target directory

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/php-file-manager.git
   cd php-file-manager
   ```

2. **Upload to your web server**
   ```bash
   # Copy the file to your web directory
   cp index.php /var/www/html/filemanager.php
   ```

3. **Set permissions**
   ```bash
   # Ensure the web server can write to the files directory
   sudo mkdir -p /var/www/html/files
   sudo chown www-data:www-data /var/www/html/files
   sudo chmod 755 /var/www/html/files
   ```

4. **Access the file manager**
   - Open your browser and navigate to: `http://your-domain.com/filemanager.php`
   - Default credentials: `admin` / `admin`

## ⚙️ Configuration

### Basic Configuration

Edit the configuration section at the top of `index.php`:

```php
// Basic Configuration
define('USERNAME', 'admin');           // Change default username
define('PASSWORD', 'admin');           // Change default password
define('BASE_PATH', '/var/www/html/files'); // Set your files directory
```

### Advanced Configuration

#### Custom File Directory

```php
// Example: Set custom upload directory
define('BASE_PATH', '/home/user/documents');

// Example: Use relative path
define('BASE_PATH', __DIR__ . '/uploads');
```

#### Security Settings

```php
// Add custom session timeout (in seconds)
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

// Add HTTPS redirect (recommended for production)
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit();
}
```

### Environment-Specific Setup

#### Development Environment

```php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use local directory
define('BASE_PATH', __DIR__ . '/dev-files');
```

#### Production Environment

```php
// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Use secure directory outside web root
define('BASE_PATH', '/var/secure-files');

// Add IP restriction (optional)
$allowed_ips = ['192.168.1.100', '10.0.0.5'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    http_response_code(403);
    exit('Access Denied');
}
```

## 🔐 Security Considerations

### Important Security Notes

⚠️ **This file manager provides full file system access. Use with caution!**

### Recommended Security Measures

1. **Change Default Credentials**
   ```php
   define('USERNAME', 'your-secure-username');
   define('PASSWORD', 'your-strong-password');
   ```

2. **Use HTTPS in Production**
   - Always use SSL/TLS encryption
   - Consider HTTP Strict Transport Security (HSTS)

3. **Restrict Access by IP**
   ```php
   $allowed_ips = ['your.ip.address'];
   if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
       exit('Access Denied');
   }
   ```

4. **Set Proper File Permissions**
   ```bash
   # Restrict access to the PHP file
   chmod 600 index.php
   
   # Set appropriate directory permissions
   chmod 755 /path/to/files/directory
   ```

5. **Use Outside Web Root**
   ```php
   // Store files outside publicly accessible directory
   define('BASE_PATH', '/home/user/secure-files');
   ```

## 📖 Usage Guide

### Basic Operations

#### Logging In
1. Navigate to the file manager URL
2. Enter your username and password
3. Click "Accedi" (Login)

#### Uploading Files
1. Click "📤 Carica File" (Upload Files)
2. **Drag and drop** files onto the upload area, or
3. **Click** the upload area to browse and select files
4. Files will upload automatically

#### Creating Folders
1. Click "📁 Nuova Cartella" (New Folder)
2. Enter the folder name
3. Click "Crea Cartella" (Create Folder)

#### Editing Files
1. Click on any text file name
2. Edit content in the popup editor
3. Click "💾 Salva" (Save) to save changes

#### File Operations
- **Rename**: Click the ✏️ icon next to any file/folder
- **Delete**: Click the 🗑️ icon next to any file/folder
- **Navigate**: Click folder names to enter directories

### Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Refresh | `F5` or `Ctrl+R` |
| Close Modal | `Esc` |

## 🛠️ Customization

### Styling

The file manager uses modern CSS with:
- CSS Grid and Flexbox layouts
- CSS custom properties (variables)
- Responsive design principles
- Smooth animations and transitions

#### Custom Colors

```css
:root {
  --primary-gradient: linear-gradient(135deg, #your-color1, #your-color2);
  --success-color: #your-success-color;
  --danger-color: #your-danger-color;
}
```

#### Custom Branding

```php
// Change the title and branding
<title>Your Company - File Manager</title>
<h1>🏢 Your Company Files</h1>
```

### Adding File Type Icons

```javascript
// Extend the file icon system
function getFileIcon(fileName) {
    const ext = fileName.split('.').pop().toLowerCase();
    const icons = {
        'pdf': '📄',
        'doc': '📝',
        'docx': '📝',
        'xls': '📊',
        'xlsx': '📊',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️',
        'gif': '🖼️'
    };
    return icons[ext] || '📄';
}
```

## 🔧 Troubleshooting

### Common Issues

#### Permission Denied Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data /path/to/files
sudo chmod -R 755 /path/to/files
```

#### Upload Failures
```php
// Check PHP upload settings in php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

#### Session Issues
```php
// Ensure session directory is writable
session_save_path('/tmp');
```

### Debug Mode

Enable debug mode for troubleshooting:

```php
// Add at the top of index.php
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log actions
    error_log("File Manager Action: " . $_POST['action'] ?? 'view');
}
```

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 7.0+
- **Memory**: 64MB
- **Disk Space**: 10MB (excluding stored files)
- **Extensions**: session, json

### Recommended Requirements
- **PHP**: 8.0+
- **Memory**: 128MB+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: fileinfo, mbstring

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable names
- Comment complex logic
- Test across different PHP versions

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- 📧 **Email**: support@yourcompany.com
- 💬 **Issues**: [GitHub Issues](https://github.com/zar7real/LocalFileManager/issues)
- 📖 **Wiki**: [Project Wiki](https://github.com/zar7real/LocalFileManager/wiki)

## 🙏 Acknowledgments

- Icons from Unicode Emoji
- Inspiration from modern file managers
- Built with ❤️ for the PHP community

---

<div align="center">

**⭐ Star this repository if you found it helpful!**

[Report Bug](https://github.com/zar7real/LocalFileManager/issues) · [Request Feature](https://github.com/zar7real/LocalFileManager/issues) · [View Demo](https://your-demo-site.com)

</div>
