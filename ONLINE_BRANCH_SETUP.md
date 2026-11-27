# CEDAR Wellness Hub — Online multi-branch setup

## What this update prepares

The app can now read database connection settings from environment variables while keeping the existing XAMPP defaults. This allows every branch to use one deployed PHP application and one central MySQL database. Existing tables already store branch IDs for employees, bookings, schedules (through employees), and HR branch assignments.

**Important:** this code change does not publish the app to the internet or create a hosting account. Online branch communication begins only after deployment to a reachable HTTPS web host and importing the database there.

## Recommended deployment architecture

1. Deploy this PHP project to a PHP 8+ hosting environment with PDO MySQL enabled and HTTPS/TLS enabled.
2. Create a MySQL database on the host and import `database/cedar_wellness.sql` using the hosting control panel/phpMyAdmin.
3. Configure the following environment variables in the hosting control panel:
   - `CWH_DB_HOST` — database hostname provided by the host
   - `CWH_DB_PORT` — usually `3306`
   - `CWH_DB_NAME` — production database name
   - `CWH_DB_USER` — dedicated database user with access only to this database
   - `CWH_DB_PASS` — strong database password
   - `CWH_BASE_URL` — `/cedar-wellness-hub` if installed in that folder, or an empty value if installed at the domain root
4. Run the seed/setup process only as documented and remove any one-time setup script immediately after use. Change all sample/default passwords before real use.
5. Sign in from each branch using the same HTTPS URL. Do **not** install a separate independent database at each branch if shared live records are required.
6. Add branch records under **Branches**, assign each employee to the correct branch, and assign HR users to their permitted branch.
7. Verify that a booking entered at Branch A appears at Branch B and that attendance/schedule updates are visible after refreshing the page.

## Security checklist before real use

- Use HTTPS only; do not expose MySQL port 3306 to the public internet.
- Permit database connections only from the web host/private network when possible.
- Use a dedicated least-privilege database user, not MySQL `root`.
- Set strong unique passwords; disable/remove sample accounts and test data.
- Keep database backups encrypted and access-restricted; test restoring a backup.
- Restrict hosting file permissions and prevent public access to SQL backups, uploads that are not meant to be public, and configuration files.
- Confirm server-side branch authorization for every HR action before production use. A branch assignment shown in the UI is not, by itself, sufficient access control.
- Use a staging copy for tests before applying database changes to production.

## Local XAMPP development

If no environment variables are configured, the app continues to use `localhost`, database `cedar_wellness_hub`, user `root`, and a blank password as before. Start Apache and MySQL, then import `database/cedar_wellness.sql` through phpMyAdmin.

## Validation still required

This package can be checked for PHP syntax locally, but the live connection and cross-branch data flow cannot be certified until production hosting credentials and a reachable database are configured. Test using separate branch accounts and verify access boundaries before launch.
