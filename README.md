# Nesypo-Azure — Déploiement Cloud Azure

Projet d'évaluation pratique Azure — Conteneurisation, déploiement PaaS, sécurité et exploitation.

Ce dépôt est une adaptation du projet [Nesypo](https://github.com/HugoZins/Nesypo), une application fullstack de gestion de TodoLists, portée sur Microsoft Azure dans un cadre proche d'un environnement professionnel.

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
                        │                                          │
   Utilisateur          │   ┌──────────────┐  ┌────────────────┐   │
      │                 │   │  App Service │  │  App Service   │   │
      │  HTTPS          │   │  (Frontend)  │  │  (Backend)     │   │
      └────────────────►│   │  Next.js 16  │  │  Symfony 7.4   │   │
                        │   └──────┬───────┘  └───────┬────────┘   │
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
                        │   │  (secrets)   │  │  (fichiers)    │   │
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

| Ressource | Nom | Rôle |
|---|---|---|
| Resource Group | rg-nesypo | Conteneur de toutes les ressources |
| Azure Container Registry | nesypoacr | Stockage des images Docker |
| App Service Plan | nesypo-plan | Plan d'hébergement (SKU B1 Linux) |
| App Service (Backend) | nesypo-backend | Hébergement API Symfony |
| App Service (Frontend) | nesypo-frontend | Hébergement Next.js |
| Azure Cosmos DB | nesypo-db | Base de données PostgreSQL managée |
| Azure Key Vault | nesypo-kv | Stockage sécurisé des secrets |
| Azure Storage Account | nesypostorage | Stockage Blob (exports, fichiers) |
| Managed Identity | nesypo-backend (system) | Accès sécurisé au Key Vault sans credentials |

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
  --location spaincentral
```

### Azure Container Registry (ACR)
```bash
# Enregistrement du provider (si nécessaire)
az provider register --namespace Microsoft.ContainerRegistry

# Création de l'ACR
az acr create \
  --resource-group rg-nesypo \
  --name nesypoacr \
  --sku Basic \
  --admin-enabled true \
  --location spaincentral

# Authentification Docker vers l'ACR
az acr login --name nesypoacr

# Build des images Docker de production
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
# Création du plan App Service (Linux, B1)
az appservice plan create \
  --name nesypo-plan \
  --resource-group rg-nesypo \
  --is-linux \
  --sku B1 \
  --location spaincentral

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

# Configuration des variables d'environnement du backend
az webapp config appsettings set \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --settings \
    APP_ENV=prod \
    DATABASE_URL="@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/database-url/)"

# Autoriser l'App Service à puller depuis l'ACR
az webapp config container set \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --docker-registry-server-url https://nesypoacr.azurecr.io \
  --docker-registry-server-user nesypoacr \
  --docker-registry-server-password <ACR_PASSWORD>
```

### Azure Cosmos DB for PostgreSQL
```bash
# Création du cluster Cosmos DB for PostgreSQL
az cosmosdb postgres cluster create \
  --resource-group rg-nesypo \
  --cluster-name nesypo-db \
  --location spaincentral \
  --coordinator-v-cores 1 \
  --coordinator-server-edition BurstableMemoryOptimized \
  --node-count 0 \
  --administrator-login-password <MOT_DE_PASSE>

# Récupération de la chaîne de connexion
az cosmosdb postgres cluster show \
  --resource-group rg-nesypo \
  --cluster-name nesypo-db \
  --query "serverNames"
```

La `DATABASE_URL` Symfony prend alors la forme :
```
postgresql://citus:<PASSWORD>@nesypo-db.postgres.cosmos.azure.com:5432/citus?sslmode=require
```

Doctrine ORM ne nécessite aucune modification car Cosmos DB for PostgreSQL est entièrement compatible avec le protocole PostgreSQL standard.

### Azure Key Vault et Managed Identity
```bash
# Création du Key Vault
az keyvault create \
  --name nesypo-kv \
  --resource-group rg-nesypo \
  --location spaincentral

# Stockage des secrets dans le Key Vault
az keyvault secret set \
  --vault-name nesypo-kv \
  --name database-url \
  --value "postgresql://citus:<PASSWORD>@nesypo-db.postgres.cosmos.azure.com:5432/citus?sslmode=require"

az keyvault secret set \
  --vault-name nesypo-kv \
  --name app-secret \
  --value "<SYMFONY_APP_SECRET>"

az keyvault secret set \
  --vault-name nesypo-kv \
  --name jwt-passphrase \
  --value "<JWT_PASSPHRASE>"

# Activation de la Managed Identity sur l'App Service backend
az webapp identity assign \
  --resource-group rg-nesypo \
  --name nesypo-backend

# Récupération du principal ID de la Managed Identity
PRINCIPAL_ID=$(az webapp identity show \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --query principalId \
  --output tsv)

# Attribution du rôle "Key Vault Secrets User" à la Managed Identity
az keyvault set-policy \
  --name nesypo-kv \
  --object-id $PRINCIPAL_ID \
  --secret-permissions get list
```

L'App Service peut alors référencer les secrets directement dans ses variables d'environnement avec la syntaxe Key Vault Reference :
```
@Microsoft.KeyVault(SecretUri=https://nesypo-kv.vault.azure.net/secrets/database-url/)
```

### Azure Blob Storage
```bash
# Création du Storage Account
az storage account create \
  --name nesypostorage \
  --resource-group rg-nesypo \
  --location spaincentral \
  --sku Standard_LRS

# Création du Blob container
az storage container create \
  --name exports \
  --account-name nesypostorage \
  --public-access off

# Exemple : upload d'un fichier vers le Blob container
az storage blob upload \
  --account-name nesypostorage \
  --container-name exports \
  --name tasks-export.json \
  --file tasks-export.json

# Liste des blobs dans le container
az storage blob list \
  --account-name nesypostorage \
  --container-name exports \
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
# Création du slot staging pour le backend
az webapp deployment slot create \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --slot staging

# Déploiement d'une image sur le slot staging
az webapp config container set \
  --resource-group rg-nesypo \
  --name nesypo-backend \
  --slot staging \
  --docker-custom-image-name nesypoacr.azurecr.io/nesypo-backend:staging

# Swap staging → production
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

## URL de l'application

> ⚠️ Le déploiement sur App Service n'a pas pu être finalisé en raison d'un throttling de l'abonnement Azure for Students lors de la création de l'App Service Plan (erreur 429 persistante malgré plusieurs tentatives via CLI et portail, sur toutes les régions disponibles). Les images Docker sont bien présentes dans l'ACR et l'application fonctionne localement.

- **Frontend (local)** : http://localhost:3000
- **Backend (local)** : http://localhost:8000
- **ACR** : nesypoacr.azurecr.io
- **Frontend Azure (prévu)** : https://nesypo-frontend.azurewebsites.net
- **Backend Azure (prévu)** : https://nesypo-backend.azurewebsites.net

---

## Explication des services Azure utilisés

### Azure Cosmos DB for PostgreSQL
Cosmos DB for PostgreSQL (anciennement Citus) est un service de base de données managé entièrement compatible avec le protocole PostgreSQL. Je l'ai choisi car mon backend Symfony utilise Doctrine ORM avec PostgreSQL : aucune migration de code n'est nécessaire, seule la chaîne de connexion `DATABASE_URL` change. Azure gère les sauvegardes, la haute disponibilité et les mises à jour automatiquement.

### Azure Key Vault
Key Vault est un coffre-fort cloud pour stocker les secrets de l'application (chaîne de connexion à la base de données, clé secrète Symfony, passphrase JWT) de manière sécurisée et auditée. Plutôt que de stocker ces informations dans des variables d'environnement en clair ou dans le code source, Key Vault les centralise et n'en autorise l'accès qu'aux identités autorisées.

### Managed Identity
La Managed Identity est une identité Azure attribuée automatiquement à l'App Service backend. Elle permet à l'application de s'authentifier auprès du Key Vault sans aucun mot de passe ni clé d'API dans le code — Azure gère le cycle de vie des credentials de façon transparente. C'est le mécanisme recommandé pour sécuriser les accès entre services Azure.

### Azure Blob Storage
Blob Storage est un service de stockage objet pour fichiers non structurés. Dans ce projet, il est utilisé pour stocker des exports JSON des tâches générés par l'API Symfony. Un container `exports` est créé avec une règle de cycle de vie qui supprime automatiquement les fichiers après 30 jours. Les blobs peuvent être rendus accessibles via URL publique ou URL signée temporaire (SAS).

### Deployment Slots
Les slots de déploiement permettent d'avoir plusieurs environnements (production et staging) sur le même App Service. On déploie d'abord une nouvelle version sur le slot `staging`, on la teste, puis on effectue un **swap** instantané qui bascule le trafic vers la nouvelle version sans downtime. En cas de problème, le swap peut être annulé immédiatement.

### Scaling manuel
Le scaling manuel permet d'augmenter le nombre d'instances (workers) de l'App Service Plan pour absorber une charge plus importante. Avec le SKU B1, on peut passer de 1 à plusieurs instances en une seule commande. En production, on utiliserait plutôt l'autoscaling basé sur des métriques (CPU, mémoire, requêtes), mais le scaling manuel permet de comprendre le principe et de démontrer l'élasticité du cloud.

---

## Ce qui a été réalisé

| Étape | Statut | Détail |
|---|---|---|
| Application TodoList fonctionnelle | ✅ | Symfony + Next.js, fonctionnel en local |
| Dockerfiles de production | ✅ | Backend (PHP-FPM + Nginx) et Frontend (Next.js standalone) |
| Azure Container Registry | ✅ | nesypoacr.azurecr.io créé en Spain Central |
| Push des images Docker | ✅ | nesypo-backend:latest et nesypo-frontend:latest |
| App Service Plan | ❌ | Bloqué par throttling 429 de l'abonnement étudiant |
| App Service (Backend + Frontend) | ❌ | Dépend du plan |
| Cosmos DB | ⏳ | Prévu, commandes documentées |
| Key Vault | ⏳ | Prévu, commandes documentées |
| Managed Identity | ⏳ | Prévu, commandes documentées |
| Blob Storage | ⏳ | Prévu, commandes documentées |
| Deployment Slot | ⏳ | Prévu, commandes documentées |
| Scaling manuel | ⏳ | Prévu, commandes documentées |

---

## Limites rencontrées

- **Throttling App Service Plan** : L'abonnement Azure for Students (Emineo Education) a retourné une erreur 429 persistante lors de toutes les tentatives de création d'App Service Plan, aussi bien via la CLI qu'via le portail Azure, sur toutes les régions disponibles (Spain Central, West Europe, North Europe, Sweden Central, Switzerland North) et tous les SKUs testés (F1, B1, S1). La création de l'ACR en Spain Central a fonctionné normalement sur le même abonnement. Un signalement a été effectué auprès du formateur.

- **ACR Tasks non disponibles** : La commande `az acr build` (build dans le cloud) n'est pas disponible sur l'abonnement étudiant. Le build a donc été effectué localement avec Docker puis poussé vers l'ACR.

- **Régions restreintes** : L'abonnement étudiant ne donne accès qu'à un sous-ensemble de régions Azure. France Central notamment est bloquée, Spain Central a été retenu comme région de travail.

---

## Installation locale

### Prérequis
- Docker et Docker Compose

### Démarrage

```bash
git clone https://github.com/HugoZins/Nesypo-Azure.git
cd Nesypo-Azure

cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env.local

docker compose up -d

docker compose exec backend composer install
docker compose exec backend symfony console lexik:jwt:generate-keypair
docker compose exec backend symfony console doctrine:migrations:migrate --no-interaction
docker compose exec backend symfony console doctrine:fixtures:load --no-interaction
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