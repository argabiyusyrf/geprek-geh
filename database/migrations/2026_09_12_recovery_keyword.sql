-- Recovery via "kata kunci" akun (reset password tanpa email).
-- Kata kunci disimpan sebagai hash bcrypt (PASSWORD_DEFAULT), bukan plaintext.
ALTER TABLE users ADD COLUMN recovery_keyword varchar(255) NULL AFTER totp_recovery;