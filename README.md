# Nesypo — Gestionnaire de TodoLists

![Backend CI](https://github.com/HugoZins/Nesypo/actions/workflows/backend.yml/badge.svg)
![Frontend CI](https://github.com/HugoZins/Nesypo/actions/workflows/frontend.yml/badge.svg)

Application fullstack de gestion de listes de tâches multi-utilisateurs, développée  
dans un objectif d'apprentissage des outils et pratiques utilisés en entreprise.

---

## Stack technique

### Backend
- **Symfony 7.4** — framework PHP
- **Doctrine ORM** — mapping objet-relationnel et migrations
- **PostgreSQL 15** — base de données
- **LexikJWTAuthenticationBundle** — authentification par JWT en cookie HTTP-only
- **gesdinet/jwt-refresh-token-bundle** — refresh token automatique
- **NelmioApiDocBundle** — documentation OpenAPI/Swagger externalisée
- **NelmioCorsBundle** — gestion des headers CORS
- **PHPUnit 12** — tests unitaires et fonctionnels (80 tests)
- **DAMA DoctrineTestBundle** — isolation des tests via transactions
- **FakerPHP** — génération de fixtures réalistes

### Frontend
- **Next.js 16 / React 19** — framework frontend avec App Router
- **TanStack Query v5** — gestion du cache et des requêtes API
- **TanStack Table** — tableau avec pagination côté serveur
- **shadcn/ui + Tailwind CSS** — composants UI et styles
- **ky** — client HTTP avec intercepteurs (retry sur 401)
- **Zod + react-hook-form** — validation de formulaires
- **Zustand** — état global d'authentification (utilisateur connecté, rôles, déconnexion)
- **Vitest + React Testing Library + MSW** — tests composants et hooks (25 tests)

### Infrastructure
- **Docker Compose** — orchestration des services
- **PostgreSQL 15** — base de données partagée entre dev et test

---

## Architecture
```
Nesypo/  
├── backend/                  # API Symfony
│   ├── src/
│   │   ├── Controller/       # Endpoints REST
│   │   ├── Service/          # Logique métier
│   │   ├── Entity/           # Entités Doctrine
│   │   ├── DTO/              # Objets de transfert de données
│   │   ├── Repository/       # Requêtes Doctrine
│   │   ├── OpenApi/          # Documentation API externalisée
│   │   └── Enum/             # Énumérations (TaskPriority)
│   └── tests/
│       ├── Unit/             # Tests unitaires (services)
│       └── Functional/       # Tests fonctionnels (controllers HTTP)
└── frontend/                 # App Next.js
    └── src/
        ├── app/              # Pages et layouts (App Router)
        ├── components/       # Composants React réutilisables
        ├── hooks/            # Hooks TanStack Query par domaine
        ├── lib/              # Clients API, validation, utilitaires
        ├── stores/           # État global Zustand
        ├── types/            # Types TypeScript
        └── tests/            # Tests Vitest + RTL + MSW
```

---

## Fonctionnalités

- Inscription et connexion avec JWT en cookie HTTP-only
- Refresh token automatique transparent (7 jours)
- Tableau de bord avec liste paginée des todolists
- Création, modification et suppression de todolists et tâches
- Progression visuelle par barre de couleur (rouge → vert)
- Rôles utilisateur (ROLE_USER) et administrateur (ROLE_ADMIN)
- Documentation API interactive via Swagger UI (`/api/doc`)

---

## Installation

### Prérequis
- Docker et Docker Compose

### Démarrage

```bash
# Cloner le projet
git clone https://github.com/HugoZins/Nesypo.git
cd Nesypo

# Copier les fichiers d'environnement en premier
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env.local

# Démarrer les services
docker compose up -d

# Backend — installation et configuration
docker compose exec backend composer install
docker compose exec backend symfony console lexik:jwt:generate-keypair
docker compose exec backend symfony console doctrine:migrations:migrate --no-interaction
docker compose exec backend symfony console doctrine:fixtures:load --no-interaction

# Copier les fichiers d'environnement
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env.local
```

L'application est accessible sur :
- **Frontend** → http://localhost:3000
- **API** → http://localhost:8000
- **Documentation API** → http://localhost:8000/api/doc

### Comptes de test

| Email            | Mot de passe | Rôle           |
|------------------|--------------|----------------|
| admin@todo.local | Admin123!    | Administrateur |
| alice@todo.local | User123!     | Utilisateur    |
| bob@todo.local   | User123!     | Utilisateur    |

---

## Tests

```bash
# Backend — tous les tests
docker compose exec backend php bin/phpunit --testdox

# Backend — suite spécifique
docker compose exec backend php bin/phpunit tests/Unit --testdox
docker compose exec backend php bin/phpunit tests/Functional --testdox

# Frontend — tous les tests
docker compose exec frontend npx vitest run --reporter=verbose

# Frontend — fichier spécifique
docker compose exec frontend npx vitest run src/tests/components/LoginForm.test.tsx
```

---

## Concepts abordés

### Backend
| Concept              | Outil                             | Implémentation                                        |
|----------------------|-----------------------------------|-------------------------------------------------------|
| Architecture MVC     | Symfony                           | Controllers → Services → Repositories                 |
| ORM et migrations    | Doctrine                          | Entités, repositories, migrations versionnées         |
| Authentification JWT | LexikJWT                          | Cookie HTTP-only, expiration 1h                       |
| Refresh token        | gesdinet/jwt-refresh-token-bundle | Cookie séparé, durée 7 jours                          |
| Validation           | Symfony Validator                 | DTOs annotés avec contraintes                         |
| Tests unitaires      | PHPUnit + stubs/mocks             | Isolation des services sans base de données           |
| Tests fonctionnels   | PHPUnit + WebTestCase             | Requêtes HTTP réelles sur base de test isolée         |
| Isolation tests BDD  | DAMA DoctrineTestBundle           | Rollback automatique entre chaque test                |
| Pagination serveur   | Doctrine QueryBuilder             | Query params page/limit + PaginatedResponse DTO       |
| Documentation API    | NelmioApiDocBundle                | Externalisée en classes dédiées par route             |
| Fixtures             | FakerPHP                          | Données réalistes et aléatoires à chaque rechargement |
| CORS                 | NelmioCorsBundle                  | Configuration par environnement                       |

### Frontend
| Concept                | Outil                          | Implémentation                                                                                                                    |
|------------------------|--------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|
| Framework UI           | Next.js 16 + App Router        | Pages, layouts, routing, metadata                                                                                                 |
| Styles                 | Tailwind CSS + shadcn/ui       | Composants accessibles et personnalisables                                                                                        |
| Requêtes API           | ky                             | Client HTTP avec retry automatique sur 401                                                                                        |
| Cache et état serveur  | TanStack Query v5              | Invalidation ciblée, pagination, placeholderData                                                                                  |
| Tableau paginé         | TanStack Table                 | Colonnes dynamiques selon le rôle utilisateur                                                                                     |
| Validation formulaires | Zod + react-hook-form          | Schémas typés, messages d'erreur localisés                                                                                        |
| État global            | Zustand                        | Stocke l'utilisateur connecté (id, email, rôles) après login — évite un appel `/api/me` à chaque composant qui vérifie les droits |
| Tests de composants    | Vitest + React Testing Library | Rendu DOM, interactions utilisateur simulées                                                                                      |
| Mock API en tests      | MSW                            | Interception des requêtes sans serveur réel                                                                                       |
