# TODO — Insurance Agent Workspace

## Today dashboard and Friday demo flow
- [x] Start the prototype on Today, populated with realistic fictional work for Wednesday, 25 November 2026 in Manila.
- [x] Today shows overdue callbacks, today’s calls, and upcoming policy deadlines. Each item shows the client, policy, reason, and next action.
- [x] Mila has a motor renewal due 10 December and a separate life premium due 2 December; logging or scheduling a callback must not alter either deadline.
- [x] Log Mila’s promise, “Ma’am, Friday na lang po, mga 4 PM.”, as a callback for Friday, 27 November 2026 at 4 PM.
- [x] After saving, show the updated Wednesday list; provide a demo control to advance the date to Friday and find the scheduled callback.
- [x] Show urgency with words as well as color, use explicit labels such as “Call Friday” and “Renewal due 10 Dec,” plain English, readable type, and large tap targets.
- [x] Design responsively for a 390 px phone and a 1440 px desktop.

## Client directory and detail
- [x] Clients can be searched by mobile number, nickname, or client reference.
- [x] The mobile number is the only required client contact detail; other contact fields remain optional.
- [x] Client detail shows multiple policies, the next promised callback, and recent conversations.
- [x] Keep Call, Text, and Log contact actions within thumb reach; calls and texts are simulated.
- [x] Include an empty search state and add/edit client flows with clear field validation.

## Policy detail and document tracking
- [x] Policy detail shows relevant dates and which documents are missing, received, or submitted.
- [x] Include a private filing reference to the insurer’s existing records.
- [x] Allow an agent to mark a missing document as received, updating its state without changing policy deadlines.
- [x] Include add/edit policy flows and clear validation.

## Contact logging, callbacks, reminders, and recovery
- [x] Log contact lets the agent record an outcome and schedule a callback quickly.
- [x] Include a “No answer” outcome flow.
- [x] Include add/edit flows for contact records and agent reminder settings.
- [x] Include empty, validation, and save-error states.
- [x] A failed save can be retried without losing the agent’s note.
- [x] Use fictional records, masked phone numbers, and simulated calls and texts.

## Handoff
- [x] Deliver a clickable prototype and editable screens or exportable source.
- [x] Include a short handoff mapping each action to its fields, validation, and resulting state.
- [x] Use local mock data and keep the Laravel backend out of scope until design review.
