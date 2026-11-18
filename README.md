# Vault Sentinel

**Team Session Breakers** · CSC 4370/6370 Project 2 · Topic 04, The PHP Vault

An escape game with three PHP-checked puzzle rooms, guarded by a rule-based AI called Sentinel that gives graded hints and adjusts each room to how the player is doing.

- **Live on CODD:** https://codd.cs.gsu.edu/~anuni1/Project02_VaultSentinel/
- **Repository:** https://github.com/nuniaishwaryachowdary17/Project02_VaultSentinel

## Team

| Name | Student ID | Main responsibility | Scrum role |
|---|---|---|---|
| Aishwarya Nuni | 002941257 | Game logic and the Sentinel AI: puzzles, scoring, room flow, escape screen | Product Owner, Project Leader |
| Adithya Godavarthi | 002909080 | Login, registration, sessions, security checks and the leaderboard | Scrum Master |
| Nirupamarayudu Chitturi | 002931435 | Page layout, CSS, animations, accessibility and the test plan | Developer, QA Lead |

## How to play

1. Register, then log in.
2. In the Vault Entry Hall, press **Enter the vault**.
3. Solve the three rooms in order:
   - **The Ledger**: find the next number in a sequence.
   - **The Cipher Gate**: decode a phrase whose letters were shifted forward.
   - **The Pattern Lock**: tick the cells of the fourth grid that follows the hidden rule.
4. The escape screen shows your score, Sentinel's report and your profile. Then check the leaderboard or play again.

A full run takes five to ten minutes.

## Scoring

```
score = 1000
      - 50 for each wrong answer
      - 100 for each hint
      + 10 for each second under the 10-minute par (at most 500)
      + 75 for each tier above Tier 1, per solved room
      (never below 0)
```

Blank or badly formed answers are turned away with a message and cost nothing.

## Sentinel, the AI feature

Sentinel only reads game state stored in `$_SESSION`. It never solves anything for the player. All of its rules live in `includes/sentinel.php`.

**Baseline**

- **Mistake feedback.** A wrong answer is compared with notes kept alongside the puzzle. Room 1 says too high, too low, or that you skipped ahead a step. Room 2 says you shifted the right way by the wrong amount, shifted the wrong way, or how many words are already right. Room 3 says you mirrored the grid instead of turning it, or how many of the nine cells are right.
- **Graded hints.** Three per room, from a nudge to a near give-away, at 100 points each. A fourth request costs nothing and says none are left.
- **Lock.** Three wrong answers in a row seal the answer box for 30 seconds. The lock is checked on the server, so a forged POST during the lock is refused. The page refreshes itself with a `<meta>` refresh when the lock ends.
- **Security Clearance meter.** Starts at 50%, +15 per clean solve (no wrong answers, no hints), −5 per wrong answer, −10 per hint, kept between 0 and 100.

**Graduate extension: adaptive tiers**

Room 1 always starts at Tier 2. When a room is cleared, Sentinel uses solve time `t` (seconds), wrong answers `w` and hints `h`:

```
if t < 60 and h = 0 and w <= 1      -> next tier = current + 1 (max 3)
else if t > 180 or h >= 2 or w >= 4 -> next tier = current - 1 (min 1)
otherwise                           -> next tier stays the same
```

The next room opens with one plain sentence explaining the decision, for example *"Room 2 moves up to Tier 3 because you cleared Room 1 in 42 seconds without hints."*

The escape screen shows each room's tier, time, wrong answers and hints, CSS bars comparing time per room, every tier change with its reason, and a one-word profile:

- **Hint-Reliant**: two or more hints in the run (checked first).
- **Speed Runner**: finished in under five minutes with at most one hint.
- **Methodical Solver**: everyone else.

## Security

- `session_start()` runs once, in `includes/config.php`, with strict mode, `HttpOnly` and `SameSite=Lax` cookies.
- Every form is POST with a CSRF token (`includes/csrf.php`), and every handler redirects afterwards (Post/Redirect/Get), so refreshing never resubmits an answer or records a score twice.
- Input is read with `filter_input()` and validated on the server. All output goes through `htmlspecialchars()` via `e()`.
- Passwords are stored only as `password_hash()` output in `data/users.json`, written under a file lock with an atomic rename. `data/.htaccess` denies web access to that folder.
- A fresh session ID is issued at login. Protected pages time out after 30 idle minutes.
- The room number only exists in the session. Opening `escape.php` or `room.php` out of order sends the player back to where they really are.
- The only cookie is the colour theme, checked against an allow-list before use.

## Design and accessibility

The interface is pure HTML and CSS, with no JavaScript. The motion is all CSS: drifting dust particles, a rotating searchlight, a perspective grid floor, film grain, word-by-word heading reveals, gilded animated panel borders, and a 3D vault door that spins its wheel and swings open after every solved room.

- Mobile first, with Grid for page layout, Flexbox inside components, and breakpoints at 600 px and 960 px. No sideways scrolling at 360 px.
- Every control has a label, focus is always visible, and the pattern grid works from the keyboard.
- With **prefers-reduced-motion** turned on, every animation stops and the door becomes a simple fade.
- There is a **High contrast** theme in the Entry Hall, which also turns off the decorative effects.

## File map

```
index.php        login
register.php     new accounts
lobby.php        Vault Entry Hall: start, resume or abandon a run; theme
room.php         the three puzzle rooms
hint.php         hint requests (POST only), then back to the room
escape.php       end of run: score, Sentinel's report, profile
leaderboard.php  top ten runs this session
team.php         team introduction
logout.php       ends the session (POST only)
router.php       router for `php -S` local testing only; blocks data/ and includes/
includes/        config, auth guard, CSRF, header/footer, user store,
                 puzzles, run flow, scoring, sentinel
css/             style.css (base, layout, backdrop), rooms.css (game, door, report)
data/            users.json (created on first sign-up), .htaccess
docs/            test_plan.md
```

## Setup

**On CODD**

1. Upload the folder to `~/public_html/Project02_VaultSentinel/`.
2. Make the data folder writable by the web server: `chmod 733 data` (or `777` if sign-up still fails).
3. Check that `https://codd.cs.gsu.edu/~anuni1/Project02_VaultSentinel/data/users.json` returns 403 Forbidden.

**Locally** (PHP 8.0 or newer)

```
php -S localhost:8000 router.php
```

Set `VAULT_DATA_DIR` to keep accounts outside the project folder if you like.

## Testing

All nine Sentinel test cases from the proposal, plus the UI and security checks, are in [docs/test_plan.md](docs/test_plan.md) with their actual results.

## Known limitations

- One room is a small sample, and a lucky puzzle can push a player up a tier they aren't ready for. That's why the tier only moves one step at a time.
- The timer can't tell thinking from stepping away.
- The 60- and 180-second limits are our own estimates.
- Sentinel only recognises the mistakes we wrote notes for. Any other wrong answer gets a cell or word count, or a plain "not quite".
- The leaderboard holds this browser session's runs only, as the topic specifies.

## AI tools used

We used Claude (Anthropic) to help draft the hint and feedback wording, the CSS animation for the vault door and backdrop, and a test harness that plays full runs in a headless browser. We reviewed every change and can explain each file in the presentation.
