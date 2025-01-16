CREATE DATABASE circle_cash;

USE circle_cash;

CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE transactions2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    month INT,  -- Store month as an integer (1-12)
    year INT,   -- Store year as an integer
    amount DECIMAL(10, 2),
    type ENUM('in', 'out') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id)
);


-- Insert default members
INSERT INTO members (name) VALUES ('Paundra'), ('Stelo'), ('Hiro'), ('Berto');
