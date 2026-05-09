# MongoDB Cheat Sheet — Wildlife Categorization

Quick reference for poking at the database directly. Two ways: a GUI
(MongoDB Compass) and the command line (`mongosh`).

---

## 1. GUI — MongoDB Compass (recommended for browsing)

1. Download: <https://www.mongodb.com/try/download/compass>
2. Open Compass → click **Connect** (default URI `mongodb://localhost:27017`).
3. In the left sidebar pick `wildlife_categorization`.
4. Click any collection (`users`, `categories`, `habitats`, `species`,
   `activity_log`) to browse, filter, edit, or delete documents — no
   commands needed.

Compass is to MongoDB what phpMyAdmin is to MySQL.

---

## 2. Command line — `mongosh`

Open the shell:

```
"C:\Program Files\MongoDB\Server\<version>\bin\mongosh.exe"
```

You'll get a `>` prompt. Type `exit` to leave.

### Vocabulary swap

| MySQL term     | MongoDB term     |
| -------------- | ---------------- |
| database       | database         |
| table          | **collection**   |
| row            | **document**     |
| column         | field            |
| primary key    | `_id` (ObjectId) |
| schema-defined | schema-less      |

### Browsing

| MySQL                              | MongoDB                                 |
| ---------------------------------- | --------------------------------------- |
| `SHOW DATABASES;`                  | `show dbs`                              |
| `USE wildlife_categorization;`     | `use wildlife_categorization`           |
| `SHOW TABLES;`                     | `show collections`                      |
| Show table structure               | `db.species.findOne()`                  |
| `SELECT COUNT(*) FROM species;`    | `db.species.countDocuments()`           |

### SELECT — `find()`

| MySQL                                                      | MongoDB                                                    |
| ---------------------------------------------------------- | ---------------------------------------------------------- |
| `SELECT * FROM species;`                                   | `db.species.find()`                                        |
| `SELECT * FROM species LIMIT 5;`                           | `db.species.find().limit(5)`                               |
| `SELECT * FROM species ORDER BY name;`                     | `db.species.find().sort({ name: 1 })`                      |
| `SELECT * FROM species WHERE name = 'Lion';`               | `db.species.find({ name: "Lion" })`                        |
| `SELECT * FROM species WHERE is_endangered = 1;`           | `db.species.find({ is_endangered: true })`                 |
| `SELECT name FROM species;`                                | `db.species.find({}, { name: 1, _id: 0 })`                 |
| `SELECT * FROM species WHERE name LIKE '%tiger%';`         | `db.species.find({ name: /tiger/i })`                      |
| `SELECT * FROM species WHERE category_name IN ('Carnivore','Omnivore');` | `db.species.find({ category_name: { $in: ["Carnivore","Omnivore"] } })` |
| `SELECT * FROM species WHERE is_endangered = 1 AND category_name = 'Carnivore';` | `db.species.find({ is_endangered: true, category_name: "Carnivore" })` |

### INSERT

| MySQL                                                          | MongoDB                                              |
| -------------------------------------------------------------- | ---------------------------------------------------- |
| `INSERT INTO habitats(name,location) VALUES('Reef','Pacific');`| `db.habitats.insertOne({ name: "Reef", location: "Pacific" })` |
| Bulk insert                                                    | `db.habitats.insertMany([{...}, {...}])`             |

### UPDATE

```js
db.species.updateOne(
  { _id: ObjectId("652e4...") },
  { $set: { name: "Lion", is_endangered: true } }
)

// update many
db.species.updateMany(
  { category_name: "Carnivore" },
  { $set: { reviewed: true } }
)
```

### DELETE

```js
db.species.deleteOne({ _id: ObjectId("652e4...") })
db.species.deleteMany({ approval_status: "rejected" })
```

### DROP

```js
db.species.drop()                         // drop one collection (table)
db.dropDatabase()                         // drop everything in current db
```

---

## 3. This project — collections cheat sheet

```js
use wildlife_categorization

// what's in here
show collections
db.users.countDocuments()
db.species.countDocuments()
db.species.countDocuments({ approval_status: "approved" })

// schema by example
db.species.findOne()
db.users.findOne({}, { password: 0 })             // hide hash

// admin user check
db.users.findOne({ username: "admin" }, { password: 0 })

// pending approvals queue
db.species.find(
  { approval_status: "pending" },
  { name: 1, scientific_name: 1, uploader_id: 1, _id: 0 }
)

// list endangered species
db.species.find(
  { is_endangered: true },
  { name: 1, category_name: 1, habitat_name: 1, _id: 0 }
).sort({ name: 1 })

// approve a single species (replace the ObjectId)
db.species.updateOne(
  { _id: ObjectId("PASTE_ID_HERE") },
  { $set: { approval_status: "approved" } }
)

// rename a category and propagate to species (this is what edit_category.php does)
db.categories.updateOne(
  { name: "Carnivore" },
  { $set: { name: "Predator" } }
)
db.species.updateMany(
  { category_name: "Carnivore" },
  { $set: { category_name: "Predator" } }
)

// recent admin activity
db.activity_log.find().sort({ created_at: -1 }).limit(10)
```

---

## 4. Performance — recommended indexes

Run these once after seeding to keep things fast as the catalog grows:

```js
use wildlife_categorization
db.species.createIndex({ approval_status: 1, name: 1 })
db.species.createIndex({ category_name: 1 })
db.species.createIndex({ habitat_name: 1 })
db.species.createIndex({ uploader_id: 1 })
db.users.createIndex({ username: 1 }, { unique: true })
db.activity_log.createIndex({ created_at: -1 })
```

`db.species.getIndexes()` lists what's there.

---

## 5. Reset password from the shell

If you forget the admin password, reset it without re-seeding:

```js
use wildlife_categorization
// pick any 60-char bcrypt hash; this one corresponds to "admin123"
db.users.updateOne(
  { username: "admin" },
  { $set: { password: "$2y$10$wH8pQbU/y.Z4Y5eH6Fd0vO2g.f8z9dLqZ0yHOmf3H2dxCZc1wQYpu" } }
)
```

(Or run `seed.php` again — it re-seeds `admin / admin123`.)

---

## 6. Big mental shift from SQL

- **No fixed schema** — every doc in a collection can have different fields.
  That's why `users` has a single `role` field instead of separate
  `admin_users` / `uploader_users` join tables.
- **No JOINs** — we *denormalize* instead. Each `species` doc carries its
  own `category_name`, `habitat_name`, `habitat_location`. Reads are fast;
  writes (renames) update many docs in one `updateMany` call.
- **`_id` is automatic** — no `AUTO_INCREMENT`. It's a 12-byte ObjectId.
  When querying, wrap the string in `ObjectId("…")`.
- **Queries are JSON objects**, not strings. `{ field: value }` is an
  equality match. Nested operators start with `$` (e.g. `$gt`, `$in`,
  `$set`, `$or`).
