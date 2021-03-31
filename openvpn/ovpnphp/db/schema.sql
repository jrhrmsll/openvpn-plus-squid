CREATE TABLE IF NOT EXISTS certs (
    cert_id INTEGER PRIMARY KEY,
    serial INTEGER NOT NULL,
    common_name TEXT NOT NULL,
    subject TEXT NOT NULL,
    cert_type TEXT NOT NULL,
    status TEXT NOT NULL,
    start_date TEXT NOT NULL,
    expiry_date TEXT NOT NULL,  
    revoked_at TEXT
);