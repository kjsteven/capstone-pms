# Application Logs Directory

This directory contains log files for the Property Management System.

## Files

- `php_errors.log`: Contains PHP errors and exceptions
- Other log files may be created by the application as needed

## Security Note

This directory is protected by an .htaccess file that denies direct access to log files.
DO NOT remove the .htaccess file as it could expose sensitive information.

## Log Maintenance

Log files should be periodically rotated or cleaned to prevent excessive disk usage.
