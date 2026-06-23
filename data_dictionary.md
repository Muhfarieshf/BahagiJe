## 5.4 Data Dictionary

The data dictionary contains a detailed and extensive specification of all database entities, their attributes, data types, functional descriptions and system constraints. The aim of this documentation is to provide a simple reference that will, throughout the development lifecycle of the BahagiJe web application, ensure architectural clarity, referential integrity, and consistency. This metadata is not only used to explicitly specify the rules of the relational schema, but it also helps to reduce implementation errors in the development of the backend object-relational mapping logic and the construction of the database.

### Table 2: Users
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique user identifier |
| `name` | VARCHAR(100) | NOT NULL | Full display name of the registered user |
| `email` | VARCHAR(150) | NOT NULL, UNIQUE | User email address from Google OAuth |
| `google_id` | VARCHAR(100) | NOT NULL, UNIQUE | Unique identifier from Google OAuth provider |
| `avatar_url` | VARCHAR(255) | NULLABLE | Profile picture URL retrieved from Google |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Account creation timestamp |

<br>

### Table 3: Group Sessions
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique session identifier |
| `uuid` | VARCHAR(36) | NOT NULL, UNIQUE | UUID used for QR code URL generation |
| `name` | VARCHAR(150) | NOT NULL | Session name set by host |
| `host_id` | INT | FK → USERS.id, NOT NULL | References the user who created the session |
| `preset_type` | ENUM | NOT NULL | Session type: dining, grocery_run, road_trip, long_trip, custom |
| `status` | ENUM | NOT NULL, DEFAULT open | Session state: open, locked, closed |
| `max_participants` | INT | NOT NULL | Maximum number of participants allowed |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Session creation timestamp |
| `closed_at` | DATETIME | NULLABLE | Timestamp when session was closed |

<br>

### Table 4: Participants
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique participant record identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session joined |
| `user_id` | INT | FK → USERS.id, NULLABLE | References registered user — null for guests |
| `guest_name` | VARCHAR(100) | NULLABLE | Display name for guest participants only |
| `role` | ENUM | NOT NULL | Participant role: host, registered, guest |
| `joined_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when participant joined |

<br>

### Table 5: Session Charges
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique charge record identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this charge belongs to |
| `charge_name` | VARCHAR(100) | NOT NULL | Name of charge e.g. SST, Service Charge, Delivery Fee |
| `charge_type` | ENUM | NOT NULL | Type of charge: percentage, flat |
| `charge_value` | DECIMAL(8,2) | NOT NULL | Numeric value of charge — percent or flat amount |
| `applies_to` | ENUM | NOT NULL | Split method for this charge: proportional, equal |

<br>

### Table 6: Expenses
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique expense identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this expense belongs to |
| `participant_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References participant who logged the expense |
| `receipt_id` | INT | FK → RECEIPTS.id, NULLABLE | Links this expense to a parent receipt for itemized billing. Null for standalone expenses. |
| `description` | VARCHAR(200) | NOT NULL | Name or description of the item or expense |
| `total_amount` | DECIMAL(10,2) | NOT NULL | Total monetary value of the expense |
| `expense_type` | ENUM | NOT NULL | Type: personal, group |
| `split_type` | ENUM | NOT NULL | How expense is split: equal, percentage, exact |
| `waypoint_id` | INT | FK → SESSION_WAYPOINTS.id, NULLABLE | References the specific map stop this expense occurred at. |
| `quantity` | INT | NOT NULL, DEFAULT 1 | Used when expense represents a line item on a receipt |
| `image_url` | VARCHAR(255) | NULLABLE | Cloudinary URL for attaching photos directly to individual line items. |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Expense entry timestamp |

<br>

### Table 7: Expense Allocations
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique allocation identifier |
| `expense_id` | INT | FK → EXPENSES.id, NOT NULL | References the expense being allocated |
| `participant_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant who owes this amount |
| `amount_owed` | DECIMAL(10,2) | NOT NULL | Calculated amount this participant owes for this expense |
| `is_payer` | BOOLEAN | NOT NULL, DEFAULT FALSE | True if this participant originally paid for the expense |

<br>

### Table 8: Payment Proofs
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique proof submission identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this proof belongs to |
| `participant_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant who submitted proof |
| `settlement_transaction_id` | INT | FK → SETTLEMENT_TRANSACTIONS.id, NULLABLE | Links this proof to the specific settlement transaction it resolves |
| `proof_url` | VARCHAR(255) | NOT NULL | Cloudinary URL of the uploaded payment screenshot |
| `status` | ENUM | NOT NULL, DEFAULT pending | Verification status: pending, approved, rejected |
| `submitted_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Proof submission timestamp |
| `reviewed_at` | DATETIME | NULLABLE | Timestamp when host reviewed the proof |
| `rejection_reason` | VARCHAR(255) | NULLABLE | Optional reason provided by host upon rejection |

<br>

### Table 9: Settlement Transactions
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique settlement transaction identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this settlement belongs to |
| `debtor_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant who owes money |
| `creditor_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant who is owed money |
| `amount` | DECIMAL(10,2) | NOT NULL | Amount to be transferred from debtor to creditor |
| `status` | ENUM | NOT NULL, DEFAULT pending | Settlement status: pending, claimed, settled, unresolved |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when settlement was calculated |

<br>

### Table 10: Session Notifications
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique notification identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this notification belongs to |
| `participant_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant receiving the notification |
| `message` | VARCHAR(255) | NOT NULL | Notification message content displayed to the participant |
| `type` | ENUM | NOT NULL | Notification type: reupload_request, approved, rejected |
| `is_read` | BOOLEAN | NOT NULL, DEFAULT FALSE | Whether the notification has been read |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Notification creation timestamp |

<br>

### Table 11: Receipts
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique receipt identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this receipt belongs to |
| `payer_id` | INT | FK → PARTICIPANTS.id, NOT NULL | References the participant who paid the receipt |
| `name` | VARCHAR(255) | NOT NULL | Title or merchant name of the receipt |
| `image_url` | VARCHAR(255) | NULLABLE | Cloudinary URL of the uploaded physical receipt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when receipt was created |

<br>

### Table 12: Session Waypoints
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique waypoint identifier |
| `session_id` | INT | FK → GROUP_SESSIONS.id, NOT NULL | References the session this waypoint belongs to |
| `name` | VARCHAR(200) | NOT NULL | Display name of the location |
| `lat` | DECIMAL(10,8) | NULLABLE | Geographical latitude coordinate. Nullable for named stops added without map search. |
| `lng` | DECIMAL(11,8) | NULLABLE | Geographical longitude coordinate. Nullable for named stops added without map search. |
| `type` | ENUM | NOT NULL | Type of waypoint: start, stop, toll, destination |
| `sort_order` | INT | NOT NULL, DEFAULT 0 | Determines chronological order on the route timeline |

<br>

### Table 13: User Payment Methods
| Field Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, NOT NULL, AUTO_INCREMENT | Unique payment method identifier |
| `user_id` | INT | FK → USERS.id, NOT NULL | References the registered user who owns this method |
| `method_type` | VARCHAR(50) | NOT NULL | Payment method category: bank_transfer, duitnow_qr, etc. |
| `label` | VARCHAR(100) | NULLABLE | Optional user-defined label for this method |
| `account_name` | VARCHAR(150) | NULLABLE | Registered name on the receiving account |
| `account_value` | VARCHAR(150) | NULLABLE | The actual account number or DuitNow ID |
| `bank_name` | VARCHAR(100) | NULLABLE | Name of the bank institution, if applicable |
| `qr_image_url` | VARCHAR(255) | NULLABLE | Cloudinary private URL of the uploaded payment QR code image |
| `qr_public_id` | VARCHAR(255) | NULLABLE | Cloudinary public ID used for server-side deletion of the QR image |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when this payment method was saved |