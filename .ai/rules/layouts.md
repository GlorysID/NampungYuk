---
paths:
  - resources/views/layouts/app.blade.php
---

# Layouts

## Header search is contextual via $onCommunity / $onProfile
The header search bar (desktop #nav-search + mobile #nav-search-mobile) always GETs to projects.index unless on communities.*, where it targets communities.index. A third context, $onProfile (route profile.show), injects a hidden `u={username}` so results scope to that creator's projects in ProjectController@index. When adding new page contexts, update BOTH forms and keep the `u` param out of non-profile searches.
