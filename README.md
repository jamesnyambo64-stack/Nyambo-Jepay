# Nyambo-Jepay

Authentication module with OTP SMS verification (PHP + MySQL)

Setup

1. Create the database and tables:
   - Import sql-schema.sql into your MySQL server (e.g., via mysql client or phpMyAdmin).

2. Configure environment variables (recommended) or edit db.php to set DB credentials:
   - DB_HOST, DB_NAME, DB_USER, DB_PASS

3. Configure Twilio (or another SMS provider):
   - Set TW_SID, TW_TOKEN, TW_FROM environment variables for Twilio.
   - Or replace send_sms_twilio in sms.php with your provider's SDK/call.

4. Place the files in your web root and visit register.php to create a user.

Security notes

- Use HTTPS in production.
- Store secrets in environment variables, not in source.
- Rate-limit OTP requests and login attempts.

