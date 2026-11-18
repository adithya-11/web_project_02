# Schedule and communication plan

## Estimated hours by phase

Draft estimates for the proposal's sprint plan (Nov 9–17, 2026). Adjust the numbers to what the team agreed, and fill in the **Actual** column from `time_log.md` before the presentation.

| Phase | Work | Aishwarya | Adithya | Nirupam | Team total (est.) | Actual |
|---|---|---|---|---|---|---|
| Design | Backlog, wireframes, session data plan, page map, pseudocode, UML | 5 | 4 | 5 | 14 | |
| Coding: core | Register, login, logout, guard, user file | 1 | 7 | 1 | 9 | |
| Coding: game | Puzzle generators, room flow, scoring, escape, leaderboard | 8 | 3 | 1 | 12 | |
| Coding: Sentinel | Hints, feedback, lock, clearance, adaptive tiers, report | 9 | 1 | 1 | 11 | |
| Coding: UI | Layout, responsive CSS, animations, accessibility, themes | 1 | 1 | 9 | 11 | |
| Testing | Test plan, 9 Sentinel cases, UI and security checks, bug fixes | 3 | 3 | 5 | 11 | |
| Presentation | README, slides, demo script, recording | 3 | 3 | 3 | 9 | |
| **Total** | | **30** | **22** | **25** | **77** | |

## Sprint calendar (from the proposal)

| Sprint | Dates | Goal |
|---|---|---|
| 1. Foundation | Nov 9 | Backlog of 15+ stories, repo skeleton, CODD write test |
| 2. Core | Nov 10 | Sign up, log in, reach a protected page |
| 3. Game loop | Nov 11–12 | Full run on CODD; tests 1–4 pass |
| 4. Adaptive and polish | Nov 13–14 | Tests 5–9 pass; 360 px check passes |
| 5. Presentation | Nov 15–16 | 20–30 minute video recorded |
| 6. Finish | Nov 17–18 | Links checked in a private window; everyone has uploaded |

## Communication plan

- **Primary tool:** Discord server for team Session Breakers: a `#standup` channel, a `#dev` channel and a voice channel.
- **Daily stand-up:** 9:30 PM ET on Discord voice, 15 minutes. Each of us answers: what I finished, what I'm doing next, what's blocking me. Anyone who can't attend posts their answers in `#standup` beforehand.
- **Class time:** play the latest CODD build together, then pair on whatever is blocked.
- **Decisions and tasks:** recorded on the GitHub Projects board (Backlog → To Do → In Progress → In Review → Done) and in `sprint_log.md`, so everyone can see them.
- **Code:** one branch per task and a pull request reviewed by a second member before merging into `main`.
- **Help:** questions to the instructor by email or in person; anything blocking for more than a day is raised at the next stand-up.
