-- ---------------------------------------------------------------------------
-- Secondary database used by `make test-mariadb`, so running the test suite
-- against the real engine never touches the development data.
-- Executed by the MariaDB entrypoint on the first initialisation of the volume.
-- ---------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS prex_gifs_test
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON prex_gifs_test.* TO 'prex'@'%';

FLUSH PRIVILEGES;
