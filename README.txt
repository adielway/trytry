Grading Portal (PHP + MySQL)
===========================

Quick start
-----------
1) Create a MySQL database (e.g., `grading_portal`).
2) Import `init.sql` into that database (via phpMyAdmin).
3) Upload all PHP files to your hosting (public_html folder).
4) Update `config.php` with your database credentials.
5) Visit your domain and log in with demo accounts:

   Teacher: teacher@example.com / password123
   Student: student@example.com / password123
   Parent : parent@example.com  / password123

Roles
-----
- Teacher: can add/delete grades.
- Student: view-only grades for their own account.
- Parent : view-only grades of their linked child.

Notes
-----
- Built with PDO and prepared statements.
- Passwords are hashed (bcrypt). Replace demo users for production.
- Add SSL (HTTPS) in hosting for security.
