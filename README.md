# ServiceHub Backend

ServiceHub is a service-booking platform. Customers request services through the Flutter application, administrators manage operations through the Laravel API and web dashboard, and staff members execute assigned work through the API.

This repository contains:

- Laravel REST APIs used by the Flutter client.
- A Blade-based administrator dashboard.
- Booking, quotation, staff assignment, invoicing, payment, payment-proof, and reporting workflows.

## Technology Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum for API tokens
- Eloquent ORM and database migrations
- Blade and Tailwind CDN for the admin dashboard
- SQLite or MySQL
- Private local storage for payment-proof files

## User Roles

| Role | Main responsibility |
| --- | --- |
| Customer | Browse services, create bookings, respond to quotations, view invoices, and submit payment proofs |
| Staff | Manage availability, respond to assignments, and update work progress |
| Admin | Manage the catalog, bookings, quotations, staff, invoices, payments, payment proofs, and reports |

API role protection uses `auth:sanctum` and the `role:admin`, `role:staff__ or `role:customer__ middleware. The web dashboard uses `admin.web`.

## Business Workflow

~~~text
Customer creates booking
        ↓
Admin reviews pending booking
        ↓
Admin creates and sends quotation
        ↓
Customer accepts or rejects quotation in Flutter
        ↓
Admin assigns staff after quotation acceptance
        ↓
Staff accepts assignment and performs the work
        ↓
Staff marks booking completed
        ↓
Admin issues invoice
        ↓
Customer uploads payment proof
        ↓
Admin approves or rejects proof
        ↓
Approved proof records invoice payment and updates balance
~~~

### Booking states

`pending` → `assigned` → `accepted` → `on_the_way` → `in_progress` → `completed`

Bookings may also become `cancelled` or `rejected` when the applicable business rules allow it.

### Quotation rules

- A quotation can be created only for a pending booking without an existing quotation.
- A quotation starts as `sent`.
- The customer can respond only while it is `sent` and not expired.
- Customer response states are `accepted`, `rejected`, or `expired`.
- Staff assignment is allowed only after the customer accepts the quotation.

### Invoice and pricing rules

- An invoice can be created only after a booking is completed.
- Each booking can have only one invoice.
- If an accepted quotation exists, its service name, service price, extra fee, discount, and total are copied into the invoice. The accepted quotation is the pricing source of truth.
- Legacy bookings without an accepted quotation use the booking price plus invoice adjustments.
- Payment amounts cannot exceed the remaining invoice balance.
- Invoice payment states are `unpaid`, `partial`, and `paid`.

### Payment-proof rules

- A customer can submit a JPG, JPEG, PNG, or PDF proof up to 10 MB.
- A paid invoice cannot receive another proof.
- Only one proof can be pending for an invoice at a time.
- Proof files are stored on the private local disk.
- Admin approval calls the shared payment service, creates an `invoice_payment`, links it to the proof, and updates the invoice balance atomically.
- Rejected proofs remain in the database with the reviewer and rejection reason for audit history.

## REST API

The API base path is `/api`. All protected endpoints require a Sanctum bearer token.

### Public authentication

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/register` | Create a customer account |
| `POST` | `/login` | Log in |
| `POST` | `/auth/google` | Google authentication |

### Shared catalog

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/service-categories` | List service categories |
| `GET` | `/services` | List services |
| `GET` | `/services/{service}` | View a service |

### Customer endpoints

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/customer/bookings` | List the customer's bookings |
| `POST` | `/customer/bookings` | Create a booking |
| `GET` | `/customer/bookings/{booking}` | View a booking |
| `PATCH` | `/customer/bookings/{booking}/cancel` | Cancel a booking |
| `GET` | `/customer/quotations` | List quotations |
| `GET` | `/customer/quotations/{quotation}` | View a quotation |
| `PATCH` | `/customer/quotations/{quotation}/respond` | Accept or reject a quotation |
| `GET` | `/customer/invoices` | List invoices |
| `GET` | `/customer/invoices/{invoice}` | View an invoice and payment proofs |
| `GET` | `/customer/payment-proofs` | List submitted payment proofs |
| `POST` | `/customer/invoices/{invoice}/payment-proofs` | Upload a payment proof as multipart form data |
| `GET` | `/customer/payment-proofs/{paymentProof}` | View proof status and review information |

Payment-proof upload fields are `amount`, `payment_method`, `proof`, and optional `note`.

### Staff endpoints

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/staff/profile` | View staff profile |
| `PATCH` | `/staff/availability` | Update availability |
| `GET` | `/staff/assignments` | List assignments |
| `GET` | `/staff/assignments/{assignment}` | View an assignment |
| `PATCH` | `/staff/assignments/{assignment}/respond` | Accept or reject an assignment |
| `PATCH` | `/staff/assignments/{assignment}/work-status` | Update work progress |

### Admin API endpoints

Admin API modules are available under `/admin` for:

- Catalog category and service CRUD.
- Booking list, detail, staff eligibility, assignment, cancellation, and rejection.
- Quotation list, creation for a booking, and detail.
- Invoice list, invoice creation from a booking, detail, and payment recording.
- Staff list, creation, detail, update, and deactivation.

## Admin Web Dashboard

Open the dashboard at `/admin/login`. After authentication, the main pages are:

| Page | Path |
| --- | --- |
| Dashboard | `/admin/dashboard` |
| Bookings | `/admin/bookings` |
| Quotations | `/admin/quotations` |
| Staff | `/admin/staff` |
| Invoices | `/admin/invoices` |
| Payments | `/admin/payments` |
| Payment proof review | `/admin/payment-proofs` |
| Reports | `/admin/reports` |

Reports support a date range and summarize booking status, quotation conversion, invoices, collections, payment methods, top services, and recent payments.

## Application Structure

~~~text
app/
├── Enums/                    Workflow and status enums
├── Http/
│   ├── Controllers/Api/      Flutter-facing controllers
│   ├── Controllers/Web/      Admin dashboard controllers
│   ├── Requests/             Validation and authorization
│   └── Resources/            API response transformations
├── Models/                   Eloquent models and relationships
└── Services/                 Transactional business rules

database/migrations/          Database schema and indexes
resources/views/admin/        Admin Blade pages
routes/api.php                REST API routes
routes/web.php                Admin web routes
tests/                        Feature and unit tests
~~~

Important shared services include:

- `QuotationService`: quotation creation and customer response rules.
- `InvoiceService`: invoice creation, quotation pricing propagation, and payment recording.
- `PaymentProofService`: proof submission, approval, rejection, and file lifecycle.
- `BookingStatusTransitionService`: staff work-status transitions.

## Local Setup

~~~bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve --port=8001
~~~

Configure the database and Google credentials in `.env` when those features are enabled. Payment proofs use the `local` private disk, so `storage:link` is not required for proof security; it remains useful for other public storage assets.

Run the test suite:

~~~bash
php artisan test
vendor/bin/pint
~~~

## Flutter Client Integration

The Flutter client is maintained in the companion `flutter_laravel_testing` project. Its Dio client points to the Laravel API base URL in `lib/core/network/api_client.dart`.

When running on a physical device, replace `localhost` with the backend machine's LAN IP address. The client currently includes customer, staff, and admin feature folders and uses `file_picker` for payment-proof uploads.

## Related Documentation

The following documents are maintained in the separate ServiceHub Project Documentation repository:

- `API_DOCUMENTATION.md`
- `BUSINESS_WORKFLOW.md`
- `DATABASE_SCHEMA.md`
- `SYSTEM_ARCHITECTURE.md`
- `DEVELOPMENT_GUIDE.md`
- `PROJECT_ROADMAP.md`
