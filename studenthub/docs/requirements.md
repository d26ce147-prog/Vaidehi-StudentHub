# Practical 1 — Requirements
**Problem:** Colleges track students and events with spreadsheets. StudentHub centralises both.

**Roles:** Visitor, Student, Admin.

**Functional requirements**
- FR1 Visitors can view Home, About, Events, FAQ, Contact, Feedback.
- FR2 Visitors can register (validated on client and server) and log in.
- FR3 Students can view dashboard, edit profile, register/cancel for open events.
- FR4 Admins can CRUD students (search, filter, CSV export, soft delete).
- FR5 Admins can CRUD events with poster upload.
- FR6 Admin dashboard shows statistics, latest registrations, charts, audit log.
- FR7 AJAX events page with live search.

**Non-functional:** responsive, accessible (WCAG basics), secure (hashing, CSRF, RBAC), maintainable folder structure.

**Modules:** Public site · Auth · Student module · Event module · Admin module · API.
