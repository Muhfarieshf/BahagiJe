# Fact-Check Report: `data_dictionary.md`

**Sources checked:** `migrate.php`, `update_roadtrip.sql`, `update_expenses.sql`, all `src/Model/Table/*.php`, all `src/Model/Entity/*.php`

---

## ✅ Confirmed Accurate

Most of the data dictionary is correct. The following tables and fields were verified against the codebase:

- **Table 3 – Group Sessions**: All fields confirmed. `preset_type` ENUM values confirmed via `migrate.php` (step 6) and `GroupSessionsTable.php` validator: `dining`, `road_trip`, `long_trip`, `custom`, `grocery`. Status values (`open`, `locked`, `closed`) confirmed.
- **Table 4 – Participants**: All fields and constraints confirmed via `ParticipantsTable.php`.
- **Table 5 – Session Charges**: All fields confirmed via `SessionChargesTable.php` — `charge_type` (`percentage`, `flat`) and `applies_to` (`proportional`, `equal`) are both accurate.
- **Table 7 – Expense Allocations**: All fields confirmed via `ExpenseAllocationsTable.php`.
- **Table 8 – Payment Proofs**: All fields confirmed via `PaymentProofsTable.php` and `migrate.php`.
- **Table 9 – Settlement Transactions**: Fields confirmed, but see ⚠️ below.
- **Table 10 – Session Notifications**: Confirmed via `GroupSessionsTable.php` (hasMany association exists).
- **Table 12 – Session Waypoints**: All fields confirmed via `update_roadtrip.sql` and `SessionWaypointsTable.php`.

---

## ❌ Discrepancies Found

### 1. Table 2 – Users: Missing fields `payment_qr_url` and `payment_qr_saved_at`

> **Dictionary says:** `payment_qr_url VARCHAR(255) NULLABLE` and `payment_qr_saved_at DATETIME NULLABLE` exist on the `users` table.

**Reality:** These fields are **not present** in the `User` entity (`User.php` — `_accessible` array) and are **not validated** in `UsersTable.php`. The codebase instead has a **separate `user_payment_methods` table** (created in `migrate.php` step 1) with full payment method support (`method_type`, `label`, `account_name`, `account_value`, `bank_name`, `qr_image_url`, `qr_public_id`).

> **Verdict:** ❌ `payment_qr_url` and `payment_qr_saved_at` are **outdated/replaced** by the `user_payment_methods` table. They may or may not still exist as DB columns (they were not explicitly dropped), but the application no longer uses them through the ORM.

---

### 2. Table 6 – Expenses: `payer_id` field is wrong — it should be `participant_id`

> **Dictionary says (line 63):** `payer_id | INT | FK → PARTICIPANTS.id, NOT NULL | References participant who logged the expense`

**Reality:** The actual column name is **`participant_id`**, not `payer_id`.
- `Expense.php` entity uses `'participant_id' => true`
- `ExpensesTable.php` has `belongsTo('Participants', ['foreignKey' => 'participant_id'])`
- `migrate.php` step 5 adds `receipt_id` column `AFTER participant_id`, confirming the column is named `participant_id`

> **Verdict:** ❌ The field name in the dictionary (`payer_id`) is **wrong**. The real column is `participant_id`.

---

### 3. Table 9 – Settlement Transactions: Missing `claimed` status

> **Dictionary says (line 110):** `status ENUM ... Settlement status: pending, settled, unresolved`

**Reality:** `migrate.php` step 2 explicitly adds a `'claimed'` value to the ENUM, and `SettlementTransactionsTable.php` validator confirms: `inList('status', ['pending', 'settled', 'unresolved', 'claimed'])`.

> **Verdict:** ❌ The dictionary is **missing the `claimed` status value** from the ENUM.

---

### 4. Table 11 – Receipts: `name` field length is wrong

> **Dictionary says (line 134):** `name VARCHAR(150) NOT NULL`

**Reality:** `migrate.php` step 4 creates receipts with `name VARCHAR(255) NOT NULL`. `ReceiptsTable.php` validator also uses `maxLength('name', 255)`.

> **Verdict:** ❌ The dictionary says `VARCHAR(150)` but the actual schema uses **`VARCHAR(255)`**.

---

### 5. Table 12 – Session Waypoints: `lat` and `lng` are NULLABLE in reality

> **Dictionary says (lines 146–147):** `lat DECIMAL(10,8) NOT NULL` and `lng DECIMAL(11,8) NOT NULL`

**Reality:** `update_roadtrip.sql` defines both as `DEFAULT NULL`, and `SessionWaypointsTable.php` validator uses `allowEmptyString('lat')` and `allowEmptyString('lng')`.

> **Verdict:** ❌ The dictionary marks both as `NOT NULL`, but the schema allows **NULL values**.

---

## ⚠️ Omissions (Table Not Documented)

### Missing: `user_payment_methods` Table

The `migrate.php` script creates a `user_payment_methods` table with the following fields:

| Field | Data Type | Constraints |
|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT |
| `user_id` | INT | FK → USERS.id, NOT NULL |
| `method_type` | VARCHAR(50) | NOT NULL |
| `label` | VARCHAR(100) | NULLABLE |
| `account_name` | VARCHAR(150) | NULLABLE |
| `account_value` | VARCHAR(150) | NULLABLE |
| `bank_name` | VARCHAR(100) | NULLABLE |
| `qr_image_url` | VARCHAR(255) | NULLABLE |
| `qr_public_id` | VARCHAR(255) | NULLABLE |
| `created_at` | DATETIME | NOT NULL |

This table is **not documented anywhere** in the data dictionary. It has its own `UserPaymentMethodsTable.php` and `UserPaymentMethod.php` entity.

---

## Summary Table

| Table | Status | Issue |
|---|---|---|
| Table 2 – Users | ⚠️ Partly stale | `payment_qr_url` / `payment_qr_saved_at` replaced by `user_payment_methods` |
| Table 3 – Group Sessions | ✅ Accurate | — |
| Table 4 – Participants | ✅ Accurate | — |
| Table 5 – Session Charges | ✅ Accurate | — |
| Table 6 – Expenses | ❌ Wrong field name | `payer_id` should be `participant_id` |
| Table 7 – Expense Allocations | ✅ Accurate | — |
| Table 8 – Payment Proofs | ✅ Accurate | — |
| Table 9 – Settlement Transactions | ❌ Incomplete ENUM | Missing `claimed` status value |
| Table 10 – Session Notifications | ✅ Accurate | — |
| Table 11 – Receipts | ❌ Wrong VARCHAR length | `name` is `VARCHAR(255)`, not `VARCHAR(150)` |
| Table 12 – Session Waypoints | ❌ Wrong nullability | `lat` and `lng` are NULLABLE, not NOT NULL |
| *(missing)* – User Payment Methods | ❌ Not documented | Entire table missing from dictionary |
