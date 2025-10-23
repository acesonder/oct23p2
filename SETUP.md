# OUTSINC - Quick Setup Guide

## Installation Steps

### 1. Database Setup

First, set up the MySQL database:

```bash
# Login to MySQL
mysql -u root -p

# Create the database and import schema
mysql -u root -p < database.sql
```

Or manually:
```sql
mysql -u root -p
CREATE DATABASE outsinc;
USE outsinc;
SOURCE database.sql;
```

### 2. Configure Database Connection

Edit `/config/database.php` and update if needed:
- Default host: `localhost`
- Default user: `root`
- Default password: (empty)
- Database name: `outsinc`

### 3. Web Server Setup

**Apache:**
- Point DocumentRoot to the application directory
- Ensure `.htaccess` is enabled
- Enable `mod_rewrite`

**Nginx:**
Add this to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 4. File Permissions

Ensure proper permissions:
```bash
chmod -R 755 /path/to/oct23p2
chmod -R 775 /path/to/oct23p2/uploads  # If you add upload functionality
```

### 5. Access the Application

Open your browser and navigate to:
- `http://localhost/` (if running locally)
- `http://your-domain.com/` (if deployed)

### 6. Login

Use the default admin credentials:
- **Username:** `admin`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change the default admin password immediately after first login!

## Post-Installation Configuration

### 1. Create Additional Users
1. Login as admin
2. Go to Admin Panel → User Management
3. Create accounts for your team members (outreach workers and service providers)

### 2. Add Local Resources
1. Go to Admin Panel → Resource Management
2. Add local shelters, clinics, job training centers, legal aid, etc.
3. Include complete contact information and eligibility criteria

### 3. Configure System Settings
1. Go to Admin Panel → System Settings
2. Update application name and tagline if needed
3. Adjust system parameters like max clients per user

### 4. Start Using the System
1. Create client profiles
2. Complete intake assessments
3. Review generated action plans
4. Add team notes and track progress

## Application URLs

- **Landing Page:** `/index.html`
- **Login:** `/modules/auth/login.php`
- **Dashboard:** `/modules/dashboard/index.php`
- **Clients:** `/modules/clients/list.php`
- **Assessments:** `/modules/assessment/list.php`
- **Resources:** `/modules/resources/list.php`
- **Admin Panel:** `/admin/index.php` (admin only)

## Default Database Contents

The installation includes:
- 1 admin user (admin/admin123)
- 5 sample resources (Hope Shelter, Bright Path Detox, Community Mental Health, Job Training Center, Legal Aid Society)
- System configuration defaults

## Troubleshooting

### Database Connection Errors
- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `/config/database.php`
- Ensure database exists: `SHOW DATABASES;`

### Permission Errors
- Check file permissions: `ls -la`
- Ensure web server user has access
- Check SELinux settings if applicable

### PHP Errors
- Check PHP version: `php -v` (requires 7.4+)
- Enable error reporting in development
- Check PHP error logs

### Login Issues
- Clear browser cache and cookies
- Verify database tables exist
- Check user table has admin record

## Security Recommendations

1. **Change Default Credentials:** Immediately update admin password
2. **Use HTTPS:** Enable SSL/TLS for production
3. **Regular Backups:** Schedule automated database backups
4. **Update Regularly:** Keep PHP and MySQL updated
5. **Restrict Access:** Use firewall rules and access controls
6. **Monitor Logs:** Review application and server logs regularly

## Getting Help

For issues or questions:
1. Check the main README.md file
2. Review the admin panel documentation
3. Contact system administrator

## Next Steps

1. ✅ Complete database setup
2. ✅ Login with admin account
3. ✅ Change admin password
4. ✅ Create team user accounts
5. ✅ Add local resources
6. ✅ Create first client profile
7. ✅ Complete first assessment
8. ✅ Review generated action plan

---

**You're ready to start making a difference!** 🎉
