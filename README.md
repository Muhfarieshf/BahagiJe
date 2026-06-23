# BahagiJe

**BahagiJe** is a dynamic, mobile-first web application built with **CakePHP 4** and **Tailwind CSS** that takes the pain out of splitting group expenses. Whether it's a food hunt, a group road trip, or a custom event, BahagiJe simplifies debt calculation using an optimized settlement algorithm so that "Who Owes Who" is minimized into the fewest possible transactions.

## 🚀 Key Features

*   **Dynamic Group Sessions:** Create targeted sessions using Presets (Food & Dining, Road Trips, Custom) that adapt the UI to the event type.
*   **Frictionless Guest Access:** No account required for friends! Users can join instantly by scanning a **QR Code** or clicking an invite link. Guest sessions persist locally via cookies.
*   **Advanced Splitting Engine:** Split expenses equally, by exact custom amounts, or by percentages. The backend automatically calculates taxes and service charges, then simplifies the graph of who owes who to minimize overall money transfers.
*   **End-to-End Payment Proofs:** 
    * Creditors can save their preferred payment methods (Touch 'n Go eWallet, DuitNow, Bank Transfer).
    * Debtors upload proof of payment (receipt screenshots) directly to the app (powered by **Cloudinary**).
    * Creditors must manually approve the receipt to permanently close out the debt.
*   **Interactive Road Trip Planner:** A specialized preset where the host can drop waypoints (Starts, Tolls, Refuels, Destinations) onto an interactive map, and any participant can attach specific expenses to those stops.
*   **Mobile-Optimized:** Built from the ground up to feel like a native app on iOS and Android browsers, complete with bottom-sheet modals, sticky buttons, and robust cookie-handling to bypass in-app browser (e.g., Instagram/WhatsApp) tracking prevention.

---

## 🛠️ Technology Stack

*   **Backend Framework:** CakePHP 4 (PHP 8+)
*   **Frontend Styling:** Tailwind CSS
*   **Database:** MySQL / MariaDB
*   **Media Storage:** Cloudinary SDK (for receipt & avatar image hosting)
*   **Authentication:** CakePHP Authentication/Authorization Middleware & Google OAuth 2.0
*   **Maps API:** Leaflet.js (for the Road Trip interactive timeline)

---

## ⚙️ Installation & Setup Guide

### Prerequisites
*   PHP 8.1 or higher
*   Composer
*   MySQL/MariaDB
*   Node.js (for compiling Tailwind CSS if making style changes)

### 1. Clone & Install Dependencies
```bash
git clone <your-repo-url>
cd BahagiJe
composer install
```

### 2. Environment Configuration
Copy the default config file:
```bash
cp config/app_local.example.php config/app_local.php
```
Open `config/app_local.php` and configure your database credentials.

If you are using Cloudinary for receipt uploads and Google OAuth for logins, make sure to set your API keys either in `app_local.php` or your `.env` file.

### 3. Database Migration
Run the CakePHP migrations to set up the database tables:
```bash
bin/cake migrations migrate
```
*(If you need to seed initial test data, run `bin/cake migrations seed`)*

### 4. Run the Development Server
```bash
bin/cake server -p 8765
```
You can now access the app at `http://localhost:8765`.

---

## 📱 Usage Workflow

1.  **Host creates a session:** Select a preset type (e.g., Food & Dining) and enter the session details.
2.  **Invite Friends:** The app generates a QR Code and an invite link. Friends scan it to join as guests.
3.  **Log Expenses:** The host uploads the master receipt. Everyone adds their individual expenses based on what they consumed.
4.  **Lock & Calculate:** The host locks the session. BahagiJe calculates the exact subtotal, distributes the taxes/charges, and generates the final Settlement dashboard.
5.  **Pay & Verify:** Debtors view the host's Touch 'n Go details, make the transfer, and upload their screenshot. The host reviews and clicks "Approve."

---

## 📝 License

This project is licensed under the MIT License.
