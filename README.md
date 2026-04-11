# 🎯 Employee Leave Management System

A comprehensive, feature-rich leave management application built with **PHP**, **MySQL**, and **HTML/CSS**. This system helps employees apply for leaves, track their leave balance, and provides analytics on leave patterns.

---
# Project Description
A web application that helps employees apply for leaves, track their balance, and allows managers to approve requests. It automatically detects suspicious leave patterns and enforces company leave limits.


## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Database Setup](#database-setup)
- [Folder Structure](#folder-structure)
- [How to Use](#how-to-use)
- [Features in Detail](#features-in-detail)
- [Demo Credentials](#demo-credentials)
- [Troubleshooting](#troubleshooting)
- [Future Enhancements](#future-enhancements)

---

## ✨ Features

### 1. 📊 **Leave Balance Predictor**
- Shows current leave balance
- Predicts remaining leaves after applying
- Shows future leave forecast: "By next month, you'll have X days left"
- Real-time leave balance updates

### 2. 📈 **Leave Pattern Analytics**
- Track total leaves taken
- Identify most frequent leave day (e.g., Mondays)
- Categorize by leave type (Sick, Casual, Earned)
- Visual progress bars for leave usage
- Breakdown table showing leave statistics

### 3. ⚠️ **Suspicious Leave Pattern Detection**
- Flags leaves adjacent to weekends
- Alerts for last-minute applications (< 48 hours)
- Shows warning: "⚠️ Your leave pattern looks suspicious"
- Helps HR identify potential misuse

### 4. ✏️ **Leave Cancellation & Modification**
- Cancel pending or approved leaves
- Cancel functionality with confirmation
- Update leave status in real-time
- Automatic notification generation

### 5. 👥 **Backup Employee Assignment**
- Assign backup employee while applying for leave
- Shows responsibility and teamwork
- Tracks who covers your work
- Visible in leave details

### 6. 📧 **Notification System**
- Simulated email notifications
- "Notification sent to manager" message
- Notifications stored in database
- Track unread/read status

### 7. 🔒 **Leave Type Limits**
- **Sick Leave:** 10 days/year
- **Casual Leave:** 5 days/year
- **Earned Leave:** 20 days/year
- **Maternity Leave:** 6 days/year
- System blocks requests exceeding limits
- Error message: "You've reached your limit!"

### 8. 🔐 **User Authentication**
- Secure login system
- Role-based access (Employee, Manager, Admin)
- Session management
- Password encryption (MD5)

### 9. 📱 **Responsive Design**
- Mobile-friendly interface
- Modern UI with gradient backgrounds
- Easy navigation menu
- Clean, professional styling

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP 7.0+ |
| **Database** | MySQL 5.7+ |
| **Server** | Apache (XAMPP) |
| **IDE** | VS Code / Notepad++ |

---

## 🖥️ System Requirements

- **XAMPP** (Apache + MySQL + PHP)
- **Windows/Mac/Linux**
- **Modern Web Browser** (Chrome, Firefox, Edge)
- **Text Editor** (VS Code, Notepad++)
- **PHP 7.0+**
- **MySQL 5.7+**

---

## 📦 Installation Guide

### Step 1: Download XAMPP
- Download from: https://www.apachefriends.org/
- Install with default settings
- Windows: `C:\xampp`

### Step 2: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Click **START** for:
   - ✅ Apache
   - ✅ MySQL

### Step 3: Create Project Folder
Navigate to: C:\xampp\htdocs
Create folder: myfirstproject


### 2. Create Database
- Open: `http://localhost/phpmyadmin`
- Create database: `leave_management`
- Run schema (provided in schema.sql)

### 3. Copy Files
- Place all `.php` files in respective folders
- Add `database.php` in `config/` folder

### 4. Access
http://localhost/myfirstproject/


## 👤 Demo Login
| Role | Email | Password |
|------|-------|----------|
| Employee | john@company.com | john123 |
| Manager | jane@company.com | jane123 |
| Admin | admin@company.com | admin123 |

🎓 Tech Used
PHP: Backend logic, database queries
MySQL: Data storage, relationships
HTML: Page structure
CSS: Styling & responsive design
JavaScript: Date calculations
🚀 Next Steps
Test all features with demo data
Create custom leave types
Add more employees
Generate reports
Upgrade to bcrypt passwords
