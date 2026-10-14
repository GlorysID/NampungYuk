---
paths:
  - resources/views/components/project-actions.blade.php
---

# Components

## Admin can warn projects from the feed card, not just detail
Admin project moderation is available directly from the feed card via the project-actions overflow (⋯) menu ("Beri Peringatan (Admin)" / "Cabut Peringatan"), not just on the detail page. It opens the global modal component `resources/views/components/admin-warn-project-modal.blade.php`, callable from anywhere with `window.openWarnProjectModal({ id, title, action: 'set'|'remove', notes })`. The modal POSTs to route admin.projects.warn (warnProject). Warning state lives in projects.warning_notes + projects.is_flagged, and is surfaced publicly on the project detail page + as a "Peringatan Moderasi" block on the card.
