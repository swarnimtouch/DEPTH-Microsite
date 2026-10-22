# DEPTH Microsite

## Registration email (Gmail SMTP)

Add these values to `.env` (use a Google App Password, not the normal account password):

```dotenv
MAIL_MAILER=smtp
MAIL_ENABLED=false
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_REQUIRE_TLS=true
MAIL_USERNAME=info@onealphamed.com
MAIL_PASSWORD="your-google-app-password"
MAIL_FROM_ADDRESS=info@onealphamed.com
MAIL_FROM_NAME="DEPTH"
```

After changing `.env`, run `php artisan config:clear`.

Keep `MAIL_ENABLED=false` until outgoing client emails are approved. Change it
to `true` later to activate the configured registration and reminder emails.
