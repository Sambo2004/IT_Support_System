# IT Support Management System

A centralized enterprise IT Service Desk and Hardware Inventory Management System developed in PHP 8 and MariaDB/MySQL.

---

## 1. System Overview & Features

### Core Functionality
- **Role-Based Access Control (RBAC)**: Distinct permissions for `Admin`, `Technician`, `Employee`, and `Manager`.
- **Incident & Ticket Lifecycle Management**:
  - Sequential concurrent-safe ticket numbers (`IT-YYYY-NNNN`).
  - Strict status transitions (`Open` → `Assigned` → `In Progress` → `Pending` → `Resolved` → `Closed`).
  - Priority levels (`Low`, `Medium`, `High`, `Critical`).
  - SLA tracking and indicators (Critical: 4h, High: 8h, Medium: 24h, Low: 72h).
  - Public conversations and internal private work notes.
  - Complete change audit trail.
- **Secure Ticket File Attachments**:
  - Whitelist-validated file uploads (images, PDFs, documents, spreadsheets, zip archives).
  - Randomized storage filenames in protected `storage/attachments/` with `.htaccess` execution blocking.
  - Role-gated streaming downloads via `readfile()`.
- **IT Asset & Inventory Management**:
  - Equipment records (Laptops, Desktops, Monitors, Network equipment, Printers).
  - Serial numbers, warranty tracking, and assignment to employees.
  - Direct ticket association with problematic hardware.
- **Service Desk Analytics & Reporting**:
  - Tickets by Status, Priority, Category, and Department.
  - Technician resolution velocity and performance tracking.
  - Formula-safe CSV export with UTF-8 support.
- **Security Engineering**:
  - Bcrypt password hashing (`PASSWORD_BCRYPT`).
  - PDO prepared statements across 100% of queries (SQL Injection prevention).
  - CSRF tokens on all POST/mutating requests.
  - Session fixation hardening and session regeneration on authentication.
  - Brute-force rate limiting (temporary account lockout after 5 failed attempts).
  - Contextual XSS sanitization (`e()` helper).
  - Directory traversal and direct file download prevention.

---

## 2. Requirements & Prerequisites

- **Web Server**: Apache 2.4+ (bundled with XAMPP)
- **PHP**: PHP 8.0+ (PDO, mbstring, session, openssl extensions enabled)
- **Database**: MySQL 5.7+ or MariaDB 10.4+
- **Browser**: Modern web browser (Chrome, Edge, Firefox)

---

## 3. Installation Guide (Windows / XAMPP)

### Option A: Web-Based GUI Installer (Recommended)
1. Start **Apache** and **MySQL** in XAMPP.
2. Open your browser and navigate to:
   ```text
   http://localhost/IT_Support_System/install.php
   ```
3. Fill in your MySQL credentials (default XAMPP: host `localhost`, user `root`, no password).
4. Configure your primary Administrator account.
5. Click **Start Installation & Initialize System**.

### Option B: Manual CLI Setup
1. Start **Apache** and **MySQL** in XAMPP.
2. Run database initialization commands:
   ```powershell
   & 'C:\xampp\mysql\bin\mysql.exe' -u root -e "source C:/xampp/htdocs/IT_Support_System/database/schema.sql;"
   & 'C:\xampp\mysql\bin\mysql.exe' -u root -e "source C:/xampp/htdocs/IT_Support_System/database/seed.sql;"
   ```
3. Copy or edit `.env` with your database credentials.

---

## 4. Automated Test Suite

Run the full automated test suite anytime using the XAMPP PHP CLI:
```powershell
& 'C:\xampp\php\php.exe' tests/run_tests.php
```
Test coverage includes:
- Database connectivity & MariaDB version
- Bcrypt authentication & account lockout rules
- Concurrency-safe sequential ticket numbering (`IT-YYYY-NNNN`)
- Full ticket lifecycle & state machine transitions
- IT Asset inventory CRUD & associations
- CSRF verification & formula injection escaping
- File attachment metadata & `.htaccess` storage protection
- User profile self-service & persistent updates
- Storage directory write permissions & runtime checks

**Result:** `48 run | 48 passed | 0 failed`

---

## 5. Default Seed Accounts

All default test accounts share the initial password: `password`

| Role | Username | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin` | `password` | Full system control, users, assets, all tickets, settings |
| **IT Technician** | `tech1` | `password` | Assigned ticket handling, internal notes, asset maintenance |
| **IT Technician** | `tech2` | `password` | Secondary technician workload demonstration |
| **Employee** | `employee1` | `password` | Submit requests, track status, add user feedback |
| **Manager** | `manager1` | `password` | Department oversight, reports, resolution analytics |

> ⚠️ **Important**: In production, change all default passwords via **Profile → Change Password**.

---

## 6. Accessing the Application

Open your browser and navigate to:
```text
http://localhost/IT_Support_System/public/
```
(Or directly to `http://localhost/IT_Support_System/` via Apache mod_rewrite).

---

## 6. Verification & Demonstration Steps

1. **Log in as Employee** (`employee1` / `password`):
   - Click **New Ticket** to submit an incident.
   - Observe the automatic ticket number generation (e.g. `IT-2026-0005`).
2. **Log in as Admin** (`admin` / `password`):
   - View the new ticket on the **Dashboard**.
   - Assign the ticket to technician `tech1`.
3. **Log in as Technician** (`tech1` / `password`):
   - Open the ticket from the list.
   - Add an **Internal Note** explaining diagnostic findings.
   - Transition status to **In Progress**, then **Resolved** with resolution notes.
4. **Log in as Manager** (`manager1` / `password`):
   - Navigate to **Reports**.
   - View ticket metrics by category and technician resolution velocity.
   - Click **Export to CSV** to download the audited dataset.

