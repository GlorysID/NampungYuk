---
paths:
  - app/Models/Conversation.php
---

# Models

## Chat gate is Instagram-style and acceptance is monotonic
DM gate is Instagram-style and MONOTONIC: a message lands directly (status 'accepted') only when the RECIPIENT follows the SENDER; otherwise it becomes a pending 'message request'. `Conversation::forMessage()` never downgrades — once 'accepted' (via rule or explicit accept) a thread stays open, even if the follow direction reverses or messages start flowing the other way. Only new conversations get gateStatusFor(); existing pending ones may upgrade to accepted. Do NOT re-add "re-evaluate and demote to pending" logic — it broke replies when the conversation direction flipped.
