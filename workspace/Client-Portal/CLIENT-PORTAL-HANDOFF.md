## Client-facing sign-up and dashboard prototype

The client experience is separate from the agent workspace and is reached through **Client preview** in the agent workspace top bar, or directly at `/client/signup`. `/client` redirects to sign-up. A **Switch to agent workspace** link returns to the agent side.

1. On **Client sign-up**, enter a full name, a Philippine mobile number as 10 digits after `+63`, and the demo agent invite code `ANA-4821`. Email is optional. The code field is case-insensitive; invalid or missing fields show inline errors. There is no password, OTP, real account creation, or backend.
2. Submit to connect the in-memory demo profile to **Ana Reyes** and open `/client/dashboard`. Alternatively, choose **Skip sign-up · view sample dashboard** to use the fictional Mila Santos account. Going directly to the dashboard with no profile also seeds the sample account.
3. The client dashboard shows the connected agent, a masked phone number, the sample Life policy with premium due **2 Dec 2026**, and the separate Motor policy with renewal due **10 Dec 2026**. Policy numbers are masked. It does not expose agent-only notes or private insurer filing references.
4. **Message Ana**, **Ask your agent**, and **Ask about payment** open a simulated message dialog; **Request a call** opens a simulated time-preference dialog. Submitting any contact action displays a confirmation that nothing was sent, called, or saved.
5. **Change account** clears the in-memory profile and returns to sign-up. The client portal stores no signup fields in localStorage or a server. Do not enter real client details into this prototype.

| Client action | Fields read or written | Validation | Resulting state |
|---|---|---|---|
| Create demo profile | `fullName`, `mobile`, optional `email`, `agentCode` | Name required (at least 2 characters); normalize a Philippine mobile to exactly 10 local digits after `+63`; email is optional but must be valid if supplied; invite code must match `ANA-4821` (case-insensitive) | Store the profile in memory only and route to client dashboard; invalid fields stay on sign-up with inline errors |
| Skip sign-up / sample account | None | N/A | Seed fictional Mila Santos profile and route to client dashboard |
| Open client dashboard directly | Existing in-memory profile, if any | N/A | Use current profile or start the sample account if the browser session has none |
| View client policies | Client-safe sample policy product, masked number, status, and due date | N/A | Show Life premium due 2 Dec 2026 and Motor renewal due 10 Dec 2026; do not display agent notes or private filing references |
| Message agent / Ask your agent / Ask about payment | Optional prefilled message in the unsent dialog | Message must contain text before the simulated send button is enabled | Close dialog and show that nothing was transmitted or stored |
| Request a call | Preferred time choice | Any listed preference is accepted | Close dialog and show that no request or call was made |
| Change account | Current in-memory client profile | N/A | Clear the client profile and return to `/client/signup` |
