# ViaCart — Full Project (Source + PHP/SQLite Deployment)

This package contains **everything**: the real, editable React source code,
and the ready-to-upload PHP/SQLite build for shared hosting. Two folders:

```
via-cart-full/
├── frontend-source/     ← the actual React/TypeScript app you can edit
│   ├── src/               ← components, pages, types, styling
│   ├── package.json, vite.config.ts, tsconfig.json
│   └── legacy-nodejs-backend/   ← old Express+Firebase backend, unused, kept for reference
│
└── deploy/               ← THIS is what you upload to your shared host
    ├── index.html, assets/    ← production build of frontend-source (already built for you)
    ├── api/                    ← PHP + SQLite backend (plain PHP, no Composer/Node needed)
    ├── uploads/                ← where uploaded images land
    ├── .htaccess
    └── README.md               ← full deployment + troubleshooting instructions
```

## Which folder do I use for what?

- **Deploying to your hosting right now?** Upload the *contents* of
  `deploy/` to your web root. That's the only folder your host needs —
  full instructions are in `deploy/README.md`.
- **Want to change the design, add a feature, or edit any page/component?**
  Edit the code in `frontend-source/src/`. It's the real, unminified
  TypeScript/React source — same code structure as the original project,
  including the recent changes (menu renamed to "Shop", new "Checkout"
  menu link, Navbar.tsx, etc).

## Editing the frontend and re-deploying

The frontend is a normal Vite + React project. After making changes:

```bash
cd frontend-source
npm install
npx vite build
```

This produces `frontend-source/dist/index.html` and
`frontend-source/dist/assets/`. Copy those two over `deploy/index.html` and
`deploy/assets/` (replacing them), then re-upload `deploy/` to your host.
The `deploy/api/` PHP backend never needs to be rebuilt — it's already
plain PHP.

## Editing the backend

The backend is plain PHP in `deploy/api/` — edit any `.php` file directly,
no build step, no dependencies to install. Re-upload the changed file(s) to
your host.

## The database

`deploy/api/data/database.sqlite` is the single file holding every product,
category, order, and setting. It's created automatically the first time the
API is hit, and nothing in the app ever deletes or resets it — back it up
periodically the same way you'd back up any other file on your hosting
account.
