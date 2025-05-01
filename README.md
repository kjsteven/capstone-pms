# PropertyWise - Property Information Management System

![PropertyWise Logo](images/logo.png)

## Overview

PropertyWise is a comprehensive Property Information Management System designed to streamline and simplify the management of rental properties. This web-based platform provides a user-friendly interface for both property administrators and tenants, facilitating efficient communication, payment processing, maintenance management, and more.

## Features

### For Administrators
- **Dashboard Analytics**: Get a comprehensive overview of property occupancy, rental income, pending maintenance requests, and recent activities.
- **Property Management**: Add, edit, and archive property units with details including unit number, type, square footage, and monthly rent.
- **Tenant Management**: Process new tenants, manage existing contracts, and handle unit turnovers.
- **Maintenance Tracking**: Assign staff to maintenance requests, track resolution status, and maintain service records.
- **Payment Processing**: Record and verify rent payments, generate invoices, and monitor outstanding balances.
- **KYC Verification**: Verify tenant identities through a robust Know Your Customer (KYC) process.
- **Report Generation**: Create and export customizable reports on occupancy, payments, and maintenance.
- **Staff Management**: Add and manage maintenance staff and their assignments.
- **Contract Management**: Upload, store, and track rental contracts.

### For Tenants
- **Profile Management**: Update personal information and view rental history.
- **Unit Information**: Access details about rented units, including lease dates and payment history.
- **Maintenance Requests**: Submit and track the status of maintenance issues.
- **Online Payments**: Make secure online rent payments through integrated payment methods.
- **Reservation System**: Reserve available properties for future occupancy.
- **Document Access**: Download rental contracts and payment receipts.
- **Notifications**: Receive important updates about payments, maintenance, and other activities.

## Technical Details

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Installation
1. Clone the repository:
   ```
   git clone https://github.com/kjsteven/capstone-pms.git
   ```
2. Import the database file from `/database/capstone.sql`
3. Configure database connection in `/config/config.php`
4. Update file permissions for upload directories
5. Access the system via your web browser

### Directory Structure
- `/admin` - Administrator panel and functionality
- `/asset` - Static assets and policy pages
- `/authentication` - Login, signup and authentication management
- `/config` - Configuration files
- `/database` - Database SQL files
- `/images` - System images and uploads
- `/logs` - System logs
- `/notification` - Notification handlers
- `/reports` - Generated reports
- `/session` - Session management
- `/staff` - Staff portal functionality
- `/uploads` - User uploaded files
- `/users` - Tenant portal functionality
- `/utils` - Utility functions and helpers

## Security Features
- Secure session management
- Password encryption
- Two-factor authentication (OTP)
- Input validation
- Role-based access control
- Activity logging and audit trails

## Technologies Used
- PHP
- MySQL
- Tailwind CSS
- JavaScript
- Feather Icons
- Chart.js (for analytics)
- Toastify (for notifications)
- Flatpickr (for date selection)

## License
© 2025 PropertyWise. All rights reserved.

## Support
For support inquiries, please contact [support@propertywise.com](mailto:support@propertywise.com)
