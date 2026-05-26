# AGENTS.md

Guidance for future Codex work on this repository.

## Project Context

This repository is for the CSE6364 Software Evolution and Maintenance assignment:

**Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform**

The project is an artwork sharing and task platform intended to run in a local XAMPP environment.

## Technology Stack

- PHP
- MySQL
- HTML
- CSS
- Bootstrap 5
- Vanilla JavaScript
- XAMPP local environment

## Approved Proposal Improvements

Only work on improvements that are part of the approved proposal:

1. Fix responsive layout issues for 1920x1080 resolution.
2. Improve low text contrast and accessibility.
3. Add Save Task feature.
4. Add task categorization and filtering.
5. Add artwork Like feature.
6. Add AJAX comment submission and near-real-time refresh.

Do not invent or implement features outside this list.

## Development Rules

- Do not rewrite the whole system.
- Make additive and maintainable changes.
- Preserve existing functionality and user flows.
- Keep changes scoped to the approved improvement being implemented.
- Keep the project suitable for XAMPP and local MySQL usage.
- Use prepared statements for all new SQL queries.
- Avoid large framework migrations or new build systems.
- Prefer Bootstrap 5 and existing CSS conventions before adding new styling patterns.
- Use vanilla JavaScript for new client-side behavior unless the project already provides a suitable helper.
- Update documentation when features are added.
- Add testing notes for every feature.

## SQL and Data Handling

- Use prepared statements for all new database reads and writes.
- Validate and sanitize user input before using it.
- Keep database changes minimal and clearly documented.
- If a schema change is required, document the SQL needed to reproduce it in XAMPP/phpMyAdmin.
- Preserve existing table relationships and naming conventions where possible.

## UI and Accessibility

- Prioritize fixing layout issues without redesigning unrelated pages.
- Check affected pages at 1920x1080 resolution.
- Keep Bootstrap 5 responsiveness in mind for smaller screens too.
- Improve text contrast where readability is poor.
- Preserve existing branding and page structure unless a targeted accessibility fix requires adjustment.
- Ensure interactive controls have clear labels, states, and keyboard-friendly behavior where practical.

## Feature Implementation Notes

When adding a feature, include concise notes covering:

- Files changed.
- Database or setup steps, if any.
- Manual testing steps.
- Any known limitations or follow-up work.

## Testing Expectations

For every feature or fix:

- Test the main happy path.
- Test at least one edge case, such as missing input, unauthenticated access, duplicate actions, or empty results.
- Test that existing related behavior still works.
- Include testing notes in the project documentation or a clearly relevant feature note.

## Boundaries

- Do not modify functional code unless the current task explicitly asks for implementation.
- Do not remove existing functionality to simplify a change.
- Do not introduce unrelated refactors.
- Do not add dependencies that make the project harder to run in XAMPP.
- Do not assume production deployment requirements beyond the assignment scope.
