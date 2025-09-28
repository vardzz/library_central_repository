CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255),
  publisher VARCHAR(255),
  year_published YEAR,
  isbn VARCHAR(20),
  copies INT DEFAULT 1,
  available INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE IF NOT EXISTS borrowings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  borrower_name VARCHAR(255) NOT NULL,
  borrowed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  due_date DATE,
  returned_at DATETIME NULL,
  status ENUM('borrowed','returned') DEFAULT 'borrowed',
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);