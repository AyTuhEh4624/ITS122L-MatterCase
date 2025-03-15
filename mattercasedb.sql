-- Drop the database if it exists
DROP DATABASE IF EXISTS mattercase;

-- Create the database
CREATE DATABASE mattercase;
USE mattercase;

-- Set SQL mode and transaction settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Users Table: Stores system users (lawyers, admin, etc.)
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usertype INT(1) NOT NULL,  -- 0: Admin, 1: Partner, 2: Lawyer, 3: Paralegal, 4: Messenger
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(150) NOT NULL,
    pass VARCHAR(150) NOT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Matters Table: Contains legal matters, each having multiple cases
CREATE TABLE matters (
    matter_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Open', 'Closed', 'Pending') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Clients Table: Clients can optionally be related to a matter
CREATE TABLE clients (
    client_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL UNIQUE,
    address TEXT DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Junction Table: client_matters
CREATE TABLE client_matters (
    client_id INT(11) NOT NULL,
    matter_id INT(11) NOT NULL,
    PRIMARY KEY (client_id, matter_id),
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (matter_id) REFERENCES matters(matter_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Cases Table: Each case belongs to a specific client and matter
CREATE TABLE cases (
    case_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    matter_id INT(11) NOT NULL,
    client_id INT(11) NOT NULL,
    case_title VARCHAR(255) NOT NULL,
    court VARCHAR(255),
    case_type VARCHAR(100),
    status ENUM('Active', 'Dismissed', 'Closed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (matter_id),
    INDEX (client_id),
    FOREIGN KEY (matter_id) REFERENCES matters(matter_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Forms Table: Stores submitted forms related to cases
CREATE TABLE forms (
    form_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    case_id INT(11) NOT NULL,
    form_title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    submission_status ENUM('Submitted', 'Pending', 'Rejected') DEFAULT 'Pending',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (case_id),
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Case Lawyers Table (Many-to-Many Relationship)
CREATE TABLE case_lawyers (
    case_id INT(11) NOT NULL,
    lawyer_id INT(11) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (case_id, lawyer_id),
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Case Updates Table: Logs updates on each case
CREATE TABLE case_updates (
    update_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    case_id INT(11) NOT NULL,
    update_text TEXT NOT NULL,
    updated_by INT(11),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (case_id),
    INDEX (updated_by),
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Case Fees Table: Tracks case-related fees
CREATE TABLE case_fees (
    fee_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    case_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    fee_description TEXT,
    payment_status ENUM('Unpaid', 'Paid', 'Overdue') DEFAULT 'Unpaid',
    due_date DATE,
    INDEX (case_id),
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Invoices Table: Tracks invoices related to cases and clients
CREATE TABLE invoices (
    invoice_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    case_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    INDEX (client_id),
    INDEX (case_id),
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Evidence Table: Stores evidence files for cases
CREATE TABLE evidence (
    evidence_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    case_id INT(11) NOT NULL,
    evidence_type VARCHAR(255),
    file_path VARCHAR(255),
    description TEXT,
    submission_status ENUM('Submitted', 'Pending', 'Rejected') DEFAULT 'Pending',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (case_id),
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Audit Log Table: Keeps track of user actions
CREATE TABLE audit_log (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    action TEXT NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- test admin
-- email: admin@email.com
-- password: password
INSERT INTO users (id, usertype, email, pass)
VALUES (1, 0, 'RDFAhvI7KF2y4RH6OPZJZGJLY0pxS2JvNFRPZS82THB3WUYwWVE9PQ==', '+lFCT9HtHdx4AwBuhiWSNkhqbWRJRUFCTVgvcHlIQjFKek9BZFE9PQ==');

COMMIT;