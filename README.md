# Nesypo-Azure — Déploiement Cloud Azure

Projet d'évaluation pratique Azure — Conteneurisation, déploiement PaaS, sécurité et exploitation.

Ce dépôt est une adaptation du projet Nesypo, une application fullstack de gestion de TodoLists, portée sur Microsoft Azure dans un cadre proche d'un environnement professionnel.

---

## Choix technologiques

### Application
- **Backend** : Symfony 7.4 (PHP) — API REST avec authentification JWT
- **Frontend** : Next.js 16 / React 19 — interface utilisateur avec App Router
- **Base de données** : Azure Cosmos DB for PostgreSQL (compatible avec Doctrine ORM sans réécriture)
- **Conteneurisation** : Docker avec images de production multi-stage

### Pourquoi ce projet existant ?
Plutôt que de développer une TodoList from scratch, j'ai adapté un projet fullstack déjà abouti afin de me concentrer entièrement sur la maîtrise d'Azure : conteneurisation de production, déploiement PaaS, gestion des secrets, stockage objet et exploitation cloud.

---

## Architecture Azure

```
                        ┌──────────────────────────────────────────┐
                        │           Resource Group: rg-nesypo      │
                        │                (Poland Central)          │
   Utilisateur          │   ┌──────────────┐   ┌────────────────┐  │
      │                 │   │  App Service │   │  App Service   │  │
      │  HTTPS          │   │  (Frontend)  │   │  (Backend)     │  │
      └────────────────►│   │  Next.js 16  │   │  Symfony 7.4   │  │
                        │   └──────┬───────┘   └───────┬────────┘  │
                        │          │   API calls       │           │
                        │          └──────────────────►│           │
                        │                              │           │
                        │   ┌──────────────┐           │           │
                        │   │  Azure       │◄──────────┘           │
                        │   │  Cosmos DB   │  Doctrine ORM         │
                        │   │  PostgreSQL  │                       │
                        │   └──────────────┘                       │
                        │                                          │
                        │   ┌──────────────┐  ┌────────────────┐   │
                        │   │  Azure       │  │  Azure         │   │
                        │   │  Key Vault   │  │  Blob Storage  │   │
                        │   │  (secrets)   │  │  (exports JSON)│   │
                        │   └──────────────┘  └────────────────┘   │
                        │                                          │
                        │   ┌──────────────────────────────────┐   │
                        │   │  Azure Container Registry (ACR)  │   │
                        │   │  nesypoacr.azurecr.io            │   │
                        │   │  ├── nesypo-backend:latest       │   │
                        │   │  └── nesypo-frontend:latest      │   │
                        │   └──────────────────────────────────┘   │
                        └──────────────────────────────────────────┘
```

---

## Ressources Azure utilisées

| Ressource | Nom | Région | Rôle |
|---|---|---|---|
| Resource Group | rg-nesypo | Poland Central | Conteneur de toutes les ressources |
| Azure Container Registry | nesypoacr | Spain Central | Stockage des images Docker |
| App Service Plan | nesypo-plan | Poland Central | Plan d'hébergement (SKU S1 Linux) |
| App Service (Backend) | nesypo-backend | Poland Central | Hébergement API Symfony |
| App Service (Frontend) | nesypo-frontend | Poland Central | Hébergement Next.js |
| Azure Cosmos DB | nesypo-db | Poland Central | Base de données PostgreSQL managée |
| Azure Key Vault | nesypo-kv | Poland Central | Stockage sécurisé des secrets |
| Azure Storage Account | nesypostorage | Poland Central | Stockage Blob (exports JSON) |
| Managed Identity | nesypo-backend (system) | — | Accès sécurisé au Key Vault sans credentials |

---

## URLs de l'application

- **Frontend Azure** : https://nesypo-frontend.azurewebsites.net
- **Backend Azure** : https://nesypo-backend.azurewebsites.net
- **Staging Backend** : https://nesypo-backend-staging.azurewebsites.net
- **ACR** : nesypoacr.azurecr.io
- **Frontend (local)** : http://localhost:3000
- **Backend (local)** : http://localhost:8000
- **Documentation API (local)** : http://localhost:8000/api/doc

---

## Commandes Azure CLI utilisées

### Connexion et configuration
```bash
# Connexion au compte Azure
az login

# Vérification de l'abonnement actif
az account show
```

### Resource Group
```bash
# Création du resource group
az group create \
  --name rg-nesypo \
  --location polandcentral
```

### Azure Container Registry (ACR)
```bash
# Enregistrement du provider
az provider register --namespace Microsoft.ContainerRegistry
az provider show --namespace Microsoft.ContainerRegistry --query "registrationState"

# Création de l'ACR (Spain Central — seule région disponible pour ce service)
az acr create \
  --resource-group rg-nesypo \
  --name nesypoacr \
  --sku Basic \
  --admin-enabled true \
  --location spaincentral

# Authentification Docker vers l'ACR
az acr login --name nesypoacr

# Build des images Docker de production (en local, ACR Tasks non disponible sur abonnement étudiant)
docker build \
  -f docker/backend/Dockerfile.prod \
  -t nesypoacr.azurecr.io/nesypo-backend:latest \
  .

docker build \
  -f docker/frontend/Dockerfile.prod \
  -t nesypoacr.azurecr.io/nesypo-frontend:latest \
  .

# Push des images vers l'ACR
docker push nesypoacr.azurecr.io/nesypo-backend:latest
docker push nesypoacr.azurecr.io/nesypo-frontend:latest

# Vérification des images présentes dans l'ACR
az acr repository list --name nesypoacr --output table
```

### App Service Plan et Web Apps
```bash
# Création du plan App Service (Linux, S1 — B1 insuffisant pour les slots)
az appservice plan create \
  --name nesypo-plan \
  --resource-group rg-nesypo \
  --is-linux \
  --sku S1 \
  --location polandcentral

# Création de la Web App backend
az webapp create \
  --resource-group rg-nesypo \
  --plan nesypo-plan \
  --name nesypo-backend \
  --deployment-container-image-name nesypoacr.azurecr.io/nesypo-backend:latest

# Création de la Web App frontend
az webapp create \
  --resource-group rg-nesypo \
  --plan nesypo-plan \
  --name nesypo-frontend \
  --deployment-container-image-name nesypoacr.azurecr.io/nesypo-frontend:latest

# Configuration de l'accès à l'ACR pour les deux Web Apps
az webapp config container set \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --container-image-name nesypoacr.azurecr.io/nesypo-backend:latest \
  --container-registry-url https://nesypoacr.azurecr.io \
  --container-registry-user nesypoacr \
  --container-registry-password <ACR_PASSWORD>

az webapp config container set \
  --resource-group rg-nesypo \
  --name nesypo-frontend \
  --container-image-name nesypoacr.azurecr.io/nesypo-frontend:latest \
  --container-registry-url https://nesypoacr.azurecr.io \
  --container-registry-user nesypoacr \
  --container-registry-password <ACR_PASSWORD>

# Configuration des variables d'environnement du backend via Key Vault References
az webapp config appsettings set \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --settings \
    APP_ENV=prod \
    DATABASE_URL='@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/database-url/)' \
    APP_SECRET='@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/app-secret/)' \
    JWT_PASSPHRASE='@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/jwt-passphrase/)'
```

### Azure Cosmos DB for PostgreSQL
```bash
# Enregistrement du provider
az provider register --namespace Microsoft.DocumentDB

# Création du cluster Cosmos DB for PostgreSQL
az cosmosdb postgres cluster create \
  --resource-group rg-nesypo \
  --cluster-name nesypo-db \
  --location polandcentral \
  --coordinator-v-cores 1 \
  --coordinator-server-edition BurstableMemoryOptimized \
  --coordinator-storage-quota-in-mb 32768 \
  --node-count 0 \
  --administrator-login-password <MOT_DE_PASSE>
```

Le serveur est accessible à l'adresse :
```
c-nesypo-db.hksavotyka7vxb.postgres.cosmos.azure.com
```

La `DATABASE_URL` Symfony prend la forme :
```
postgresql://citus:<PASSWORD>@c-nesypo-db.hksavotyka7vxb.postgres.cosmos.azure.com:5432/citus?sslmode=require
```

Doctrine ORM ne nécessite aucune modification car Cosmos DB for PostgreSQL est entièrement compatible avec le protocole PostgreSQL standard.

### Azure Key Vault et Managed Identity
```bash
# Enregistrement du provider
az provider register --namespace Microsoft.KeyVault

# Création du Key Vault
az keyvault create \
  --name nesypo-kv \
  --resource-group rg-nesypo \
  --location polandcentral

# Attribution du rôle pour pouvoir écrire des secrets (RBAC)
USER_ID=$(az ad signed-in-user show --query id --output tsv)
az role assignment create \
  --role "Key Vault Secrets Officer" \
  --assignee $USER_ID \
  --scope /subscriptions/<SUBSCRIPTION_ID>/resourceGroups/rg-nesypo/providers/Microsoft.KeyVault/vaults/nesypo-kv

# Stockage des secrets dans le Key Vault
az keyvault secret set \
  --vault-name nesypo-kv \
  --name database-url \
  --value 'postgresql://citus:<PASSWORD>@c-nesypo-db.hksavotyka7vxb.postgres.cosmos.azure.com:5432/citus?sslmode=require'

az keyvault secret set \
  --vault-name nesypo-kv \
  --name app-secret \
  --value '<SYMFONY_APP_SECRET>'

az keyvault secret set \
  --vault-name nesypo-kv \
  --name jwt-passphrase \
  --value '<JWT_PASSPHRASE>'

# Activation de la Managed Identity sur l'App Service backend
az webapp identity assign \
  --resource-group rg-nesypo \
  --name nesypo-backend

# Attribution du rôle "Key Vault Secrets User" à la Managed Identity
az role assignment create \
  --role "Key Vault Secrets User" \
  --assignee <MANAGED_IDENTITY_PRINCIPAL_ID> \
  --scope /subscriptions/<SUBSCRIPTION_ID>/resourceGroups/rg-nesypo/providers/Microsoft.KeyVault/vaults/nesypo-kv
```

L'App Service référence les secrets via la syntaxe Key Vault Reference :
```
@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/database-url/)
```

### Azure Blob Storage
```bash
# Enregistrement du provider
az provider register --namespace Microsoft.Storage

# Création du Storage Account
az storage account create \
  --name nesypostorage \
  --resource-group rg-nesypo \
  --location polandcentral \
  --sku Standard_LRS

# Attribution du rôle pour accéder aux blobs
az role assignment create \
  --role "Storage Blob Data Contributor" \
  --assignee <USER_PRINCIPAL_ID> \
  --scope /subscriptions/<SUBSCRIPTION_ID>/resourceGroups/rg-nesypo/providers/Microsoft.Storage/storageAccounts/nesypostorage

# Création du Blob container
az storage container create \
  --name exports \
  --account-name nesypostorage \
  --auth-mode login

# Upload d'un fichier JSON de test
az storage blob upload \
  --account-name nesypostorage \
  --container-name exports \
  --name tasks-export.json \
  --file tasks-export.json \
  --auth-mode login

# Liste des blobs dans le container
az storage blob list \
  --account-name nesypostorage \
  --container-name exports \
  --auth-mode login \
  --output table

# Configuration d'une règle de cycle de vie (expiration après 30 jours)
az storage account management-policy create \
  --account-name nesypostorage \
  --resource-group rg-nesypo \
  --policy '{
    "rules": [{
      "name": "expire-exports",
      "enabled": true,
      "type": "Lifecycle",
      "definition": {
        "filters": {"blobTypes": ["blockBlob"], "prefixMatch": ["exports/"]},
        "actions": {"baseBlob": {"delete": {"daysAfterModificationGreaterThan": 30}}}
      }
    }]
  }'
```

### Deployment Slot (Staging)
```bash
# Upgrade du plan vers S1 pour supporter les slots
az appservice plan update \
  --name nesypo-plan \
  --resource-group rg-nesypo \
  --sku S1

# Création du slot staging
az webapp deployment slot create \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --slot staging

# Swap staging → production (sans downtime)
az webapp deployment slot swap \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --slot staging \
  --target-slot production
```

### Scaling manuel
```bash
# Passage à 2 instances (scale out)
az appservice plan update \
  --name nesypo-plan \
  --resource-group rg-nesypo \
  --number-of-workers 2

# Retour à 1 instance
az appservice plan update \
  --name nesypo-plan \
  --resource-group rg-nesypo \
  --number-of-workers 1
```

---

## Explication des services Azure utilisés

### Azure Cosmos DB for PostgreSQL
Cosmos DB for PostgreSQL (anciennement Citus) est un service de base de données managé entièrement compatible avec le protocole PostgreSQL. Je l'ai choisi car mon backend Symfony utilise Doctrine ORM avec PostgreSQL : aucune migration de code n'est nécessaire, seule la chaîne de connexion `DATABASE_URL` change. Azure gère les sauvegardes, la haute disponibilité et les mises à jour automatiquement.

### Azure Key Vault
Key Vault est un coffre-fort cloud pour stocker les secrets de l'application (chaîne de connexion à la base de données, clé secrète Symfony, passphrase JWT) de manière sécurisée et auditée. Plutôt que de stocker ces informations dans des variables d'environnement en clair ou dans le code source, Key Vault les centralise et n'en autorise l'accès qu'aux identités autorisées via RBAC.

### Managed Identity
La Managed Identity est une identité Azure attribuée automatiquement à l'App Service backend. Elle permet à l'application de s'authentifier auprès du Key Vault sans aucun mot de passe ni clé d'API dans le code — Azure gère le cycle de vie des credentials de façon transparente. C'est le mécanisme recommandé pour sécuriser les accès entre services Azure.

### Azure Blob Storage
Blob Storage est un service de stockage objet pour fichiers non structurés. Dans ce projet, il stocke des exports JSON des tâches. Un container `exports` est créé avec une règle de cycle de vie qui supprime automatiquement les fichiers après 30 jours. Les blobs sont accessibles via URL ou URL signée temporaire (SAS).

### Deployment Slots
Les slots de déploiement permettent d'avoir plusieurs environnements (production et staging) sur le même App Service. On déploie d'abord une nouvelle version sur le slot `staging`, on la teste, puis on effectue un **swap** instantané qui bascule le trafic sans downtime. En cas de problème, le swap peut être annulé immédiatement.

### Scaling manuel
Le scaling manuel permet d'augmenter le nombre d'instances (workers) de l'App Service Plan pour absorber une charge plus importante. On peut passer de 1 à plusieurs instances en une seule commande CLI. En production, on utiliserait l'autoscaling basé sur des métriques (CPU, mémoire, requêtes).

---

## Ce qui a été réalisé

| Étape | Statut | Détail |
|---|---|---|
| Application TodoList fonctionnelle | ✅ | Symfony + Next.js, fonctionnel en local |
| Dockerfiles de production | ✅ | Backend (PHP-FPM + Nginx) et Frontend (Next.js standalone) |
| Azure Container Registry | ✅ | nesypoacr.azurecr.io créé en Spain Central |
| Push des images Docker | ✅ | nesypo-backend:latest et nesypo-frontend:latest |
| App Service Plan | ✅ | nesypo-plan créé en Poland Central (SKU S1) |
| App Service Backend | ✅ | nesypo-backend.azurewebsites.net |
| App Service Frontend | ✅ | nesypo-frontend.azurewebsites.net |
| Cosmos DB for PostgreSQL | ✅ | nesypo-db créé et prêt en Poland Central |
| Key Vault | ✅ | 3 secrets stockés (database-url, app-secret, jwt-passphrase) |
| Managed Identity | ✅ | Activée sur le backend, rôle Key Vault Secrets User assigné |
| Blob Storage | ✅ | Container exports + fichier uploadé + règle de cycle de vie 30j |
| Deployment Slot staging | ✅ | nesypo-backend-staging.azurewebsites.net |
| Scaling manuel | ✅ | Démontré 1→2→1 instances |

---

## Limites rencontrées

- **Throttling App Service Plan** : L'abonnement Azure for Students a retourné une erreur 429 persistante lors des premières tentatives en Spain Central. Le déploiement a finalement réussi en Poland Central après plusieurs essais.

- **ACR Tasks non disponibles** : La commande `az acr build` (build dans le cloud) n'est pas disponible sur l'abonnement étudiant. Le build a été effectué localement avec Docker puis poussé vers l'ACR.

- **Régions restreintes** : L'abonnement étudiant ne donne accès qu'à un sous-ensemble de régions Azure. France Central est bloquée. L'ACR a été créé en Spain Central et les autres ressources en Poland Central.

- **RBAC obligatoire** : Le Key Vault et le Storage Account utilisent RBAC par défaut, ce qui nécessite d'assigner explicitement les rôles (`Key Vault Secrets Officer`, `Storage Blob Data Contributor`) avant de pouvoir interagir avec ces ressources.

---

## Installation locale

### Prérequis
- Docker et Docker Compose

### Démarrage

```bash
git clone https://github.com/HugoZins/Nesypo-Azure.git
cd Nesypo-Azure

cp backend/.env.example backend/.env
echo "NEXT_PUBLIC_API_URL=http://localhost:8000" > frontend/.env.local

docker compose up -d

docker compose exec backend composer install
docker compose exec backend php bin/console lexik:jwt:generate-keypair
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
```

- **Frontend** → http://localhost:3000
- **API** → http://localhost:8000
- **Documentation API** → http://localhost:8000/api/doc

### Comptes de test

| Email | Mot de passe | Rôle |
|---|---|---|
| admin@todo.local | Admin123! | Administrateur |
| alice@todo.local | User123! | Utilisateur |
| bob@todo.local | User123! | Utilisateur |