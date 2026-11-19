# Home Movers and Cleaners

A Laravel 13 service website and lightweight CMS for moving, fumigation and home cleaning services in Kenya. The site uses Blade, Vite and Tailwind CSS with SQLite for local development and supports MySQL in deployment.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js and npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install --ignore-scripts
npm run build
php artisan serve
```

Set `DB_CONNECTION=sqlite` in `.env` for local development. For MySQL, set `DB_CONNECTION=mysql` and configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`. Never commit `.env` or production secrets.

## Website and CMS features

- Responsive home, about, service overview and detail, pricing, gallery, booking, contact, privacy, terms, and sitemap pages.
- Homepage section copy, visibility, order, and photos are editable through the admin CMS.
- Service descriptions, service FAQs, package pricing, gallery captions, social settings, footer links, and site metadata are editable in the CMS.
- The media library accepts image uploads, creates smaller WebP copies when PHP GD is available, tracks usage, and protects in-use files from deletion.
- Customer testimonials can be added and published by an editor. The public section stays hidden until genuine feedback is added and approved for publication; the CMS records when permission was confirmed.
- Legal pages are drafts by default, carry a review notice, are marked `noindex`, and stay out of the sitemap until an administrator publishes them.
- Contact enquiries are stored, rate limited, searchable, exportable, and can be assigned an internal status and notes. A honeypot field helps deter automated submissions.

## Booking and administration

- The booking wizard uses server-checked availability in `Africa/Nairobi`, a duplicate-request token, and a booking reference. New requests stay pending until the team confirms them.
- Administrators can set working days and hours, slot interval and capacity, lead time, blocked dates, and service durations in **Admin > Bookings**.
- Booking management includes day, week and month calendars, search and filters, status changes, rescheduling checks, internal notes, status history, and CSV export.
- Customer status pages use signed links and are not indexed. Booking and enquiry email templates use the brand colors and logo.
- Admin roles are Super Admin, Administrator, Booking Manager, and Content Editor. Role assignments are managed in **Admin > Staff Access** for existing user accounts.
- The dashboard shows database-backed booking totals, monthly trends, service breakdowns, and upcoming confirmed appointments.

## Initial administrator and staff

Create an administrator interactively; the password is entered privately and must be at least 12 characters:

```powershell
php artisan app:create-admin-user
```

Open `/admin/login` to sign in. To grant staff access, provision an existing user account through the project's trusted account setup process, then assign a role in **Admin > Staff Access**. The CMS does not expose public registration or email invitations.

## Business configuration

- Set the notification recipients in **Admin > Bookings**. Email sending uses Laravel's configured mail transport and is isolated from saving booking and enquiry records.
- Configure `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` in the deployment environment. Keep provider credentials out of source control. The local default uses the `log` mailer and does not deliver real email.
- Configure the confirmed service area, business email, operating hours, phone/WhatsApp, and social profile URLs in **Admin > Settings**. Social links without a value are hidden.
- Service pricing is editable under **Admin > Pricing**. Moving packages use the supplied studio through four-bedroom prices. Five-bedroom moves are quote-only.
- Policy pages must be reviewed for the business's actual cancellation, refund, privacy, and service terms before publication.

## Development and verification commands

```powershell
php artisan migrate --seed
npm run dev
php artisan serve
```

Build production frontend assets with `npm run build`. Run the PHPUnit suite with `php artisan test --compact`.

New schema changes are additive. Apply them to an existing installation with `php artisan migrate --no-interaction`; do not use `migrate:fresh` where customer data must be preserved.

## Deployment checklist

- Configure production environment values, APP_KEY, database, HTTPS, mail and logging.
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Run `php artisan migrate --force` and `php artisan storage:link`.
- Build frontend assets with `npm ci --ignore-scripts && npm run build`.
- Configure the web root to Laravel's `public/` directory and use a process manager for queue workers if queues are enabled.
- Create the initial administrator after deployment and keep credentials in a password manager.
- Schedule database and uploaded-media backups and define a data-retention process.
- Before launch, have the business approve legal drafts, confirm service areas and opening hours, verify configured contact/social channels, set mail recipients, and test delivery with the chosen provider.

# home-movers-and-cleaners
