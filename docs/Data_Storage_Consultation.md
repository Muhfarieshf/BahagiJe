# 📋 Data Storage Decision Summary — EquiSplit

---

### 1.0 Initial Plan

The original system design used **Cloudinary API** as the file storage solution for:
- Payment proof screenshots uploaded by participants
- Reference documents uploaded by host (receipt, bill, invoice)
- Saved payment QR codes stored in user profile

Cloudinary was chosen for:
- Free tier (25GB storage, 25GB bandwidth)
- Simple PHP SDK integration with CakePHP
- CDN-backed fast image loading on mobile
- Private URL support for access control

---

### 2.0 New Feature Proposed — Saved Payment QR

During development planning, a new UX feature was proposed:

**Concept:** Allow registered users to save their payment QR code to their profile so it can be pulled automatically in future sessions — eliminating the need to re-upload every time.

**Proposed flow:**
```
Payment step in session
        ↓
System checks: Does user have saved QR?
        ↓
YES → "Use your saved QR?" [Use Saved] or [Upload New]
NO → Upload screen + optional checkbox "Save for future sessions"
```

**Database impact:**
One new table added: `user_payment_methods`
QR images stored with fields `qr_image_url` and `qr_public_id` per record.

**PDPA safeguards for saved QR:**
- Must be opt-in only — never saved by default
- User can delete anytime from profile settings
- Deletion removes file from Cloudinary AND clears database record
- Must use Cloudinary private authenticated URLs
- Consent notice shown when saving

---

### 3.0 Cloudinary Deletion Flow

When user deletes saved QR from profile:

```
User clicks "Delete Saved QR"
        ↓
CakePHP calls CloudinaryService::destroy($qr_public_id)
        ↓
Actual image file deleted from Cloudinary servers
        ↓
Record deleted from user_payment_methods table
        ↓
Flash confirmation shown to user
```

**Cloudinary folder structure planned:**
```
equisplit/
├── payment_proofs/
│   └── session_{id}/
│       └── {uuid}.jpg
├── payment_qr/
│   └── user_{id}/
│       └── qr.jpg
└── reference_docs/
    └── session_{id}/
        └── doc.jpg
```

**Session closure bulk delete:**
```php
$cloudinary->adminApi()->deleteAssetsByPrefix(
    "equisplit/payment_proofs/session_{$sessionId}/"
);
$cloudinary->adminApi()->deleteAssetsByPrefix(
    "equisplit/reference_docs/session_{$sessionId}/"
);
```

---

### 4.0 PDPA Concern Raised — Cross Border Data Transfer

A concern was raised about **PDPA Malaysia Section 129** which states:

> *"A data user shall not transfer any personal data to a place outside Malaysia unless that place is specified by the Minister"*

**Why Cloudinary is problematic under Section 129:**
- Cloudinary is USA-based
- Default servers are US and EU regions
- Malaysia is not on the approved transfer list
- Payment screenshots and QR codes contain sensitive financial data including bank account numbers, transaction amounts, recipient names and linked phone numbers

**Data sensitivity assessment:**
| Data | Sensitivity |
|---|---|
| Payment screenshot | 🔴 High — contains financial transaction details |
| Saved payment QR | 🔴 High — contains banking details and account info |
| Reference documents | 🟡 Medium — receipts and invoices |
| Profile avatars | ✅ Low — pulled from Google, not our responsibility |

---

### 5.0 Option A Considered — Stay With Cloudinary + Disclaimer

**Pros:** Zero development change, common practice for student projects, fast mobile performance maintained.

**Cons:** Technically still non-compliant with Section 129, weak argument if questioned by panel.

---

### 6.0 Option B Considered — Switch to Malaysia-Based Storage

| Provider | Malaysia DC | Free Tier | Verdict |
|---|---|---|---|
| AWS S3 ap-southeast-1 | Singapore | Limited | Paid beyond free tier |
| Wasabi | No MY DC | Paid only | Not suitable |
| Backblaze B2 | No MY DC | 10GB free | Not suitable |
| MinIO self-hosted | Own server | Free | Too complex for FYP |
| TM ONE / Malaysian cloud | ✅ Malaysia | Paid | Not free |

**Conclusion:** No major cloud storage provider offers a Malaysian data center on a free tier suitable for FYP scope.

---

### 7.0 Option C Considered — Local File Storage on Server

**Pros:** Strong PDPA Section 129 compliance, simple PHP file upload, free.

**Cons:** InfinityFree clears files periodically, limited storage, no CDN, not scalable.

---

### 8.0 Critical Issue Identified — Mobile Experience

The core value proposition of EquiSplit is a **QR-based mobile-first application**. Local storage on InfinityFree fails the primary mobile user journey due to unreliable file persistence and lack of CDN.

---

### 9.0 PDPA Re-evaluation — Academic Project Context

PDPA Section 129 primarily targets **commercial data users** — registered businesses processing personal data in commercial transactions. EquiSplit is an academic FYP project, not a registered commercial entity.

**Panel-ready PDPA response:**
> *"EquiSplit acknowledges PDPA Section 129 regarding cross-border data transfer. In the current academic implementation, Cloudinary is used for reliable file storage with private authenticated URLs ensuring data is not publicly accessible. For a production commercial deployment, migration to a Malaysia-approved storage provider would be required for full PDPA Section 129 compliance."*

---

### 10.0 Final Decision — Cloudinary With Proper Controls

**Keep Cloudinary** as the storage solution with proper privacy controls and PDPA acknowledgment in the report.

**Rationale:** Mobile UX is the core value of EquiSplit — cannot be compromised. Cloudinary free tier is sufficient and reliable for FYP and real users. Private authenticated URLs mitigate public exposure risk. PDPA concern addressed through report acknowledgment and user consent. Academic project context reduces strict PDPA commercial applicability.

---

### 11.0 Final Storage Architecture

| File Type | Storage | Access Control | Deletion |
|---|---|---|---|
| Payment proofs | Cloudinary private | Session participants only via authenticated URL | On session closure via bulk delete API |
| Saved payment QR | Cloudinary private | User profile only | User-initiated via profile settings |
| Reference documents | Cloudinary private | Session participants only | On session closure via bulk delete API |
| Profile avatars | Google CDN | Public | Not our responsibility |

---

### 12.0 PDPA Safeguards Implemented

| Safeguard | Implementation |
|---|---|
| Private URLs | Cloudinary authenticated type — not publicly accessible |
| User consent | Notice shown before upload — cross-border storage acknowledged |
| Opt-in QR saving | Never saved by default — user explicitly chooses |
| Right to deletion | Delete button in profile settings — removes from Cloudinary AND database |
| Session data retention | All session files deleted from Cloudinary on session closure |
| Minimum data collection | Only collect what is necessary for payment verification |
| Access control | Only session participants can view session files |

---

### 13.0 Report Language — PDPA Section

> *"EquiSplit stores uploaded files including payment proofs and reference documents on Cloudinary, a cloud-based media management platform. While Cloudinary's servers are located outside Malaysia, which raises considerations under Section 129 of the Personal Data Protection Act 2010, all uploaded files are stored as private authenticated assets inaccessible to the public. User consent is obtained prior to upload informing users of cross-border storage. For a future commercial deployment, migration to a Malaysia-approved cloud storage provider is recommended to achieve full PDPA Section 129 compliance."*

---

### 14.0 Future Production Recommendation

| Stage | Storage Solution | Reason |
|---|---|---|
| FYP Demo | Cloudinary free tier | Reliable, mobile-friendly, free |
| Post-FYP MVP | AWS S3 ap-southeast-1 Singapore | Closest regional DC, scalable |
| Production Malaysia | TM ONE or Malaysian cloud | Full PDPA Section 129 compliance |
