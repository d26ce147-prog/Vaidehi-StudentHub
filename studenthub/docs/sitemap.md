# Sitemap & wireframe notes
```
Home ─┬─ About
      ├─ Events (JSON/Fetch)
      ├─ FAQ
      ├─ Contact (CSV)
      ├─ Feedback (JSON)
      ├─ Register ─► Login ─┬─ Student: Dashboard · Profile · Live Events
      │                     └─ Admin: Admin Dashboard · Students · Manage Events · Audit Log
      └─ Logout
```
Wireframe (all pages): header with logo + nav + theme toggle → notification banner → breadcrumb → main content (cards/grid/forms/tables) → footer.
Navigation flow: Visitor → Register → Login → role-based redirect (admin → admin/dashboard.php, student → dashboard.php).
Suggested Git workflow: one commit per practical, e.g. `git commit -m "Practical 5: registration validation"`.
