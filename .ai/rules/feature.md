---
paths:
  - 'tests/Feature/**'
---

# Feature

## Never mutate the real DB from verification scripts
CRITICAL: never run ad-hoc verification scripts that call DB::table(...)->delete()/insert()/update() against the real database — always use feature tests with RefreshDatabase (php artisan test) or wrap in a transaction + rollback. Earlier ad-hoc scripts wiped production conversations/messages. Seeders (NampungYukSeeder, MassDummySeeder) do NOT recreate chat data.
