# Enterprise B2B Multi-Tenant SaaS Platform

### 🚀 Lead Software Engineer: Mateusz Grzegorz Lipiński
**A production-grade, highly scalable, and secure B2B Multi-Tenant SaaS Platform built using Laravel 11, designed for international financial management and EU-compliant cross-border invoicing in the Euro zone (€).**

The entire infrastructure was designed and implemented by me from scratch to solve real-world architectural challenges of corporate software: strict data isolation, multi-database cross-boundary authentication, and an event-driven asynchronous usage-based billing pipeline. The entire ecosystem is fully containerized inside a custom Docker layout.

---

## 🏗️ Core Architectural Pillars

### 1. Advanced Multi-Tenancy (Database-Per-Tenant Model)
I implemented a highly secure **Database-Per-Tenant** blueprint driven by `stancl/tenancy` to ensure absolute corporate data isolation.
* **Dynamic Routing:** Tenants are resolved dynamically at the network layer based on custom subdomains (e.g., `company_a.localhost`).
* **Complete Context Isolation:** The system intercepts incoming database sessions on the fly, switching the active PDO connector context to an isolated tenant schema, effectively neutralizing any risks of cross-tenant data bleeding.

### 2. Multi-DB Cross-Boundary Authentication & RBAC
Solves the Enterprise challenge where Workspace Owners exist in the central landlord directory but must coordinate within isolated tenant environments.
* **Cross-DB Verification:** Authentication guards securely query the central `mysql` database to validate master owner footprints, then automatically initialize a cryptographic bearer token profile using `laravel/sanctum` within the active tenant stack.
* **Granular RBAC Middleware:** Restricts internal resource access based on corporate roles (`admin`, `manager`, `accountant`, `viewer`).

### 3. Event-Driven Asynchronous Billing Architecture (Stripe Metered System)
To enforce 0ms UI blocking overhead, all heavy computing vectors are completely offloaded to background worker pools.
* **Event Dispatching:** Successfully saving an invoice triggers an internal `InvoiceCreated` event infrastructure.
* **Stripe Usage-Based Tracking:** An asynchronous listener taps into the background database queue (backed by Redis in Docker) to register a real-time consumption event (+1 invoice factor) inside the central landlord `meter_events` table, feeding Stripe Metered Billing APIs.
* **In-Memory PDF Mail Streaming:** A secondary listener compiles compliant bilingual/English PDF commercial invoice sheets directly inside the container's RAM using DomPDF and attaches the binary bytes directly to SMTP mail envelopes dispatched asynchronusly to **Mailpit**.

### 4. Hardened Security Shield Gates & Audit Trails
* **Global Billing Gate Middleware:** A composite network screen (`TenantBillingGate`) intercepting route contexts. It terminates calls immediately with **401 Unauthorized** exceptions if bearer arrays are missing. Furthermore, it parses realtime Stripe cancellation signals (`customer.subscription.deleted`) to block unpaid workspaces with rigid **402 Payment Required** web protocol states.
* **Compliance Audit Trails:** Using a custom reusable `LogsActivity` trait, the active tenant context automatically records deep digital fingerprints (including explicit IPv4/IPv6 geo-proxies and browser User Agents) of sensitive actions inside an immutable `activity_logs` database matrix.
* **Hardened Rate Limiting:** Outfitted with an infrastructure-level `throttle:tenant_api` shield restricting heavy calls to `60 requests per minute` per token to block DoS and brute-force actors.

---

## 🎛️ Docker Stack & Infrastructure

The application runs inside a containerized local development ecosystem managed via Laravel Sail:
* **`laravel.test`** - PHP 8.5 app container layer executing logic and API kernels.
* **`mysql`** - Database engine split into central landlord configuration and isolated tenant partitions.
* **`redis`** - In-memory key-value container matrix managing background job payload distribution queues.
* **`mailpit`** - Secure SMTP interceptor engine trapping outgoing invoice and recruitment emails localizing views under port `8025`.

---

## 📊 Standard API Response Matrix

The gateway unifies protocol delivery codes according to strict RESTful specifications:

| Status Code | Code Name | Context Cause | System Reaction |
| :--- | :--- | :--- | :--- |
| **`201`** | `Created` | Valid bearer token + Active Stripe subscription | Resource compiled successfully, background queues fired. |
| **`401`** | `UNAUTHENTICATED` | Bearer token missing, expired, or invalid | Intercepts call, drops database query execution instantly. |
| **`402`** | `PAYMENT_REQUIRED` | Subscription status labeled as `canceled` or `past_due` | Blocks business endpoints, returns Stripe Customer Portal recovery links. |
| **`429`** | `Too Many Requests` | Burst threshold exceeds 60 actions per minute | Throttles incoming network sockets to guard CPU cycles. |

---

## 🛠️ Local Installation & Automated Verification

### 1. Environment Synchronization
Clone the repository and compile the localized environment blueprints:
```bash
cp .env.example .env
```

### 2. Stand Up the Infrastructure
Boot up the Docker multi-network bridge in detached mode:
```bash
./vendor/bin/sail up -d
```

### 3. Initialize Database Ecosystem
Execute seeders and primary migration files across all central and tenant boundaries:
```bash
./vendor/bin/sail artisan migrate --path=database/migrations_central
./vendor/bin/sail artisan tenants:migrate
```

### 4. Spin Up the Background Queue Worker
Run the background queue listener daemon to handle in-memory PDF generation and Stripe event dispatch tracks:
```bash
./vendor/bin/sail artisan queue:work
```

### 5. Automated Verification (JetBrains HTTP Client Vectors)
All critical business cycles—ranging from cross-domain login and webhook payment simulations to automated PDF byte-stream download checks—are fully mapped inside the **`api-tests.http`** pipeline file. Open it inside PhpStorm to execute end-to-end integration test runs.

---

## 👨‍💻 Author & Contact

* **Name:** Mateusz Grzegorz Lipiński
* **Role:** Backend Software Engineer / SaaS Architect
* **LinkedIn:** [Your LinkedIn Profile URL Here]
* **Email:** [mglipinski92@gmail.com](mailto:mglipinski92@gmail.com)
* **GitHub Portfolio:** [https://github.com/lipa71](https://github.com/lipa71)
