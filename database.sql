-- OUTSINC Database Schema
-- Outreach Someone In Need Of Change

CREATE DATABASE IF NOT EXISTS outsinc;
USE outsinc;

-- Users table with role-based access
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'outreach', 'provider') NOT NULL DEFAULT 'outreach',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Clients table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    phone VARCHAR(20),
    email VARCHAR(100),
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Intake assessments
CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessed_by INT NOT NULL,
    assessment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Housing Status
    housing_status ENUM('homeless', 'shelter', 'transitional', 'unstable', 'stable') NOT NULL,
    housing_duration VARCHAR(50),
    housing_notes TEXT,
    
    -- Addiction History
    has_addiction BOOLEAN DEFAULT FALSE,
    addiction_types TEXT,
    addiction_duration VARCHAR(50),
    previous_treatment BOOLEAN DEFAULT FALSE,
    treatment_notes TEXT,
    
    -- Employment
    employment_status ENUM('unemployed', 'part_time', 'full_time', 'disabled', 'student') NOT NULL,
    employment_history TEXT,
    employment_barriers TEXT,
    
    -- Mental Health
    mental_health_concerns BOOLEAN DEFAULT FALSE,
    mental_health_diagnosis TEXT,
    currently_in_treatment BOOLEAN DEFAULT FALSE,
    mental_health_notes TEXT,
    
    -- Support Networks
    has_family_support BOOLEAN DEFAULT FALSE,
    support_network_details TEXT,
    
    -- Legal Issues
    has_legal_issues BOOLEAN DEFAULT FALSE,
    legal_issues_details TEXT,
    has_id_documents BOOLEAN DEFAULT FALSE,
    missing_documents TEXT,
    
    -- Daily Living
    transportation_access BOOLEAN DEFAULT FALSE,
    food_security ENUM('secure', 'insecure', 'very_insecure'),
    healthcare_access BOOLEAN DEFAULT FALSE,
    
    -- Overall Assessment
    primary_barriers TEXT,
    immediate_needs TEXT,
    assessment_complete BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id)
);

-- Barriers identified
CREATE TABLE IF NOT EXISTS barriers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    barrier_type ENUM('housing', 'addiction', 'employment', 'mental_health', 'legal', 'documentation', 'transportation', 'healthcare', 'other') NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('identified', 'in_progress', 'resolved', 'cannot_resolve') DEFAULT 'identified',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
);

-- Action plans and recommendations
CREATE TABLE IF NOT EXISTS action_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    barrier_id INT,
    action_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    recommended_resource_id INT,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('recommended', 'in_progress', 'completed', 'cancelled') DEFAULT 'recommended',
    assigned_to INT,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (barrier_id) REFERENCES barriers(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Resources directory
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category ENUM('shelter', 'housing', 'detox', 'rehab', 'mental_health', 'employment', 'legal', 'healthcare', 'food', 'transportation', 'education', 'other') NOT NULL,
    description TEXT,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(200),
    hours_of_operation TEXT,
    eligibility_criteria TEXT,
    services_offered TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Team notes and communication
CREATE TABLE IF NOT EXISTS team_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    note_type ENUM('contact', 'assessment', 'intervention', 'follow_up', 'general') NOT NULL,
    note TEXT NOT NULL,
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Client assignments
CREATE TABLE IF NOT EXISTS client_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('primary', 'secondary', 'support') DEFAULT 'primary',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (client_id, user_id)
);

-- Progress tracking
CREATE TABLE IF NOT EXISTS progress_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    milestone_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    achieved_date DATE NOT NULL,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- System configuration
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@outsinc.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert sample resources
INSERT INTO resources (name, category, description, address, phone, website, services_offered) VALUES
('Hope Shelter', 'shelter', 'Emergency shelter with 50 beds available', '123 Main St, City', '555-0101', 'www.hopeshelter.org', 'Emergency housing, meals, case management'),
('Bright Path Detox', 'detox', 'Medical detox facility', '456 Oak Ave, City', '555-0102', 'www.brightpathdetox.org', 'Medical detox, addiction counseling'),
('Community Mental Health', 'mental_health', 'Mental health services', '789 Pine St, City', '555-0103', 'www.cmhc.org', 'Therapy, psychiatry, crisis intervention'),
('Job Training Center', 'employment', 'Job training and placement', '321 Elm St, City', '555-0104', 'www.jobtrainingcenter.org', 'Job training, resume help, placement services'),
('Legal Aid Society', 'legal', 'Free legal assistance', '654 Maple Dr, City', '555-0105', 'www.legalaid.org', 'Legal counsel, document recovery, court support');

-- Insert default system configuration
INSERT INTO system_config (config_key, config_value, description) VALUES
('app_name', 'OUTSINC', 'Application name'),
('app_tagline', 'Outreach Someone In Need Of Change', 'Application tagline'),
('max_clients_per_user', '50', 'Maximum clients per outreach worker'),
('assessment_validity_days', '90', 'Days before reassessment recommended');
