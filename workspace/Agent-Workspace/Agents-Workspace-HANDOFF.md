# Insurance Agent Workspace — design handoff

## Prototype boundary

This is a Vue 3 + TypeScript + Tailwind frontend prototype using fictional, browser-local mock data. There is no Laravel API, real insurer connection, notification service, or customer contact action. Calls and texts open a demo dialog and do not dial or send. Workspace changes persist in local storage under `insurance-agent-workspace-v1`; use **Reset demo** on Today to restore the seeded Wednesday state. Phone numbers are fictional, stored locally for mobile search, and displayed masked.

The layout starts on **Today**, uses Manila dates, and is designed to reflow from a 390 px phone to a 1440 px desktop. The frontend entry points are `src/App.vue`, `src/router.ts`, screen components in `src/views/`, mock data in `src/seed.ts`, and shared state/actions in `src/stores/workspace.ts`.

## Scenario: Wednesday to Friday

1. Today starts on **Wednesday, 25 November 2026**. The upcoming policy list contains Mila’s separate life premium (**due 2 Dec**) and motor renewal (**due 10 Dec**).
2. Open Mila from **Clients**, or use **Open Mila’s details**. The client record shows her motor and life policies, prior conversation notes, and the thumb-reachable **Call**, **Text**, and **Log contact** actions.
3. Choose **Log contact**. For Mila’s motor policy, the outcome starts at **Reached**, the note is prefilled with **“Ma’am, Friday na lang po, mga 4 PM.”**, and the callback is prefilled for **Friday, 27 Nov at 4:00 PM**. The reason is “Follow up on motor renewal quote.” All fields remain editable.
4. Select **Save contact**. The new conversation and callback are stored; Today returns to Wednesday and lists **Call Friday** under Upcoming callbacks. Neither policy deadline is edited by contact logging.
5. On Today, select **Advance to Friday**. The callback moves into **Today’s calls** with its 4 PM time, client, linked policy, reason, and next action. Both December policy deadlines remain listed. Use **Back to Wednesday** or **Reset demo** to revisit the initial state.

## Action-to-data map

| Action | Fields read or written | Validation | Resulting state |
|---|---|---|---|
| Search clients | Search term against `Client.mobile`, `Client.nickname`, `Client.reference`, and name | Empty query shows all clients; partial/full mobile digits accepted, including `09…` normalization | Matching client cards show nickname/reference, masked number, policy count, and open detail; no match shows an explicit empty state |
| Add client | `name`, `nickname`, `reference`, `mobile`, `notes` | Name required for identification; mobile is the only required contact detail and must contain at least 10 digits. Nickname, reference, and notes are optional. On edit, leave mobile blank to retain the current value | Client saved to local state; reference generated when omitted; detail opens |
| Edit client | Same fields; mobile optional when unchanged | Same name and number checks as above | Updates client record without changing linked policies or conversations |
| Call / Text | Client identity and mobile number | None; the phone stays masked | Opens a clear “demo action / no message sent” dialog; no call or SMS is made |
| Add policy | `clientId`, `kind`, `insurer`, `product`, optional vehicle/policy number/filing reference, `coverageStart`, `coverageEnd`, `renewalDue` or `premiumDue`, `premiumAmount`, `documents[]` | Insurer/product and coverage dates required; end date cannot precede start; Motor requires renewal date; Life requires premium due date; premium must be > 0; blank document names are rejected | Policy detail is created and linked to its client; deadline becomes visible on Today |
| Edit policy | Same policy fields and `documents[]` | Same date, required-field, amount, and document checks | Updates only that policy record; other policies for the client remain unchanged |
| View policy | Policy dates, premium, `documents[]`, `filingReference` | N/A | Shows coverage start/end, correct motor-renewal or life-premium deadline, document status and a private insurer-filing reference with reveal/hide control |
| Mark document received | `Policy.documents[].status` | Only offered while status is `Missing` | Changes that document to `Received`; does not update coverage, renewal, premium, client, or callback fields |
| Log contact | `clientId`, optional `policyId`, `outcome`, `note`, optional callback fields | Outcome required; note ≤ 500 characters. If callback is enabled, date/time required; date must be on/after the current demo day; reason ≤ 120 characters | Adds a contact record and, when selected, a scheduled callback. The Today list and Client detail update; policy deadlines do not |
| Choose **No answer** | `outcome = "No answer"`, optional note/callback | Same contact rules; callback can remain on or be turned off | Saved outcome appears in recent conversations; a selected callback remains visible on Today |
| Edit conversation | `outcome`, `note`, `policyId`, callback fields | Same log-contact validation | Updates the existing contact and replaces/removes its linked scheduled callback based on the form selection |
| Simulate save error / Retry save | Current in-form draft, especially `note` and callback fields | First save is intentionally rejected once; no draft fields are cleared. Retry goes through the normal outcome/date checks | Error banner quotes the retained note. **Retry save** then persists the same draft once the simulated failure is consumed |
| Advance demo date | `WorkspaceData.demoDate` | N/A; demo control switches Wednesday/Friday only | Friday callback becomes a call due today; December policy dates remain unchanged |
| Reminder settings | `enabled`, `callbackTime`, `policyDaysAhead`, `dailySummary` | Time required; lead time must be a whole number from 1–30 | Saves local agent reminder preferences; no customer or platform notification is sent |
| Reset demo | Entire local `WorkspaceData` | Confirm by button action | Re-seeds fictional Wednesday records and clears added contacts/callbacks/policy-document edits/settings |

## Seed records and policy integrity

Mila Santos (`CL-2048`) has two independent records: HarborSure Comprehensive Motor with renewal due **10 Dec 2026**, and HarborSure Life Family Protect 20 with premium due **2 Dec 2026**. Each policy has its own coverage dates and document checklist. Contact actions only touch conversation/callback fields; document updates only touch a document status; due dates change only through the explicit policy edit flow.

The dashboard also seeds Benjie’s call today and Lorna’s overdue callback so urgency, routine work, and upcoming deadlines are visible immediately. All seeded names, policy records, insurer names, references, and mobile numbers are fictional.
