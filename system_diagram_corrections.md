# System Diagram Fact-Check Corrections

**File checked:** `2. System Diagram flow.md`
**Verified against:** `GroupSessionsController.php`, `PaymentProofsController.php`, `ExpensesController.php`

---

## Correction 1 — Close Flow: Session Does Not Wait for "All Settled"

### ❌ Current Diagram
The diagram shows:

```
H_Txns[Generate Settlement Transactions]
  → H_Proof_Review{Review Payment Proofs}
    → Approved → H_Confirm_Settle[Confirm Settlement]
      → H_All_Settled{All Settled?}
        → Yes → H_Close[Close Session]
        → No  → H_Proof_Review
```

This implies the host reviews proofs one-by-one and the session only closes once all transactions are confirmed.

### ✅ What Actually Happens
`GroupSessionsController::close()` closes the session **immediately** and generates all settlement transactions in a single database transaction. There is no "all settled?" gate before closing. Proof review and settlement confirmation happen **after** the session is already closed.

### ✔ Corrected Flow Description
```
H_Lock[Lock Session]
  → H_Preview[Preview Close — Calculate Settlements]
    → H_Settle[Net Debt Settlement Engine]
      → H_Txns[Generate Settlement Transactions]
        → H_Close[Close Session immediately]
          → H_Closed([Session Closed])
            → H_Proof_Review{Host Reviews Proofs — async, post-close}
              → Approve → H_Mark_Settled[Mark Transaction as Settled]
              → Reject  → H_Notify[Notify Participant to Reupload]
                          → H_Proof_Review
```

---

## Correction 2 — Proof Approval: Two Separate Actors, Not One

### ❌ Current Diagram
The diagram shows a single approval path:
```
H_Proof_Review{Review Payment Proofs}
  → Approved → H_Confirm_Settle[Confirm Settlement]
  → Rejected → H_Notify[Notify Participant to Reupload]
```
This implies the **host** both reviews proofs and confirms settlements as one combined action.

### ✅ What Actually Happens
There are **two separate controller actions** with different actors:

| Action | Controller Method | Actor |
|---|---|---|
| Approve/Reject a proof | `PaymentProofsController::verify()` | **Host** |
| Confirm payment received | `PaymentProofsController::confirmSettlement()` | **Creditor** (participant) |

The creditor can bypass the host approval entirely by self-confirming via `confirmSettlement()`, which sets the transaction to `settled` and the proof to `approved` in one step. Only the creditor's `user_id` is accepted; the host cannot call this method for someone else.

### ✔ Corrected Flow Description

**Host side (in Host Flow):**
```
H_Proof_Review{Host: Review Payment Proofs}
  → Approve Proof → H_Mark_Settled[Mark Transaction as Settled]
                    → H_Proof_Review  (continue reviewing)
  → Reject Proof  → H_Notify[Notify Participant to Reupload]
                    → H_Proof_Review
```

**Participant side (in Participant Flow) — add creditor path:**
```
P_View_Txn[View Settlement Transaction]
  → P_Txn_Role{Role in Transaction?}
      → Debtor   → P_Up_Proof[Upload Payment Proof]
                   → P_Wait{Await Host Approval}
                       → Rejected → P_Up_Proof
                       → Approved → P_Verified([Payment Verified])
      → Creditor → P_Confirm_Settle[Confirm Payment Received]
                   → P_Verified([Payment Verified])
```

---

## Correction 3 — Waypoint Management is Host-Only

### ❌ Current Diagram
Both the **Host Flow** (lines 36–45) and **Participant Flow** (lines 118–120) show the road trip path leading to waypoint management / adding expenses at waypoints with no distinction on who can do what.

### ✅ What Actually Happens
`GroupSessionsController::addWaypoint()` and `deleteWaypoint()` both enforce:
```php
if ($session->host_id !== $identity->id) {
    $this->Flash->error('Only the host can add waypoints.');
```

Participants can only **view** the map and **add expenses linked to existing waypoints** (via `waypoint_id` on the expense form). They cannot add or remove the waypoints themselves.

### ✔ Corrected Flow Description

**Host Flow (unchanged in structure, clarify label):**
```
H_RT_Map["Interactive Map UI — Leaflet.js + OpenStreetMap"]
  → H_RT_WP{Manage Waypoints (Host Only)}
      → Add Start Point / Stop / Toll / Destination
      → Delete Waypoint
```

**Participant Flow (add a read-only note):**
```
P_RT_View["View Interactive Map and Timeline (read-only)"]
  → P_RT_Exp["Add Expense at Specific Waypoint (linked to existing waypoints only)"]
```

---

## Summary

| # | Issue | Severity |
|---|---|---|
| 1 | `H_All_Settled` gate doesn't exist in code — close is immediate | ❌ Inaccurate flow |
| 2 | Creditor self-confirm path (`confirmSettlement`) is missing | ❌ Missing flow |
| 3 | Diagram does not distinguish host-only waypoint management from participant expense-at-waypoint | ⚠️ Misleading |
