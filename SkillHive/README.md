# SkillHive - setup

1. Install XAMPP. Start Apache and MySQL in the XAMPP Control Panel.
2. Extract this zip so the folder is C:\xampp\htdocs\SkillHive (backend, frontend, database inside).
3. Open http://localhost/phpmyadmin, click Import, choose database/skillhive.sql, click Go. (Skip if your skillhive database already exists.)
4. Click the skillhive database, open the SQL tab, and run database/add_admin.sql. If it says the column "role" already exists, run only the UPDATE line.
5. Open http://localhost/SkillHive/frontend/login.html
   Demo login: priya@college.edu / password123
6. Admin page: http://localhost/SkillHive/frontend/admin.html (admin accounts only)
