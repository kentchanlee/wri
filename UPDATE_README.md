# CEDAR Wellness Hub — combined Superadmin, HR and Employee update

## Upgrade your existing XAMPP installation

1. Back up your current project folder and export the current database in phpMyAdmin.
2. Replace the project files with this `cedar-wellness-hub` folder. Retain your working database credentials and employee uploads. The default folder URL remains `/cedar-wellness-hub`.
3. In phpMyAdmin, select your existing database and import **`database/migration_superadmin_management.sql` once**. This adds HR name fields, branch capacity and structured address fields, and copies existing linked HR names where available. It does not delete records. The migration is not intended for repeated imports. If an import is interrupted, inspect which columns were added before retrying.
4. Start Apache and MySQL. Sign in as Superadmin, open HR Manage, and complete any missing HR names with Edit. Open Branches / Map and complete each old branch's address fields. Old free-text addresses are retained and shown as a reference; they cannot safely be split automatically.
5. Confirm the branch HR capacities and actual service menu before use. Edit `config/services.php` to match Cedar's real offerings. Existing service names already encoded for the HR branch also appear in its dropdown. The starter entries are examples from the original project, not a verified company service list.

For a NEW empty installation only, import `database/cedar_wellness.sql` instead. It already includes the new columns; do not run the migration afterward. **Do not import the full database over an existing installation.**

## What changed

### Superadmin
- HR first and last names now have their own database columns in `users`, separate from employee records. The displayed Name is composed from these saved fields.
- HR Manage supports editing name, username, email and branch assignment, with username/email checks and HR-only action targets.
- New branches default to **one active HR account**. Superadmin can set a branch capacity from 1–100. Creation, reassignment and re-enabling enforce the capacity. Disabled accounts do not consume capacity. The migration retains the number of active HR accounts that already exist at each branch instead of disabling them.
- Branches can be edited, including contact, address and coordinates. Click the map inside the form to place a pin, or drag its marker; editing loads the existing position. Coordinates remain editable when the map cannot load.
- Addresses have separate building/street, barangay, city/municipality, province and four-digit postal-code fields. The combined `address` remains available to existing pages. These are structured fields, not a government geographic-code lookup; geographic name matching still needs business review.
- Branch removal is blocked while employees, HR assignments or bookings still refer to it.

### HR
- Employee Edit now opens correctly: the missing employment-type input caused JavaScript to stop. Department, employment type, personal/contact information and email can be saved. Email changes update both employee and login records together.
- Edit buttons safely handle names with apostrophes. Archive, restore and on-field actions are restricted to the HR branch.
- Scheduling supports multiple employee checkboxes and multiple date checkboxes. Save/cancel applies to the selected combinations in one transaction. No partial batch is kept after a validation failure. Past dates, invalid times, invalid breaks, other-branch employees and work assignments during approved leave are rejected. Existing assignments on the selected dates are replaced deliberately.
- Attendance disables the manual button while a request is pending, limits a session to one accepted request attempt every two seconds, and requires at least 60 seconds between an employee's punches. Employee-row locking serializes concurrent punches; day-off rejections roll back without leaving a Time In. Completed daily attendance cannot be punched again.
- The scanner reports missing libraries, insecure camera contexts and denied cameras with an Employee ID fallback. It tries an available camera if the preferred rear camera cannot start. A short browser cooldown reduces repeated scans; server checks still apply after refresh or from another browser.
- Bookings use a configurable service dropdown. Phone numbers accept 7–15 digits and an optional leading plus, without letters. Dates/times, sources, employees, statuses, duplicates and same-time employee reservations are checked on the server. Existing schedules/breaks and approved leave are checked when assigning a booking. Different-time overlapping services cannot be fully assessed until actual service durations are supplied.

### Employee and shared validation
- Leave dates must be real calendar dates, today or later, and in the correct order. Pending/approved leave cannot overlap. Defaults: maximum **three pending requests**, at least **60 seconds** between submissions. These are initial application rules for Cedar to confirm; they are not statutory leave-entitlement rules.
- The leave end-date picker follows the selected start date. Buttons lock during submission. Invalid leave input is retained for correction.
- Employee schedule/history month inputs are bounded and validated.
- Modified mutation workflows use CSRF checks, prepared statements and server-side field validation. Employee birth dates cannot be in the future, contact fields reject letters, and government-ID fields accept digits with spaces/hyphens.
- Database session timestamps and PHP use Philippine time (UTC+08:00) to keep attendance and cooldown calculations aligned.
- Cedar styling is retained, with scrolling modal forms, visible keyboard focus and narrower-screen form improvements. Employee camera scanning remains removed.

## Validation performed

- All 33 PHP files passed real PHP CLI syntax checks. Final checks used PHP 8.2; PHP 8.5 syntax checks were also performed earlier in this update.
- Seven modified inline JavaScript scripts passed syntax checks.
- 39 workflow/render checks passed using PHP 8.2 and an isolated SQLite test database with a MySQL-SQL translation adapter. These exercised HR capacity/names/editing, branch/address validation, employee/department/email editing, cross-branch rejections, bulk scheduling/cancellation, invalid booking inputs, duplicates, leave dates, overlap, cooldown and pending limits.
- 10 attendance checks passed: manual Time In, QR Time Out, rapid duplicate rejection, completed-day rejection, branch scoping, invalid QR fallback, malformed identifiers, CSRF, day-off rollback and disabled employee rejection.
- Eight DOM behavior checks passed for edit forms, apostrophe names, map pin coordinates with a mocked map, manual coordinate fallback, multi-employee selection, booking phone/service controls, leave date constraints and the pending attendance button lock.

The SQLite adapter was used only for testing. The delivered application still uses its original MySQL PDO connection. These checks do not establish native MySQL locking behavior or camera reliability on a physical device.

## Live XAMPP/MySQL acceptance checks still required

- Import the migration against a BACKUP COPY of your actual database, then verify existing records, old HR names, branches and employee uploads.
- As Superadmin: create/edit HR, move an HR to a full branch, disable/re-enable HR, edit branch capacity, and add/edit/move a map pin. Confirm the real addresses and coordinates.
- As HR: edit an employee's department, email and employment type; sign in using the updated employee email. Confirm another branch's employees remain inaccessible.
- Assign multiple employees across multiple dates, including a noon-only shift with both breaks cleared. Test cancellation and one invalid employee/date in a batch. Verify no partial changes on failure.
- Test concurrent attendance submissions from two independent browsers against MySQL; one employee must receive only one punch inside the cooldown. Verify actual time, breaks, day off, worked hours and reports.
- Test QR badges on the real kiosk camera, with permission allowed/denied, poor lighting and a network outage. Camera capture requires HTTPS or localhost. Leaflet tiles and QR libraries currently require internet access. Manual ID remains the fallback.
- Test booking status changes, the actual service dropdown, duplicate reservations and employee availability. Confirm service durations before relying on overlap detection beyond identical start times.
- Submit invalid/past/reversed/overlapping leave dates, rapid repeats and a fourth pending request from separate sessions. Confirm HR review still works.
- Review the screens on your normal desktop and phone. DOM behavior checks do not replace visual browser/device acceptance testing.

## Business details to gather

Confirm the official branch addresses and pins, active HR capacity for each branch, service names and durations, phone-number conventions, and the acceptable pending-leave/cooldown rules. No survey or external personal-data collection was performed by this update.
