# OUTSINC - Outreach Someone In Need Of Change

A comprehensive web application for outreach teams to connect, assess, and support individuals in need. Built with PHP, MySQL, HTML, CSS, and JavaScript with AJAX functionality.

## Features

### 🔐 User Authentication & Role Management
- Secure login system with password hashing
- Three user roles: **Admin**, **Outreach Worker**, **Service Provider**
- Role-based access control ensuring data privacy

### 👤 Client Profile Management
- Create and manage client profiles
- Capture demographics, contact information
- Track emergency contacts
- View client history and timeline

### 📋 Comprehensive Intake Assessment
Multi-section questionnaire covering:
- **Housing Status**: Current living situation and duration
- **Addiction History**: Substance use, treatment history
- **Employment**: Current status, barriers, history
- **Mental Health**: Diagnoses, treatment status
- **Support Networks**: Family and social connections
- **Legal Issues**: Active cases, documentation status
- **Daily Living**: Transportation, food security, healthcare access

### 🎯 Progress Tracking & Barrier Identification
- Automated barrier identification from assessment data
- Priority-based barrier categorization (Low, Medium, High, Critical)
- Track barrier resolution status
- Visual progress indicators

### 💡 Solution Recommendation Engine
- AI-powered action plan generation based on assessment
- Tailored recommendations for each barrier type
- Links to available local resources
- Priority-based action items

### 💬 Team Communication Dashboard
- Centralized interface for team collaboration
- Add and view team notes on client cases
- Note categories: Contact, Assessment, Intervention, Follow-up, General
- Real-time updates with AJAX

### 📈 Client History & Outcome Tracking
- Longitudinal assessment records
- Progress milestones tracking
- Intervention history
- Outcome measurement

### 🏢 Resource Directory
- Searchable database of local services
- Categories: Shelter, Housing, Detox, Rehab, Mental Health, Employment, Legal Aid, Healthcare, Food, Transportation, Education
- Detailed resource information including contact details, hours, eligibility criteria
- Filter by category and search functionality

### ⚙️ Comprehensive Admin Panel
- User management (create, activate/deactivate users)
- Resource management (add, edit resources)
- System reports and analytics
- Configuration settings
- System setup status overview
- Future enhancement roadmap

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **AJAX**: Asynchronous data updates
- **Design**: Modern, responsive CSS with gradient backgrounds and card-based layouts

## Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd oct23p2
   ```

2. **Configure the database**
   - Update database credentials in `/config/database.php` if needed
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: outsinc

3. **Import the database schema**
   ```bash
   mysql -u root -p < database.sql
   ```
   
   Or manually:
   ```sql
   mysql -u root -p
   CREATE DATABASE outsinc;
   USE outsinc;
   SOURCE database.sql;
   ```

4. **Configure your web server**
   - Point document root to the application directory
   - Ensure PHP is enabled
   - Enable mod_rewrite if using Apache

5. **Access the application**
   - Navigate to your configured domain or localhost
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

## Project Structure

```
oct23p2/
├── admin/                  # Admin panel pages
│   ├── index.php          # Admin dashboard
│   ├── users.php          # User management
│   ├── resources.php      # Resource management
│   ├── reports.php        # Reports & analytics
│   └── settings.php       # System settings
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── client.js      # Client-side JavaScript
├── config/                # Configuration files
│   └── database.php       # Database connection
├── includes/              # Shared PHP includes
│   ├── session.php        # Session management
│   ├── functions.php      # Utility functions
│   └── sidebar.php        # Sidebar navigation
├── modules/               # Application modules
│   ├── auth/              # Authentication
│   │   ├── login.php
│   │   └── logout.php
│   ├── clients/           # Client management
│   │   ├── list.php
│   │   ├── create.php
│   │   ├── view.php
│   │   └── add_note.php
│   ├── assessment/        # Assessment module
│   │   ├── list.php
│   │   ├── create.php
│   │   └── view.php
│   ├── resources/         # Resource directory
│   │   └── list.php
│   └── dashboard/         # Main dashboard
│       └── index.php
├── database.sql           # Database schema
├── index.html            # Landing page
└── README.md             # This file
```

## User Roles

### Administrator
- Full system access
- User management
- Resource management
- System configuration
- View all clients and assessments
- Access to reports and analytics

### Outreach Worker
- Create and manage assigned clients
- Complete assessments
- Add team notes
- View resources
- Track action plans

### Service Provider
- View assigned clients
- Add team notes
- View assessments
- Access resource directory

## Key Features Explained

### Barrier Identification
The system automatically identifies barriers from assessment responses:
- Unstable housing situations
- Active addiction without treatment
- Unemployment
- Untreated mental health concerns
- Missing identification documents
- Lack of transportation or healthcare access
- Active legal issues

### Action Plan Generation
Based on identified barriers, the system generates tailored recommendations:
- Emergency shelter connections for homeless clients
- Detox and rehab referrals for addiction
- Job training enrollment for unemployed
- Mental health clinic referrals
- Legal aid for document recovery
- Transportation vouchers
- Healthcare program enrollment

### Team Communication
- Real-time note addition with AJAX
- Note categorization for easy filtering
- User attribution and timestamps
- Visible to all team members working with the client

## Security Features

- Password hashing with bcrypt
- Session-based authentication
- Role-based access control
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF token implementation

## Future Enhancements

The admin panel includes a comprehensive roadmap for future features:
- Mobile app for field workers
- Email/SMS notifications
- Advanced analytics and predictive modeling
- External system integrations
- Document management
- Appointment scheduling
- Real-time messaging
- Goal tracking
- Custom report builder
- Two-factor authentication
- Multi-language support
- AI-powered barrier prediction
- Client self-service portal

## Support

For issues, questions, or contributions, please contact the system administrator.

## License

Copyright © 2024 OUTSINC. All rights reserved.

---

**Making a difference, one person at a time.** 🤝