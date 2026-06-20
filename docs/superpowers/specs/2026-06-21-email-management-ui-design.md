# Email Management UI Design

Date: 2026-06-21

## Goal

Add a UI-only email management page for FAYLabs admin users. The page should be reachable from the sidebar, open at `/emails`, show incoming email examples, allow viewing email details, and provide a compose modal. No backend email fetching, database integration, or real email sending is included in this scope.

## Scope

### In scope

- Add protected admin page `/emails`.
- Add `Emails` navigation item to the sidebar.
- Show 5 realistic dummy inbound emails.
- Show a split inbox layout with list on the left and detail panel on the right.
- Add local UI actions for read/unread state.
- Add a compose modal with placeholder behavior.
- Support light/dark theme and responsive layout.
- Keep the page usable without JavaScript by showing the first email detail by default.

### Out of scope

- Database table for emails.
- API endpoints for email list/detail/send.
- SMTP, IMAP, webhook, or provider integration.
- Real delete, archive, send, reply, or persistence behavior.
- Separate create/compose route.

## Architecture

Create a new PHP page at `pages/emails.php`. It follows the existing dashboard page pattern used by `pages/index.php`:

- Define `ROOT_PATH`.
- Load `config/app.php`.
- Load auth and CSRF helpers.
- Call `csrfStart()` and `requireAdmin()`.
- Set `$pageTitle = 'Emails'` and `$activePage = 'emails'`.
- Include `partials/head.php`, `partials/sidebar.php`, and `partials/footer.php`.

Add a sidebar nav item in `partials/sidebar.php`:

- Label: `Emails`.
- Icon: Bootstrap Icons mail-style icon.
- URL: `<?= BASE_PATH ?>/emails`.
- Active state when `$activePage === 'emails'`.

Routing should match the existing clean route style. If the current Apache rewrite maps `/create` to `pages/create.php`, `/emails` should map to `pages/emails.php` using the same convention.

## UI Design

The page uses the existing dashboard layout and visual system:

- Fixed sidebar.
- Topbar with hamburger on mobile.
- `page-content` main area.
- Existing buttons, colors, borders, radius, and theme variables from `assets/css/admin.css`.

### Topbar

- Title: `Emails`.
- Primary action: `Compose`.
- Compose opens a modal, not a separate page.

### Main layout

Use a split layout:

1. Inbox list panel on the left.
2. Email detail panel on the right.

The inbox list contains 5 dummy emails. Each item shows:

- Sender name.
- Sender email.
- Subject.
- Short preview.
- Date/time.
- Unread indicator or badge.

The detail panel shows the selected email:

- Sender name and email.
- Recipient display, such as `to FAYLabs`.
- Subject.
- Date/time.
- Full message body.
- UI actions: Back on mobile, mark read/unread, reply placeholder.

No archive action is included.

### Compose modal

The compose modal contains:

- To field.
- Subject field.
- Message textarea.
- Cancel button.
- Send button with placeholder behavior.

Submitting the compose form must not send email. It should prevent default submission and show a local placeholder response, such as a toast or inline message.

## Data Flow

Email data is a local PHP array inside `pages/emails.php`. No DB query is used.

Default state:

- The first email is selected by default.
- The first email detail is rendered server-side so the page remains useful if JavaScript is unavailable.

Interactive state:

- JavaScript can store the dummy email payload in the page or use data attributes.
- Clicking an inbox item updates the detail panel without page reload.
- Mark read/unread updates only the DOM state.
- Compose submit is intercepted and handled locally.

No UI state needs persistence across reloads.

## Error, Empty, and Responsive States

### Errors

There is no backend error state because there is no backend integration. Any disabled or placeholder action should clearly communicate that email backend integration is not available yet.

### Empty state

If the dummy email array is empty, show an empty state using the existing dashboard empty-state style:

- Icon.
- Title: `No emails yet.`
- Description: `Incoming FAYLabs emails will appear here once email integration is connected.`
- Compose button remains available as UI-only placeholder.

### Responsive behavior

On desktop, use the split list/detail layout.

On mobile and narrow screens:

- Stack list above detail, or make the list full-width followed by the selected detail.
- Keep the sidebar hamburger behavior unchanged.
- Compose modal should fit small screens and remain scrollable if needed.

## Component Boundaries

This first version can remain in a single `pages/emails.php` file because it is UI-only and small. CSS should be added to `assets/css/admin.css` in a focused email section using existing variables.

If the email feature later gains backend integration, the design should split into:

- API endpoints under `api/emails/`.
- Shared email helpers or models.
- Separate JavaScript module if interaction grows beyond basic DOM updates.

Those future boundaries are not part of this implementation.

## Accessibility

- Sidebar link has visible active state.
- Compose modal uses `role="dialog"`, `aria-modal="true"`, and a labelled title.
- Buttons use accessible names.
- Inbox items are buttons or links with clear focus styles.
- Unread state is represented by text or badge, not color alone.

## Verification

Manual checks:

- `/emails` opens for authenticated admin users.
- Sidebar shows `Emails` and active state works.
- Compose modal opens and closes.
- Compose submit does not send email and shows placeholder feedback.
- Clicking each dummy email updates the detail panel.
- Mark read/unread changes local UI state.
- Page works in light and dark mode.
- Layout is usable on mobile width.

Technical checks:

- Run PHP syntax checks for new or edited PHP files.
- Run available lint/typecheck commands if the project provides them.
- Do not commit `.env` or `.superpowers/` artifacts.
