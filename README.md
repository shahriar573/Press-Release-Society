# Press Release Council Dashboard

A modern, responsive PHP-based dashboard for managing press releases, members, media outlets, distribution records, and events.

## Features

✅ **Responsive Design** - Mobile and desktop optimized with CSS Grid/Flexbox  
✅ **Admin Login** - Session-based authentication (default: `admin` / `admin123`)  
✅ **Dynamic Data** - Fetch API for async table loading  
✅ **Interactive Cards** - Click to load data tables  
✅ **Modal Forms** - Add/Edit records (front-end ready, requires backend API)  
✅ **Clean UI** - Modern design with hover effects and smooth transitions

---

## Project Structure

```
press_release_society/
├── index.php           # Main dashboard (header, cards, tables, modals, JS)
├── style.css           # Responsive CSS with Grid/Flexbox
├── api.php             # JSON API endpoint (sample data)
├── auth.php            # Login/logout session handler
├── config.php          # Config & credentials
├── view.php            # Legacy table viewer (optional)
└── README.md           # This file
```

---

## Quick Start

### 1. **Setup Database (New - Automated)**

The project now uses a real MySQL database. Run the setup script to create and populate tables:

**Option A: Browser (Recommended)**
```
http://localhost/press_release_society/setup_db.php
```

**Option B: Command Line**
```powershell
cd c:\xampp\htdocs\press_release_society
php setup_db.php
```

The setup script will:
- Create database `press_release_db`
- Run `schema.sql` to create tables
- Insert sample data
- Show verification results

⚠️ **Important:** After setup, delete or secure `setup_db.php` to prevent unauthorized database resets.

### 2. **Run with PHP Built-in Server** (Alternative to XAMPP)

```powershell
cd c:\xampp\htdocs\press_release_society
php -S localhost:8000
```

Then open: **http://localhost:8000**

Or use XAMPP and navigate to: **http://localhost/press_release_society**

### 3. **Admin Login**

- Click **"Admin Login"** in the navbar
- Username: `admin`
- Password: `admin123`

After login, you'll see **Add/Edit/Delete** buttons in tables.

---

## Configuration

### Change Admin Credentials

Edit `config.php`:

```php
$CONFIG = [
    'admin_user' => 'yourusername',
    'admin_pass_hash' => password_hash('yourpassword', PASSWORD_DEFAULT),
];
```

### Connect to a Database

1. **Uncomment DB connection in `config.php`:**

```php
$conn = new mysqli('localhost','user','pass','database');
if ($conn->connect_error) { die('DB Connection failed: ' . $conn->connect_error); }
```

2. **Update `api.php`** to query real tables:

```php
$result = $conn->query("SELECT * FROM $table");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode(['table'=>$table,'count'=>count($data),'rows'=>$data]);
```

3. **Implement POST/PUT/DELETE endpoints** in `api.php` for persistence.

---

## API Endpoints

### `api.php?table=<TableName>`

**Method:** `GET`  
**Tables:** `Members`, `PressReleases`, `MediaOutlets`, `DistributionRecords`, `Events`  
**Response:**

```json
{
  "table": "Members",
  "count": 2,
  "rows": [
    {"id":1,"name":"Alice Smith","role":"Editor","email":"alice@example.com"},
    {"id":2,"name":"Bob Jones","role":"Reporter","email":"bob@example.com"}
  ]
}
```

### `auth.php`

**Login:** POST `action=login&user=admin&pass=admin123`  
**Logout:** POST `action=logout`  
**Check:** GET (returns `{"is_admin": true/false}`)

---

## Roadmap Checklist

- [x] Header / Navbar with logo and navigation links
- [x] Sidebar for quick links (collapsible on mobile)
- [x] Dashboard cards for each category
- [x] Responsive tables with dynamic data from API
- [x] Admin login modal with session management
- [x] Add/Edit/Delete buttons (admin only)
- [x] Modal forms for add/edit (front-end ready)
- [x] Modern CSS with Grid/Flex, hover effects
- [x] Fetch API for async data loading
- [x] Footer with copyright & contact

---

## Browser Compatibility

- Chrome, Firefox, Edge, Safari (latest versions)
- Mobile responsive (iOS Safari, Chrome Android)

---

## License

Free to use. Modify as needed for your organization.

---

## Contact

For support: info@presscouncil.org

---

## Database Schema

This project now uses a **real MySQL database** via mysqli. The schema is defined in `schema.sql`.

### Automated Setup (Recommended)

Use the included setup script:

**Browser:** Navigate to `http://localhost/press_release_society/setup_db.php`  
**CLI:** Run `php setup_db.php`

The script will create the database, tables, and populate sample data automatically.

### Manual Setup (Alternative)

If you prefer manual setup or need to customize:

```powershell
# From project root (Windows PowerShell)
mysql -u root -p < .\schema.sql
```

**Note:** You'll need to create the database first:
```sql
CREATE DATABASE press_release_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Tables Overview

Quick summary of tables created:
- `Members` - council members and authors (id, name, role, email, phone, bio, is_active, joined_at)
- `PressReleases` - releases (id, title, slug, summary, content, published_at, status, author_id)
- `MediaOutlets` - media contacts (id, name, contact_person, email, phone, outlet_type)
- `DistributionRecords` - tracking which release was sent where (id, release_id, media_outlet_id, sent_to, sent_at, status)
- `Events` - events related to press releases (id, title, description, event_date, location, created_by, related_release_id)

### Database Configuration

Database settings are in `config.php`:
- **Host:** localhost
- **User:** root (XAMPP default)
- **Password:** (empty for XAMPP default)
- **Database:** press_release_db

Modify these in `config.php` if your setup differs.

### Connection Details

The app now uses mysqli for all database operations:
- `config.php` - Establishes database connection (`$conn` global)
- `api.php` - Fetches data from real tables instead of sample arrays
- `setup_db.php` - One-time setup script to create and populate database

### Notes and Assumptions

- Schema uses InnoDB-style foreign keys and MySQL-compatible types.
- Passwords and authentication use `config.php`/`auth.php` (file-based) for the demo; consider DB-backed auth for production.
- `slug` in `PressReleases` is optional but unique to support pretty URLs.
- `DistributionRecords` stores both `media_outlet_id` and a free-text `sent_to` to allow sending to external addresses not in the `MediaOutlets` table.
- The setup script (`setup_db.php`) includes `DROP TABLE` statements - it will reset your database each time you run it. **Delete or secure this file after initial setup.**
- Database connection is now active in `config.php` - the app will fail if MySQL is not running or credentials are incorrect.
