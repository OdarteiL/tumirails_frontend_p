## 🧭 **Decisions Summary**

This document summarizes key technical and architectural decisions made during the design and development of the **Project Management and Payment System**.
It provides the reasoning, alternatives considered, and trade-offs accepted for each major decision.

---

### **1. Payment Model: Milestone-Based with Partial Payments**

**Decision:**
Adopt a **milestone-based payment model** where each project is divided into milestones, and each milestone can be paid in **installments** (partial payments).

**Reasoning:**

* Reflects real-world project workflows where clients commit funds progressively.
* Offers flexibility to both clients and service providers.
* Enables more granular tracking of payment progress.

**Alternatives Considered:**

* **Full upfront payment:** Simpler but less flexible and riskier for clients.
* **Pure installment-based model:** Lacks milestone clarity and project structure.

**Trade-offs:**

* More complex data model (requires linking `installments` to `milestones`).
* Requires payment reconciliation logic to track when milestones are fully paid.

---

### **2. Entity Relationships and Domain Modeling**

**Decision:**
Introduce distinct entities:

* `Project` → has multiple `Milestones`.
* `Milestone` → has multiple `Payments` (installments).
* `Estimation` → linked to `Project`, provides projected cost and resource breakdown.
* `RecommendedHardware` → linked to `Estimation`, defines environment and performance requirements.

**Reasoning:**

* Ensures modular data structure that mirrors business reality.
* Allows analytics and forecasting to be performed at project, milestone, and payment levels.
* Keeps estimation and hardware configuration independent but relatable.

**Trade-offs:**

* More complex joins and queries for aggregating project summaries.
* Requires additional validation logic to prevent inconsistencies.

---

### **3. Estimation Workflow**

**Decision:**
Estimation is performed at the **project level** and contains details like cost, duration, resource allocation, and dependencies.

**Flow Example:**

1. User submits project details.
2. System creates an `Estimation` record.
3. Estimation API calculates projected cost, time, and required resources.
4. Milestones are created based on estimation.
5. Each milestone can have hardware recommendations linked.

**Reasoning:**

* Establishes a logical flow from planning → estimation → execution → payment.
* Makes it easier to version estimations if project parameters change.

**Trade-offs:**

* Requires synchronization when estimates change after project start.

---

### **4. Recommended Hardware Module**

**Decision:**
Include a `RecommendedHardware` section to define and manage hardware requirements (e.g., GPU type, memory, CPU cores, or compute environment).

**Flow Example:**

1. During estimation, the system determines the resources required.
2. Hardware recommendations are generated automatically or configured manually.
3. They can later inform deployment, infrastructure setup, or DevOps provisioning.

**Reasoning:**

* Improves transparency for clients and DevOps teams.
* Helps predict infrastructure costs early in the project lifecycle.

**Trade-offs:**

* Requires integration with infrastructure pricing APIs (optional).
* Adds maintenance overhead if hardware options evolve frequently.

---

### **5. API Design: OpenAPI-Driven Development**

**Decision:**
Use **OpenAPI 3.1 specification** to design and document the REST API.

**Reasoning:**

* Encourages contract-first development.
* Simplifies backend-frontend synchronization.
* Supports auto-generation of SDKs and client documentation.

**Trade-offs:**

* Requires discipline to keep documentation up to date.
* Slightly longer setup time before coding starts.

---

### **6. Versioning and Change Tracking**

**Decision:**
Maintain version history for `Estimation` and `RecommendedHardware` entities.

**Reasoning:**

* Enables auditing and comparison between old and new configurations.
* Prevents loss of historical estimation accuracy data.

**Trade-offs:**

* More complex database structure (soft deletes, historical tables, or event sourcing).
* Slightly higher storage overhead.

---

### **7. Validation and Business Logic Layer**

**Decision:**
Centralize validation and business rules within the Laravel backend service layer.

**Reasoning:**

* Keeps API predictable and consistent.
* Simplifies front-end integration (Angular).
* Allows shared validation utilities for entities like Milestones and Payments.

**Trade-offs:**

* More backend load compared to front-end-only validation.
* Requires clear schema synchronization with OpenAPI definitions.

---

### **8. Future Enhancements (To Be Decided)**

* Support for **dynamic pricing adjustments** during project progress.
* Integration with **third-party payment providers** (Stripe, Paystack, etc.).
* Predictive analytics for cost and timeline accuracy.
* AI-based recommendation for **optimal milestone segmentation**.

---

### **9. Documentation & Governance**

**Decision:**

* Maintain architectural diagrams in `/docs/architecture/`.
* Keep API specs in `/api/openapi.yaml`.
* Use `decisions-summary.md` for architectural decision tracking.

**Reasoning:**

* Improves onboarding and change visibility.
* Ensures stakeholders understand the system evolution.

---

### **10. Tools & Frameworks**

| Component  | Tool/Framework   | Reason                                       |
| ---------- | ---------------- | -------------------------------------------- |
| API Design | OpenAPI 3.1      | Contract-first approach                      |
| Backend    | Laravel          | Modular, scalable                            |
| Frontend   | Angular.js           | Lightweight and flexible for dashboards      |
| Database   | PostgreSQL       | Supports complex relationships & JSON fields |
| ORM        | Eloquent         | First-class ORM in Laravel                   |
| CI/CD      | GitHub Actions   | Automated testing and deployment             |
| Infra      | AWS / Docker     | Scalable deployment and containerization     |
