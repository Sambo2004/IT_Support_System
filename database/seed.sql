-- DEVELOPMENT SEED DATA
-- Default password for all seed accounts is: password
-- Password Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

USE `it_support`;

-- 1. Departments
INSERT INTO `departments` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'Information Technology', 'IT Support, Infrastructure & Systems', 1),
(2, 'Human Resources', 'People Operations & Talent', 1),
(3, 'Finance & Accounting', 'Financial Planning & Accounting', 1),
(4, 'Marketing & Sales', 'Brand, Sales & Communications', 1),
(5, 'Operations', 'Logistics & Office Administration', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Ticket Categories
INSERT INTO `ticket_categories` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'Hardware Issue', 'Computer, monitor, printer or peripheral malfunction', 1),
(2, 'Software & Application', 'OS, Office apps, corporate software errors', 1),
(3, 'Network & Internet', 'Wi-Fi, LAN, VPN or internet connectivity', 1),
(4, 'Email & Communication', 'Outlook, email access, Zoom or Teams setup', 1),
(5, 'Access & Security', 'Account login, password reset, permissions', 1),
(6, 'Other Inquiry', 'General questions or IT assistance', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Asset Categories
INSERT INTO `asset_categories` (`id`, `name`, `description`) VALUES
(1, 'Laptop', 'Portable personal computers'),
(2, 'Desktop PC', 'Workstations & towers'),
(3, 'Monitor', 'Computer screens & displays'),
(4, 'Printer / Scanner', 'Network printers & multifunction devices'),
(5, 'Networking Device', 'Routers, switches & access points'),
(6, 'Server', 'Physical & virtual servers'),
(7, 'Other Equipment', 'Peripherals & accessories')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Initial Users (All default password: "password")
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role`, `department_id`, `is_active`) VALUES
(1, 'admin', 'admin@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+855 12 345 678', 'admin', 1, 1),
(2, 'tech1', 'tech1@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Technician', '+855 12 345 679', 'technician', 1, 1),
(3, 'tech2', 'tech2@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Technician', '+855 12 345 680', 'technician', 1, 1),
(4, 'employee1', 'employee1@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Employee', '+855 12 345 681', 'employee', 2, 1),
(5, 'employee2', 'employee2@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Employee', '+855 12 345 682', 'employee', 3, 1),
(6, 'manager1', 'manager1@itsupport.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael Manager', '+855 12 345 683', 'manager', 1, 1)
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- 5. Sample IT Assets
INSERT INTO `assets` (`id`, `asset_tag`, `device_name`, `category_id`, `brand`, `model`, `serial_number`, `purchase_date`, `warranty_expiry`, `assigned_to`, `department_id`, `location`, `status`, `notes`) VALUES
(1, 'AST-2026-001', 'Dell Latitude 5430', 1, 'Dell', 'Latitude 5430', 'SN-DELL-98214', '2025-01-15', '2028-01-15', 4, 2, 'Building A, Floor 2', 'assigned', 'Assigned to Jane Employee'),
(2, 'AST-2026-002', 'ThinkPad T14 Gen 4', 1, 'Lenovo', 'ThinkPad T14', 'SN-LNVO-48172', '2025-03-20', '2028-03-20', 5, 3, 'Building B, Floor 1', 'assigned', 'Assigned to David Employee'),
(3, 'AST-2026-003', 'HP LaserJet Pro M404dn', 4, 'HP', 'LaserJet M404dn', 'SN-HP-11093', '2024-06-10', '2027-06-10', NULL, 1, 'IT Service Room', 'available', 'Shared network printer'),
(4, 'AST-2026-004', 'Cisco Catalyst 2960', 5, 'Cisco', '2960-X', 'SN-CSCO-77291', '2023-11-05', '2026-11-05', NULL, 1, 'Server Room Rack 1', 'available', 'Core switch')
ON DUPLICATE KEY UPDATE `asset_tag`=VALUES(`asset_tag`);

-- 6. Ticket Sequential Counter Seed (Year 2026, 4 sample tickets)
INSERT INTO `ticket_number_seq` (`id`, `year`, `last_number`) VALUES
(1, 2026, 4)
ON DUPLICATE KEY UPDATE `last_number`=VALUES(`last_number`);

-- 7. Sample Tickets
INSERT INTO `tickets` (`id`, `ticket_number`, `title`, `description`, `category_id`, `requester_id`, `department_id`, `assigned_to`, `asset_id`, `priority`, `status`, `resolution`, `resolved_at`, `created_at`) VALUES
(1, 'IT-2026-0001', 'Blue Screen error on startup', 'My Dell laptop keeps displaying BSOD with error CRITICAL_PROCESS_DIED immediately upon booting into Windows.', 1, 4, 2, 2, 1, 'high', 'in_progress', NULL, NULL, NOW() - INTERVAL 2 DAY),
(2, 'IT-2026-0002', 'Cannot connect to Office Wi-Fi', 'My device repeatedly prompts for Wi-Fi credentials but fails with authentication error.', 3, 5, 3, 3, 2, 'medium', 'assigned', NULL, NULL, NOW() - INTERVAL 1 DAY),
(3, 'IT-2026-0003', 'VPN access request for remote work', 'Need Cisco AnyConnect VPN profile setup for remote work access during next week.', 5, 4, 2, 2, 1, 'low', 'resolved', 'VPN profile configured and credentials sent via secure channel. Tested successfully.', NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 3 DAY),
(4, 'IT-2026-0004', 'Main accounting software license expired', 'Sage Accounting displays error code LIC-004: License key expired. Entire finance team blocked.', 2, 5, 3, NULL, NULL, 'critical', 'open', NULL, NULL, NOW() - INTERVAL 2 HOUR)
ON DUPLICATE KEY UPDATE `ticket_number`=VALUES(`ticket_number`);

-- 8. Sample Ticket Comments
INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`, `is_internal`, `created_at`) VALUES
(1, 1, 2, 'Investigating dump files. Looks like a corrupted storage driver or SSD issue.', 1, NOW() - INTERVAL 1 DAY),
(2, 1, 4, 'Thank you John, please let me know if you need to take the laptop for hardware diagnostics.', 0, NOW() - INTERVAL 18 HOUR),
(3, 3, 2, 'VPN access configured with MFA. User verified connection from mobile hotspot.', 0, NOW() - INTERVAL 5 HOUR)
ON DUPLICATE KEY UPDATE `comment`=VALUES(`comment`);

-- 9. Sample Ticket History
INSERT INTO `ticket_history` (`id`, `ticket_id`, `user_id`, `field_changed`, `old_value`, `new_value`, `created_at`) VALUES
(1, 1, 1, 'assigned_to', 'None', 'John Technician', NOW() - INTERVAL 2 DAY),
(2, 1, 2, 'status', 'open', 'in_progress', NOW() - INTERVAL 1 DAY),
(3, 3, 1, 'assigned_to', 'None', 'John Technician', NOW() - INTERVAL 3 DAY),
(4, 3, 2, 'status', 'assigned', 'resolved', NOW() - INTERVAL 5 HOUR)
ON DUPLICATE KEY UPDATE `field_changed`=VALUES(`field_changed`);

