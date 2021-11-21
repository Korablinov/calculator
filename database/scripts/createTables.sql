CREATE TABLE banks
(
    id   INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255)                      NOT NULL
);
CREATE TABLE mortgages
(
    id      INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    bank_id INTEGER                           NOT NULL,
    name    VARCHAR(255)                      NOT NULL,
    percent DOUBLE
);
