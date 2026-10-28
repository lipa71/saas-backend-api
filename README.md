# Enterprise B2B Multi-Tenant SaaS Platform

### 🚀 Lead Software Engineer: Mateusz Grzegorz Lipiński
**A production-grade, highly scalable, and secure B2B Multi-Tenant SaaS Platform built using Laravel 11, designed for international financial management and EU-compliant cross-border invoicing in the Euro zone (€).**

The entire infrastructure was designed and implemented by me from scratch to solve real-world architectural challenges of corporate software: strict data isolation, multi-database cross-boundary authentication, and an event-driven asynchronous usage-based billing pipeline. The entire ecosystem is fully containerized inside a custom Docker layout.

---

## 🏗️ Core Architectural Pillars

### 1. Advanced Multi-Tenancy & Data Isolation (Database-Per-Tenant)
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

## 🧪 Comprehensive Automated Testing Ecosystem

The platform is engineered with a dual-layer testing architecture to ensure extreme business stability and full regression protection across both logic layers and raw HTTP protocols.

### Layer A: Real-Time HTTP Pipeline Automation (`api-tests.http`)
Designed and integrated an end-to-end integration testing suite inside the `api-tests.http` file utilizing the JetBrains HTTP Client.
* **Token Extraction:** Programmed custom JavaScript post-request handlers that automatically parse successful invitation creation logs, cutting the cryptographic token out of the raw `activation_url`.
* **State Management:** The script dynamically injects extracted variables straight into the environment's `{{invitation_token}}` array, automating registration pipelines and end-to-end multi-tenant validation runs without manual clipboard operations.
* **Payment Simulation:** Includes complete mock tracks mimicking raw inbound Stripe payment payloads and cancellation webhooks to test system defense parameters.

### Layer B: SecOps Feature Suite (Pest Framework)
Backed by a bulletproof feature and security testing layout achieving 100% green coverage across all active enterprise workflows.
* `TenantIsolationTest` — Verifies absolute data containment. Proves that Cross-Tenant resource interception (BOLA prevention) is mathematically impossible at the Eloquent query layer.
* `AuthTenantTest` — Validates authentication security boundaries, ensuring fake credentials are strictly rejected by core application drivers.
* `InvoiceTenantTest` — Confirms that unauthenticated external requests are dropped with precise network response layers.
* `CentralSubscriptionArchitectureTest` — Validates the entire Landlord subscription synchronization layer, matching multi-item multi-tenant plans with active Stripe structures.
* **Teardown Engineering:** Every feature iteration runs an automated raw SQL cleanup script (`DROP DATABASE IF EXISTS`) to fully clear temporary tenant test database footprints from the MySQL server, completely eliminating memory blocks.

---

## 🎛️ Docker Stack & Infrastructure

The application runs inside a containerized local development ecosystem managed via Laravel Sail:
* **`laravel.test`** - PHP 8.4/8.5 app container layer executing logic and API kernels.
* **`mysql`** - Database engine split into central landlord configuration and isolated tenant partitions.
* **`redis`** - High-performance in-memory key-value database acting as the core job broker distributing asynchronous queue payloads.
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

## 🛠️ Fresh Installation & Verification

Follow these precise steps to spin up the entire multi-database skunkworks application infrastructure from a freshly cloned repository:

### 1. Environment Synchronization
Clone the repository, enter the directory, and duplicate the production environment blueprint:
```bash
cp .env.example .env
```

### 2. Install Composer Dependencies (Bootstrap via Temporary Container)
Since the `vendor/` directory is missing upon a fresh clone, use this official localized Docker container to securely download and install Composer modules without a local PHP runtime instance:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Spin Up the Infrastructure Stack
Boot up the Docker skunkworks bridge networks in background detached mode:
```bash
./vendor/bin/sail up -d
```

### 4. Application Cryptographic Setup
Generate the encrypted app runtime handshake signatures:
```bash
./vendor/bin/sail artisan key:generate
```

### 5. Landlord Database Setup & Central Seeding
Migrate the central landlord configuration system tables and seed the structural dictionaries, VAT plans, and mock enterprise workspace parameters:
```bash
./vendor/bin/sail artisan migrate --path=database/migrations_central
./vendor/bin/sail artisan db:seed
```

### 6. Corporate Tenant Provisioning (`company_a`)
Run the tenant tenant migrations scheme to provision the core corporate workspaces and build out separate company isolation schemas (e.g., `company_a` storage arrays):
```bash
./vendor/bin/sail artisan tenants:migrate
```

### 7. Run Background Queue Processors
Fire up the Redis message queue workers daemon to dynamically capture background streams (PDF compiling and Stripe usage metrics logging):
```bash
./vendor/bin/sail artisan queue:work
```

---

## 🧪 Testing Environment Orchestration (Pest Coverage)

To run the automated **Pest feature suite** securely without clashing against your active local development databases, initialize a separate text context ledger:

### 1. Create Testing Configurations File
Duplicate your production environment variables specifically targeting the isolated testing database matrix:
```bash
cp .env.example .env.testing
```

### 2. Configure `.env.testing` Keys
Ensure your `.env.testing` layout has the following parameters set to drop data safely into transactions and connect seamlessly to the internal MySQL Docker engine:
```env
APP_ENV=testing

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=testing
DB_USERNAME=root
DB_PASSWORD=password

QUEUE_CONNECTION=sync
```

### 3. Generate Testing Application Encryption Key
Generate a distinct cryptographic key directly inside your testing layout file to authorize secure Sanctum bearer tokens during feature runs:
```bash
./vendor/bin/sail artisan key:generate --env=testing
```

### 4. Execute Automated Tests
Run the global automated feature suite to achieve 100% green coverage tracking parameters:
```bash
./vendor/bin/sail artisan test
```


## 👨‍💻 Author & Contact

* **Name:** Mateusz Grzegorz Lipiński
* **Role:** Backend Software Engineer / SaaS Architect
* **Email:** [mglipinski92@gmail.com](mailto:mglipinski92@gmail.com)
* **LinkedIn:** [linkedin.com/in/mateusz-lipinski-165956236](https://linkedin.com/in/mateusz-lipinski-165956236)
