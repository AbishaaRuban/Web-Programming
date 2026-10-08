ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user';
UPDATE users SET role='admin' WHERE email='abi@college.edu';
