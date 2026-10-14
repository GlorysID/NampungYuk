---
paths:
  - resources/views/chat/show.blade.php
---

# Chat

## Chat gate UI is reactive (no refresh to lock)
chat/show gate UI is REACTIVE, not server-rendered: the banner (x-show="isPending") and composer (x-show="canSend") are always in the DOM and toggle via Alpine state. chatRoom receives isPending/canSend/canRespond as seed props and ChatController@send returns a `conversation` block {id, status, can_send} that the JS applies after each send — so the first opener locks the thread immediately without a refresh. Do NOT gate the composer/banner with @if() server-side; the composer input is always present (hidden by Alpine).
