# Wildlife Explorer (NoSQL edition)

A wildlife catalog built on **PHP + MongoDB**. Originally MySQL/RDBMS; now a
document database with a modern public catalog, public user accounts, an
approval workflow, and a polished admin panel.

---

## Feature overview

### Public site
- Hero, search, category & endangered filters, **sort dropdown**, **pagination**.
- **Species detail page** (image, conservation status, related species).
- **Public sign-up / login** — anyone can register, submit species, and track
  their submissions.
- Topbar adapts to logged-in vs. logged-out state.

### Admin panel
- Hashed-password login, session-based auth (`role: admin` only).
- **Dashboard** — 5 stat cards (species, endangered, categories, habitats,
  pending approvals) + recent species + recent activity feed.
- **CRUD for species, categories, habitats, users** — all with confirmation
  pages and refusal-to-delete-when-referenced safeguards.
- **Approval workflow** — uploaders/users submit species → admins approve or
  reject from `manage_approvals.php`.
- **Profile page** — admin can change own username/password (with
  current-password verification).
- **Activity log** — every add/edit/delete/approve/reject is recorded with
  actor + timestamp; surfaced on the dashboard.

### NoSQL / MongoDB
- 5 collections: `users`, `categories`, `habitats`, `species`, `activity_log`.
- Species are denormalized — each doc embeds `category_name`, `habitat_name`,
  `habitat_location` so the public list page renders without joins.
- Editing a category/habitat name propagates to every species via
  `updateMany` ($set + multi).
- Passwords use `password_hash()` (bcrypt by default).

---

## How to run it

### 1. Install MongoDB Server
Download from <https://www.mongodb.com/try/download/community> and install.
After install, MongoDB runs as a Windows service on `mongodb://localhost:27017`.

Verify it's running:
```
"C:\Program Files\MongoDB\Server\<version>\bin\mongosh.exe"
```

### 2. Install the PHP MongoDB extension
1. Check your PHP version: `C:\xampp\php\php.exe -v`
2. Download the matching `php_mongodb.dll` from
   <https://pecl.php.net/package/mongodb> (Thread Safe x64 build matching
   your PHP version).
3. Put `php_mongodb.dll` into `C:\xampp\php\ext\`.
4. In `C:\xampp\php\php.ini` add: `extension=mongodb`
5. Restart Apache from the XAMPP control panel.

Verify: `C:\xampp\php\php.exe -m | findstr mongodb`

### 3. Seed the database
Open <http://localhost/wildlife_categorization/seed.php>. You'll get a
confirmation page first; click **Drop and reseed** to drop every collection
and reinsert the demo dataset (`admin / admin123`, `uploader_jane /
password123`, 3 categories, 12 habitats, 50 species).

Re-running the script is safe: it drops + reinserts. **Delete `seed.php`
after a successful run** so it can't be re-triggered.

### 4. Open the site
- Public site: <http://localhost/wildlife_categorization/index.php>
- Public sign-up: <http://localhost/wildlife_categorization/register.php>
- Admin login: <http://localhost/wildlife_categorization/admin/login.php>
  (username `admin`, password `admin123` after a fresh seed)

---

## File map

```
wildlife_categorization/
├── config.php              MongoDB URI + DB name
├── mongo.php               Mongo helper class
├── seed.php                ONE-TIME seeder — POST-with-confirm guarded; delete after use
├── public_auth.php         Session guard for logged-in user pages
├── lib/csrf.php            CSRF token helpers (csrf_field / csrf_check)
├── index.php               Public catalog (search · filter · sort · pagination)
├── species_detail.php      Species detail page + related strip
├── register.php            Public account sign-up
├── login.php               Public login (admins are routed to admin panel)
├── logout.php              Clears public session
├── submit_species.php      Logged-in users submit a species (pending review)
├── my_submissions.php      User's own submissions + status
│
├── partials/
│   ├── head.php            Shared <head> block (title + Inter font + CSS imports)
│   └── topbar.php          Shared public topbar (logged-in vs. logged-out)
│
├── assets/css/
│   ├── base.css            Variables, reset, buttons, badges, alerts
│   ├── public.css          Topbar, hero, search, chips, cards, pagination
│   ├── admin.css           Sidebar, dashboard, tables, forms
│   ├── auth.css            Login/register/confirm cards
│   └── detail.css          Species detail page
│
├── images/                 Hero image
└── admin/
    ├── auth.php                 Session guard + admin shell
    ├── login.php / logout.php   Admin login (hashed)
    ├── dashboard.php            Stat cards + recent species + activity feed
    ├── profile.php              Self-service username/password change
    ├── lib/activity.php         log_activity() helper + formatters
    │
    ├── manage_species.php       (with status filter + search)
    ├── add_species.php / edit_species.php / delete_species.php
    │
    ├── manage_categories.php
    ├── add_category.php / edit_category.php / delete_category.php
    │
    ├── manage_habitats.php
    ├── add_habitat.php / edit_habitat.php / delete_habitat.php
    │
    ├── manage_users.php         (admin / uploader / user roles)
    ├── add_user.php / edit_user.php / delete_user.php
    │
    ├── manage_approvals.php     Pending species queue
    └── approval_action.php      POST endpoint: approve / reject
```

---

## MongoDB schema cheat-sheet

```js
// users
{ _id, username, password (hashed),
  role: "admin" | "uploader" | "user",
  created_at }

// categories
{ _id, name }

// habitats
{ _id, name, location }

// species  — denormalized for fast public reads
{ _id, name, scientific_name, is_endangered, image_url,
  category_id, category_name,
  habitat_id, habitat_name, habitat_location,
  uploader_id,
  approval_status: "pending" | "approved" | "rejected",
  created_at }

// activity_log  — every admin action
{ _id, action: "create"|"update"|"delete"|"approve"|"reject",
  target_type: "species"|"category"|"habitat"|"user",
  target_name, actor_username, created_at }
```

---

## Performance notes

- **Denormalization**: the public catalog is one `find()` on `species` with
  no joins — category & habitat names live on each species doc.
- **Index recommendations** (run once in `mongosh` for a populated dataset):
  ```js
  use wildlife_categorization
  db.species.createIndex({ approval_status: 1, name: 1 })
  db.species.createIndex({ category_name: 1 })
  db.species.createIndex({ uploader_id: 1 })
  db.users.createIndex({ username: 1 }, { unique: true })
  ```
- **Pagination**: public catalog limits to 12 per page via `skip` + `limit`.

## Security notes

- Passwords stored only as bcrypt hashes (`password_hash` /
  `password_verify`).
- Admin pages all guarded by `admin/auth.php`; public protected pages by
  `public_auth.php`. Both bounce unauthenticated users to login.
- **CSRF tokens** on every mutating form (admin + public) via `lib/csrf.php` —
  forms emit `csrf_field()`, handlers call `csrf_check()` before processing.
- Public submissions land in `pending`; only an admin approval surfaces them
  on the public catalog.
- Self-deletion blocked on the user management page.
- `session_regenerate_id(true)` on login to mitigate fixation.
- `seed.php` requires an explicit POST with a confirm token before dropping
  any data — a casual `GET /seed.php` only shows a confirmation page.
- `image_url` values rejected unless they start with `http://` or `https://`.

---

## Troubleshooting

**"MongoDB extension not installed"** — DLL missing or `extension=mongodb`
not in `php.ini`. Restart Apache after editing.

**"Cannot connect to MongoDB"** — `mongod` isn't running. On Windows, start
it via Services (`services.msc` → MongoDB Server) or
`net start MongoDB`.

**Forgot the admin password** — re-run `seed.php` (it re-seeds
`admin / admin123`) or set a new hash in `mongosh`:
```js
use wildlife_categorization
db.users.updateOne(
  { username: "admin" },
  { $set: { password: "<paste a password_hash output here>" } }
)
```
