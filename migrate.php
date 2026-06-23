<?php
require 'config/paths.php';
require 'vendor/autoload.php';
$app = new \App\Application('config');
$app->bootstrap();
$db = \Cake\Datasource\ConnectionManager::get('default');

echo "Running migrations...\n\n";

// 1. Create user_payment_methods table
$db->execute("
    CREATE TABLE IF NOT EXISTS user_payment_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        method_type VARCHAR(50) NOT NULL,
        label VARCHAR(100) NULL,
        account_name VARCHAR(150) NULL,
        account_value VARCHAR(150) NULL,
        bank_name VARCHAR(100) NULL,
        qr_image_url VARCHAR(255) NULL,
        qr_public_id VARCHAR(255) NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_upm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✅ Created user_payment_methods table\n";

// 2. Alter settlement_transactions: add 'claimed' to status enum
$db->execute("
    ALTER TABLE settlement_transactions 
    MODIFY COLUMN status ENUM('pending','settled','unresolved','claimed') NOT NULL DEFAULT 'pending'
");
echo "✅ Added 'claimed' status to settlement_transactions\n";

// 3. Add settlement_transaction_id to payment_proofs
$cols = $db->execute("SHOW COLUMNS FROM payment_proofs LIKE 'settlement_transaction_id'")->fetchAll('assoc');
if (empty($cols)) {
    $db->execute("
        ALTER TABLE payment_proofs 
        ADD COLUMN settlement_transaction_id INT NULL AFTER participant_id,
        ADD CONSTRAINT fk_pp_settlement FOREIGN KEY (settlement_transaction_id) REFERENCES settlement_transactions(id) ON DELETE SET NULL
    ");
    echo "✅ Added settlement_transaction_id to payment_proofs\n";
} else {
    echo "⏭ settlement_transaction_id already exists in payment_proofs\n";
}

echo "\n";

// 4. Create receipts table
$db->execute("
    CREATE TABLE IF NOT EXISTS receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        payer_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_receipts_session FOREIGN KEY (session_id) REFERENCES group_sessions(id) ON DELETE CASCADE,
        CONSTRAINT fk_receipts_payer FOREIGN KEY (payer_id) REFERENCES participants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✅ Created receipts table\n";

// 5. Add receipt_id and quantity to expenses
$cols = $db->execute("SHOW COLUMNS FROM expenses LIKE 'receipt_id'")->fetchAll('assoc');
if (empty($cols)) {
    $db->execute("
        ALTER TABLE expenses 
        ADD COLUMN receipt_id INT NULL AFTER participant_id,
        ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER total_amount,
        ADD CONSTRAINT fk_expenses_receipt FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE
    ");
    echo "✅ Added receipt_id and quantity to expenses\n";
} else {
    echo "⏭ receipt_id already exists in expenses\n";
}

// 6. Update preset_type ENUM in group_sessions
$db->execute("
    ALTER TABLE group_sessions 
    MODIFY COLUMN preset_type ENUM('dining','road_trip','long_trip','custom','grocery') NOT NULL
");
echo "✅ Added 'grocery' to preset_type ENUM in group_sessions\n";

// 7. Add image_url to receipts and expenses
try {
    $db->execute("ALTER TABLE receipts ADD COLUMN image_url VARCHAR(255) NULL");
    echo "✅ Added image_url to receipts\n";
} catch (\Exception $e) {
    echo "⏭ image_url already exists in receipts\n";
}

try {
    $db->execute("ALTER TABLE expenses ADD COLUMN image_url VARCHAR(255) NULL");
    echo "✅ Added image_url to expenses\n";
} catch (\Exception $e) {
    echo "⏭ image_url already exists in expenses\n";
}

// 8. Remove reference_doc fields from group_sessions
try {
    $db->execute("ALTER TABLE group_sessions DROP COLUMN reference_doc_url, DROP COLUMN reference_doc_type");
    echo "✅ Removed reference_doc fields from group_sessions\n";
} catch (\Exception $e) {
    echo "⏭ reference_doc fields already removed from group_sessions\n";
}

echo "\n✅ All migrations complete.\n";
