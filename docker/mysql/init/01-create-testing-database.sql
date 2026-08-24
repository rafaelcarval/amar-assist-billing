CREATE DATABASE IF NOT EXISTS amar_billing_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES
    ON amar_billing_test.*
    TO 'amar'@'%';

FLUSH PRIVILEGES;