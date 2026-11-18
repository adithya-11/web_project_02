# Test plan and results

Tested on November 18, 2026 with PHP 8.3 (`php -S` and `router.php`) and headless Chrome at 1366 px and 360 px widths. Answers were checked against the server's puzzle generator for the live session.

## Sentinel test cases (from the proposal)

| # | Input | Expected | Actual | Result |
|---|---|---|---|---|
| 1 | Room 2, shift 5, player decodes with 3 | "Right direction, wrong amount", one attempt, −50 | "Right direction, wrong amount. You shifted back 3 places. 50 points deducted." Wrong count +1 | Pass |
| 2 | Ask for a hint four times | Hints 1–3 in order at −100 each; the 4th says none left and costs nothing | Score 1000 → 900 → 800 → 700 → 700. 4th: "No hints left for this room. Nothing was deducted." | Pass |
| 3 | Three wrong answers, then a forged POST within 30 s | Lock shown, forged answer refused, answers work after 30 s | Form disabled with countdown bar. Forged `fetch` POST returned "sealed". Correct answer accepted after 31 s | Pass |
| 4 | Blank or non-numeric answer in Room 1 | Error message, no attempt or penalty | "Enter a whole number, such as 42 or -7. Nothing was deducted." Score unchanged | Pass |
| 5 | Room 1 in 42 s, no hints, no mistakes | Room 2 moves to Tier 3 with an explanation | "Room 2 moves up to Tier 3 because you cleared Room 1 in 42 seconds without hints." | Pass |
| 6 | Room 1 in exactly 60 s, no hints | Room 2 stays at Tier 2 | "Room 2 stays at Tier 2 because you solved Room 1 in 1:00 with 0 wrong answers and 0 hints." | Pass |
| 7 | Fast clean solve while at Tier 3 | Next room stays at Tier 3 | "Room 3 stays at Tier 3, the top tier, because you cleared Room 2 in 30 seconds without hints." | Pass |
| 8 | 200 s solve at Tier 1 | Next room stays at Tier 1 | "Room 3 stays at Tier 1, the easiest tier, because Room 2 took you 3:20." | Pass |
| 9 | Full run in 250 s with two hints | Profile is Hint-Reliant | Hint-Reliant | Pass |

## Extra feedback checks

| Input | Sentinel's message |
|---|---|
| Room 1, answer one step too far | "You jumped one step too far ahead. Sentinel wants the very next number." |
| Room 1, answer above the target | "Too high. The next number is smaller than that." |
| Room 2, letters shifted forward | "You shifted the wrong way. Move the letters backward, not forward." |
| Room 2, two of three words right | "2 of 3 words are already right." |
| Room 3 (Tier 2 or 3), mirrored grid | "You mirrored the grid. Sentinel expected it to turn, not flip." |
| Room 3, other wrong grid | "7 of 9 cells are right." |

## UI test cases

| # | Check | Result |
|---|---|---|
| 1 | Wrong password: one general error, username kept | Pass |
| 2 | Room page at 360 px: no sideways scroll, Sentinel panel below the puzzle | Pass (`scrollWidth` 360) |
| 3 | Keyboard only: tab through the pattern lock, Space toggles, focus outline visible | Pass |
| 4 | Registration errors appear next to each field and the username is kept | Pass |
| 5 | `prefers-reduced-motion`: animations stop, the door becomes a fade | Pass |
| 6 | No browser console errors on any page | Pass |

## Security checks

| Check | Result |
|---|---|
| Open `escape.php` mid-run | Redirected to `room.php` |
| `GET hint.php` or `GET logout.php` | 405 Method not allowed |
| POST without a CSRF token | 403 |
| Theme cookie set to an unknown value | Falls back to the dark theme |
| Refresh after a correct answer or on the escape screen | No double submit; the run is recorded once |
| Request `data/users.json` | Blocked (`.htaccess` on Apache, `router.php` locally) |
