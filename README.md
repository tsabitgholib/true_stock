# True Stock - WMS

Warehouse Management System built with Laravel 12, Vue 3, and DDD Architecture.

## Quick Start

1. **Clone & Install**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database & Seeding**
   ```bash
   php artisan migrate --seed
   ```

4. **Run Development Server**
   ```bash
   npm run dev
   # and in another terminal
   php artisan serve
   ```

## Documentation
For detailed technical architecture, module designs, and coding guidelines, please refer to:
[**DOCUMENTATION.md**](./DOCUMENTATION.md)

## Key Features
- **In-Transit Management:** Realistic warehouse transfers.
- **Stock Reservation:** Prevent overselling with "Available vs Physical" logic.
- **Advanced Audit Trail:** Detailed change tracking (Before/After).
- **Real-time Analytics:** Professional dashboard with inventory valuation.
- **Modern Stack:** Clean Architecture, DDD, and Event-Driven.

---
Built for high-performance and data integrity.
