# Enterprise B2B Multi-Tenant SaaS Platform

### 🚀 Lead Software Engineer: Mateusz Grzegorz Lipiński
**A production-grade, highly scalable, and secure B2B Multi-Tenant SaaS Platform built using Laravel 11, designed for international financial management and EU-compliant cross-border invoicing in the Euro zone (€).**

---

## 🏗️ Core Architectural Pillars

### 1. Advanced Multi-Tenancy & Data Isolation (Database-Per-Tenant)
* **Dynamic Routing:** Tenants are resolved dynamically at the network layer based on custom subdomains (e.g., `company_1_test.localhost`).
* **Complete Context Isolation:** Driven by `stancl/tenancy`, the system intercepts database connections on the fly, switching the active PDO connector context to an isolated tenant schema, eliminating any risks of cross-tenant data bleeding.

### 2. Multi-DB Cross-Boundary Authentication & RBAC
* **Cross-DB Verification:** Authentication guards securely query the central Landlord database to validate master owner footprints, then automatically initialize a cryptographic bearer token profile using `laravel/sanctum` within the active tenant stack.
* **Granular RBAC Middleware:** Restricts internal resource access based on corporate roles (`admin`, `manager`, `accountant`, `viewer`).

### 3. Event-Driven Asynchronous Billing Architecture (Stripe Metered System)
* **Event Dispatching:** Successfully saving an invoice triggers an internal `InvoiceCreated` event infrastructure.
* **Stripe Usage-Based Tracking:** An asynchronous listener taps into the background database queue (backed by Redis) to register a real-time consumption event (+1 invoice factor) inside the central landlord `meter_events` table, feeding Stripe Metered Billing APIs.
* **In-Memory PDF Mail Streaming:** A secondary listener compiles compliant bilingual/English PDF commercial invoice sheets directly inside the container's RAM using DomPDF and attaches the binary bytes directly to SMTP mail envelopes dispatched asynchronusly to **Mailpit**.

### 4. Hardened Security Shield Gates & Audit Trails
* **Global Billing Gate Middleware:** A composite network screen (`TenantBillingGate`). It terminates calls immediately with **401 Unauthorized** exceptions if bearer arrays are missing. Furthermore, it parses realtime Stripe cancellation signals (`customer.subscription.deleted`) to block unpaid workspaces with rigid **402 Payment Required** web protocol states.
* **Compliance Audit Trails:** Using a custom reusable `LogsActivity` trait, the active tenant context automatically records deep digital fingerprints (including explicit IPv4/IPv6 proxies and browser User Agents) of sensitive actions inside an immutable `activity_logs` database matrix.
* **Hardened Rate Limiting:** Outfitted with an infrastructure-level `throttle:tenant_api` shield restricting heavy calls to `60 requests per minute` per token to block DoS and brute-force actors.

---

## 🧪 Automated Testing Suite (100% Green Coverage)

The platform is backed by a bulletproof feature and security testing suite powered by the **Pest Framework**. All tests enforce clean database lifecycle states by executing raw SQL purge hooks (`DROP DATABASE IF EXISTS`) to completely bypass multi-tenant schema compilation locks.

### Active Test Registry
* `TenantIsolationTest` — Verifies absolute data containment. Proves that Cross-Tenant resource interception (BOLA prevention) is mathematically impossible at the Eloquent query layer.
* `AuthTenantTest` — Validates authentication security boundaries, ensuring fake credentials are strictly rejected by core application drivers.
* `InvoiceTenantTest` — Confirms that unauthenticated external requests are dropped with precise network response layers.
* `CentralSubscriptionArchitectureTest` — Validates the entire Landlord subscription synchronization layer, matching multi-item multi-tenant plans with active Stripe structures.

To execute the global automated test suite, run:
```bash
./vendor/bin/sail artisan test
```

---

## 👨‍💻 Author & Contact

* **Name:** Mateusz Grzegorz Lipiński
* **Role:** Backend Software Engineer / SaaS Architect
* **Email:** [mglipinski92@gmail.com](mailto:[mglipinski92@gmail.com)
* **GitHub:** [Link here](https://github.com/lipa71)
* **LinkedIn:** [Link here](https://www.linkedin.com/in/mateusz-lipi%C5%84ski-165956236/?isSelfProfile=true)
