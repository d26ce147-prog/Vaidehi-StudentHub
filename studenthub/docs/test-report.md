# Practical 15 — Test Report (fill in screenshots/results while demoing)
| # | Test | Expected | Result |
|---|---|---|---|
| 1 | Register with invalid email/weak password | Inline errors, no submit | |
| 2 | Register duplicate email | "already registered" | |
| 3 | Login with wrong password | Generic error | |
| 4 | Login as student → open /students.php | Redirected, access denied | |
| 5 | Idle 15 min then click | Session expired, login page | |
| 6 | Remember-me, close browser, reopen | Still logged in | |
| 7 | Admin add/edit/delete student | Success flash, DB updated | |
| 8 | Upload .php renamed as .png | Rejected (MIME check) | |
| 9 | Upload > 2 MB poster | Rejected | |
| 10 | AJAX add/edit/delete event | Table updates without reload | |
| 11 | Mobile / tablet / desktop widths | Layout adapts | |
| 12 | Keyboard-only navigation, skip link | All controls reachable | |
