# Bug log

Bugs found in review and testing during the final sprint, in the order they were found. Each has a test that now passes.

| # | Found by | Bug | Severity | Fix | Commit |
|---|---|---|---|---|---|
| 1 | Proposal review | No `hint.php` or `includes/sentinel.php`: the Request Hint feature promised in the proposal didn't exist | High | Added both files; hints are charged through the existing `score_breakdown()` | Add Request Hint form… |
| 2 | Proposal review | Every wrong answer gave the same message: "That answer did not open the lock" | High | `sentinel_feedback()` analyses each mistake per room | Replace the generic wrong-answer message… |
| 3 | Proposal review | `advance_run()` always opened the next room at Tier 2, so the graduate rule never ran | High | `room.php` computes t, w, h and passes the chosen tier in | Make Sentinel choose the next room's tier |
| 4 | Proposal review | The escape screen had no Sentinel report and the leaderboard had no profile | Medium | Added the report, time bars, tier reasons and profile | Add Sentinel's report… |
| 5 | Proposal review | `wrong_streak` and `locked_until` were stored but never checked, so there was no lockout | High | Lock checked in PHP before any answer is read; forged POSTs are refused | Enforce the 30-second lockout… |
| 6 | Test case 3 | A forged POST sent during the lock could still be scored | High | Same fix as #5; confirmed with a scripted `fetch` POST | Enforce the 30-second lockout… |
| 7 | Code review | `sentinel.php` loads `scoring.php` while pages used `require`, which caused a "cannot redeclare function" fatal error | High | All shared includes switched to `require_once` | Add Request Hint form… |
| 8 | Code review | The tier reason was shown once and disappeared on refresh | Low | Reason stored on the next room in the session | Make Sentinel choose the next room's tier |
| 9 | Browser test | Large headings rendered as garbled blocks in Chrome (`background-clip: text` on a parent whose words animate) | Medium | Gradient moved onto each animated word | Enforce the 30-second lockout… (UI pass) |
| 10 | Browser test | The vault door faded out during the whole animation instead of only at the end | Low | Opacity pinned to 1 on the earlier keyframes | UI pass |
| 11 | Browser test | The "Grid 4" legend overlapped the toggle cells at ≥ 600 px | Low | Negative margin limited to the direct child label | UI pass |
| 12 | Browser test | Console 404 for `/favicon.ico` | Low | Inline SVG favicon in the header | UI pass |
| 13 | Browser test | The drifting room text widened the page on desktop | Low | Backdrop clipped to the hero; `html { overflow-x: clip }` | Add a second CSS-only motion layer |
| 14 | Browser test | The lock siren dot overlapped the lock message | Low | Padding on the message | Add a second CSS-only motion layer |
| 15 | Copy review | Tier sentences read awkwardly ("because you cleared it … in Room 1") | Low | Reworded to "because you cleared Room 1 in 42 seconds without hints" | Make Sentinel choose the next room's tier |

## How we tracked bugs

1. Whoever finds a bug opens a GitHub issue with the steps to reproduce it, what was expected and what happened.
2. The Product Owner sets its priority at stand-up (High: breaks a rubric requirement or security; Medium: wrong output; Low: visual).
3. The fix goes on a branch, and its commit message names the bug.
4. The matching row in `test_plan.md` is re-run before the issue is closed.
