-- SkySoft Bonded Warehouse — MySQL schema
-- Import this via phpMyAdmin in your GoDaddy cPanel (Databases > phpMyAdmin > Import).

CREATE TABLE IF NOT EXISTS bonds (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  bond_no       VARCHAR(30)  NOT NULL UNIQUE,
  airline       VARCHAR(10)  NOT NULL,
  product       VARCHAR(150) NOT NULL,
  qty           INT          NOT NULL DEFAULT 0,
  value         DECIMAL(12,2) NOT NULL DEFAULT 0,
  duty          DECIMAL(12,2) NOT NULL DEFAULT 0,
  bond_date     DATE NOT NULL,
  expiry_date   DATE NOT NULL,
  damage_qty    INT NOT NULL DEFAULT 0,
  shortage_qty  INT NOT NULL DEFAULT 0,
  sample_qty    INT NOT NULL DEFAULT 0,
  shipped_qty   INT NOT NULL DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Every damage / sample / shortage / expiry-change entry, for the Ledger report
CREATE TABLE IF NOT EXISTS bond_entries (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  bond_id       INT NOT NULL,
  entry_type    ENUM('damage','sample','shortage','expiry_change') NOT NULL,
  qty           INT DEFAULT 0,
  entry_date    DATE NOT NULL,
  remark        TEXT,
  created_by    VARCHAR(50),
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bond_id) REFERENCES bonds(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shipping_bills (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  airline       VARCHAR(10),
  flight_no     VARCHAR(20),
  destination   VARCHAR(50),
  bill_date     DATE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shipping_bill_items (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  shipping_bill_id   INT NOT NULL,
  bond_id            INT NOT NULL,
  qty                INT NOT NULL,
  duty_amount        DECIMAL(12,2),
  FOREIGN KEY (shipping_bill_id) REFERENCES shipping_bills(id),
  FOREIGN KEY (bond_id) REFERENCES bonds(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50) UNIQUE NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role           VARCHAR(20) DEFAULT 'operator',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed data matching the sample bonds from the SkySoft manual
INSERT INTO bonds (bond_no, airline, product, qty, value, duty, bond_date, expiry_date) VALUES
('6E/00001/20-21','6E','Black Label 1 Ltr',8,11416,19840,'2021-02-11','2021-08-06'),
('6E/00002/20-21','6E','Majestik White',205,38335,63140,'2021-02-11','2021-07-04'),
('6E/00003/20-21','6E','Majestik Red',166,31042,51128,'2021-02-11','2021-07-04'),
('6E/00004/20-21','6E','Smirnoff Red Mini',208,10192,16848,'2021-02-11','2021-07-04'),
('6E/00005/20-21','6E','J/Walker Double Black 1 Ltr',12,22068,33108,'2021-02-11','2021-07-09'),
('6E/00006/20-21','6E','Black Label 50 ML',192,24576,40512,'2021-03-25','2021-07-04'),
('IX/00003/20-21','IX','Alu.Foil Square Cont.',5400,19495,4741,'2021-02-11','2021-06-06'),
('IX/00004/20-21','IX','Disp. Pillow Cover',4000,12148,4552,'2021-02-11','2021-07-27');

-- To create your first login, run make_password_hash.php (included) with your
-- chosen password, then insert the row it prints, e.g.:
-- INSERT INTO users (username, password_hash, role) VALUES ('admin', '<paste hash here>', 'admin');
