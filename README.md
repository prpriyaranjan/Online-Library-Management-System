📚 The Shiv Library – Library Management System
The Shiv Library is a fully-featured, dynamic Library Management System built using PHP and MySQL, designed to streamline book lending operations and improve the communication between users and administrators. With a modern UI and intuitive design, it offers a powerful local library experience ideal for educational institutions or small-scale libraries.

🌟 Key Features
🔐 Authentication System
User Registration & Login with profile photo upload

Admin Login with secure access and dashboard control

Passwords are hashed using bcrypt for security

🧑‍💼 Admin Panel
Dashboard overview with total users, books, and pending requests

Manage Users (add, edit, remove, send reminders)

Manage Books (add, update, delete, track availability)

Handle Borrow Requests (approve/decline user borrow requests)

Messaging system to communicate with users (real-time chat with profile photos)

Fee Management:

View user payments

Confirm fee status and send messages/emails

Auto-calculate fines after due dates

👨‍🎓 User Dashboard
Personal dashboard with borrowed book history

Book Search and Borrow Request

Return borrowed books

Real-time messaging with admin (chat-style interface)

Monthly fee countdown with fine calculation

Upload payment proof (image/screenshot)

Clear or delete messages (single/multiple/all)

💬 Messaging System
AJAX-powered real-time chat (similar to WhatsApp/Telegram)

Profile photo bubbles next to each message

Right-click to delete individual, multiple, or all messages

💻 UI/UX
Modern, responsive layout with dark mode and glassmorphism

Animated SVG and parallax effects on every page

Optimized for both desktop and mobile devices

🗂️ Technologies Used
Frontend: HTML5, CSS3, JavaScript, AJAX

Backend: PHP (procedural)

Database: MySQL (with mysqli)

Server: XAMPP / WAMP

Security: bcrypt for password hashing

Visuals: SVG animations, parallax effects, custom UI

🗃️ Database Overview
Database Name: TheShivLibrary

Key Tables:

users – stores user and admin details (with role column)

books – stores all book records

borrow_requests – handles borrow/return tracking

messages – stores chat data (user ↔ admin)

fees – tracks monthly payments, fines, due dates

🚀 Getting Started
Install XAMPP or WAMP

Import the TheShivLibrary.sql file into phpMyAdmin

Place the project folder inside /htdocs/

Start Apache & MySQL

Open http://localhost/TheShivLibrary/ in your browser

📎 Project Folder Structure
bash
Copy
Edit
/TheShivLibrary/
├── admin/
├── users/
├── assets/
├── database/
├── messages/
├── books/
├── index.html
├── index_styles.css
└── ...
📌 Notes
Admin and user profile photos are stored in /assets/images/

All message and fee updates are reflected in real-time

Designed for local use; can be adapted for live hosting

🧑‍💻 Developed By
Priyaranjan
An educational and functional project built from scratch to learn full-stack web development with practical PHP-MySQL integration.

