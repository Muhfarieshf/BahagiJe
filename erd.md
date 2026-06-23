%%[cite: 8]
erDiagram
    Users {
        int id PK
        varchar name
        varchar email
        varchar google_id
        varchar avatar_url
        varchar payment_qr_url
        datetime payment_qr_saved_at
        datetime created_at
    }

    GroupSessions {
        int id PK
        varchar uuid
        varchar name
        int host_id FK
        enum preset_type
        enum status
        int max_participants
        datetime created_at
        datetime closed_at
    }

    Participants {
        int id PK
        int session_id FK
        int user_id FK
        varchar guest_name
        enum role
        datetime joined_at
    }

    SessionCharges {
        int id PK
        int session_id FK
        varchar charge_name
        enum charge_type
        decimal charge_value
        enum applies_to
    }

    SessionWaypoints {
        int id PK
        int session_id FK
        varchar name
        decimal lat
        decimal lng
        enum type
        int sort_order
    }

    SessionNotifications {
        int id PK
        int session_id FK
        int participant_id FK
        varchar message
        boolean is_read
        datetime created_at
    }

    Receipts {
        int id PK
        int session_id FK
        int payer_id FK
        varchar name
        varchar image_url
        datetime created_at
    }

    Expenses {
        int id PK
        int session_id FK
        int payer_id FK
        int receipt_id FK
        int waypoint_id FK
        varchar description
        int quantity
        varchar image_url
        decimal total_amount
        enum expense_type
        enum split_type
        datetime created_at
    }

    ExpenseAllocations {
        int id PK
        int expense_id FK
        int participant_id FK
        decimal amount_owed
        boolean is_payer
    }

    SettlementTransactions {
        int id PK
        int session_id FK
        int debtor_id FK
        int creditor_id FK
        decimal amount
        enum status
        datetime created_at
    }

    PaymentProofs {
        int id PK
        int session_id FK
        int settlement_transaction_id FK
        int participant_id FK
        varchar proof_url
        enum status
        varchar rejection_reason
        datetime submitted_at
        datetime reviewed_at
    }

    %% Relationships
    Users ||--o{ GroupSessions : "hosts"
    Users ||--o{ Participants : "joins as"

    GroupSessions ||--o{ Participants : "has"
    GroupSessions ||--o{ SessionCharges : "configured with"
    GroupSessions ||--o{ SessionWaypoints : "has many"
    GroupSessions ||--o{ SessionNotifications : "triggers"
    GroupSessions ||--o{ Receipts : "tracks"
    GroupSessions ||--o{ Expenses : "contains"
    GroupSessions ||--o{ SettlementTransactions : "generates"
    GroupSessions ||--o{ PaymentProofs : "contains"

    Participants }o--|| Users : "belongs to"
    Participants ||--o{ Receipts : "pays"
    Participants ||--o{ Expenses : "logs"
    Participants ||--o{ SettlementTransactions : "debtor/creditor"
    Participants ||--o{ PaymentProofs : "uploads"
    Participants ||--o{ SessionNotifications : "receives"

    Receipts ||--o{ Expenses : "has many"
    Expenses }o--|| SessionWaypoints : "belongs to"
    Expenses ||--o{ ExpenseAllocations : "allocated to"
    ExpenseAllocations }o--|| Participants : "belongs to"

    SettlementTransactions ||--o{ PaymentProofs : "has many"