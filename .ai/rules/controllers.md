---
paths:
  - app/Http/Controllers/ChatController.php
---

# Controllers

## Chat: soft-delete messages, hard-delete conversations, 4 broadcast events
Messages are soft-deleted (`Message` uses SoftDeletes); deleting a conversation hard-deletes all its messages (`forceDelete`). Realtime is split across three events: MessageSent (conversation channel .message.sent), MessageRead (read receipts, .message.read), UserTyping (ShouldBroadcastNow, .user.typing), plus InboxUpdated (user's App.Models.User.{id} channel, .inbox.updated) so the inbox updates live. Chat views seed initial data via window.__chatSeed / __chatHasMore and Alpine `chatRoom` merges server + live messages deduped by id. Conversation history loads latest 40 with "load older" via GET /obrolan/{user}/lama?before={id}.

## Chat is gated by mutual follow (message requests)
Messaging uses a follow-based gate: `Conversation::forMessage($from,$to)` sets status `accepted` when the two users mutually follow, otherwise `pending` (a "message request"). While pending, only the initiator's single opener is allowed; the recipient must POST `chat.accept` (or `chat.decline`) first. Request gating lives in ChatController@show (flags: $isPending, $canRespond, $canSend) and @send. Inbox splits into `$requests` (pending, recipient-only) and `$conversations`. Desktop UI is a 2-pane shell (list via resources/views/chat/_list.blade.php + conversation pane); mobile stays single-pane.
