# SkySoft — GoDaddy deployment

This assumes standard GoDaddy shared/cPanel hosting (PHP + MySQL). If you're
on GoDaddy's Windows/ASP hosting or a VPS instead, the SQL schema is still
valid but the API layer below is written for PHP.

## 1. Create the database
1. cPanel → **MySQL Databases**. Create a database (e.g. `skysoft`) — GoDaddy
   will prefix it, giving something like `usk1234_skysoft`.
2. Create a database user and password, and add that user to the database
   with **All Privileges**.
3. cPanel → **phpMyAdmin** → select the new database → **Import** →
   upload `schema.sql`.

## 2. Create your login
Bond numbers, quantities, etc. are seeded, but you still need a user account
to log in.
1. On any machine with PHP installed, run:
   `php make_password_hash.php "YourChosenPassword"`
   (or temporarily upload the script and visit
   `yourdomain.com/make_password_hash.php?password=YourChosenPassword`,
   then **delete the file from the server immediately** — it takes a
   plain-text password over the URL, which must never sit on a live site).
2. Copy the hash it prints, then in phpMyAdmin run:
   `INSERT INTO users (username, password_hash, role) VALUES ('yourname', '<paste hash>', 'admin');`

## 3. Upload the files
Via cPanel **File Manager** or FTP, upload into `public_html` (or a
subfolder if you want it at a sub-path):
- `config.php`
- the whole `api/` folder
- `skysoft.html`

## 4. Fill in your real credentials
Edit `config.php` and replace `DB_NAME`, `DB_USER`, `DB_PASS` with the
values from step 1 (host is normally `localhost` on GoDaddy shared hosting).

Once the site is live at its real address, also change
`ALLOWED_ORIGIN` in `config.php` from `'*'` to your actual domain
(e.g. `'https://uskbond.com'`) — this stops other sites from calling your API.

## 5. Point the frontend at the API
In `skysoft.html`, near the top of the `<script>` block, `API_BASE` is set
to `/api`. That's correct if `skysoft.html` and the `api/` folder are in the
same directory. If you host them differently, change it to the full URL,
e.g. `const API_BASE = 'https://uskbond.com/api';`

## 6. Test
Visit `yourdomain.com/skysoft.html`, log in with the account from step 2,
and confirm the dashboard loads the seeded bonds.

## Notes on what changed from the earlier demo
- Data now lives in MySQL, not in the browser — closing the tab no longer
  loses anything.
- Login is enforced server-side with PHP sessions; every API endpoint
  (except login/logout/session) requires a logged-in session.
- Damage, sample, shortage, and expiry changes are written to
  `bond_entries` so the Ledger report shows real history, not just a
  running total.
- Shipping bill saves are transactional — if any line item fails (e.g. not
  enough stock), nothing in that bill is applied.
- All quantity/stock rules (can't damage/sample/short/ship more than
  current stock) are re-checked server-side, not just in the browser.

## Still to decide
- User management (creating/suspending operators) has no UI yet — do it
  directly in phpMyAdmin for now, or ask for an admin screen to be added.
- PDF export for the Shipping Bill and Form A/B reports isn't wired up —
  they render on-screen only.
