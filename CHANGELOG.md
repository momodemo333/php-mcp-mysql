# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Rien pour le moment

## [1.1.1] - 2025-09-01

### Fixed
- 🐛 **Bug critique DDL résolu** : Les permissions DDL fonctionnent maintenant correctement (Issue #3)
  - `ALLOW_DDL_OPERATIONS=true` autorise désormais réellement CREATE, ALTER, DROP
  - `ALLOW_ALL_OPERATIONS=true` prime correctement sur `BLOCK_DANGEROUS_KEYWORDS`
  - Variables de configuration manquantes ajoutées dans `MySqlServer.php`
  - Logique de validation corrigée dans `SecurityService.php`
- ✅ **Résolution définitive** : Fini les erreurs "Mot-clé non autorisé détecté: CREATE/ALTER/DROP"
- 🧪 **Tests complets ajoutés** : Suite de tests `DDLPermissionsTest.php` pour éviter les régressions
- 📊 **Capabilities mises à jour** : L'endpoint `/server/capabilities` affiche maintenant l'état DDL et ALL

### Technical Details
- **Root Cause 1** : Configuration incomplète - `ALLOW_DDL_OPERATIONS` et `ALLOW_ALL_OPERATIONS` non chargées
- **Root Cause 2** : Logique défaillante - `ALLOW_ALL_OPERATIONS` n'outrepassait pas `BLOCK_DANGEROUS_KEYWORDS`
- **Commits** : [5d81756](https://github.com/momodemo333/php-mcp-mysql/commit/5d81756)

### Migration automatique
Aucune action requise. Si vous aviez configuré `ALLOW_DDL_OPERATIONS=true`, cela fonctionne maintenant !

## [1.1.0] - 2025-01-08

### Added
- 🔒 **Nouvelles permissions DDL** : Contrôle granulaire des opérations de schéma
  - `ALLOW_DDL_OPERATIONS=true/false` : Autorise CREATE, ALTER, DROP tables/indexes
  - `ALLOW_ALL_OPERATIONS=true/false` : Mode super admin autorisant toutes les opérations
  - Résout les erreurs "Mot-clé non autorisé détecté: ALTER" pour la gestion de schéma
- 🧪 **Suite de test complète** : Infrastructure de test professionnelle
  - **Codeception + PHPUnit** : 29+ tests unitaires et d'intégration
  - **Docker MySQL automatisé** : Environnement de test isolé avec fixtures
  - **GitHub Actions CI/CD** : Tests automatiques sur PHP 8.1, 8.2, 8.3
  - **Makefile** : 20+ commandes pour développeurs (`make test`, `make test-coverage`)
  - **Couverture >90%** : Services critiques entièrement testés
- 📚 **Documentation étendue** : Guides complets pour développeurs
  - [docs/TESTING.md](docs/TESTING.md) : Architecture et stratégie de test
  - [tests/README.md](tests/README.md) : Guide de démarrage rapide

### Enhanced  
- ⚡ **Sécurité renforcée** : Système de permissions à 3 niveaux
  - **Niveau 1** : CRUD (INSERT, UPDATE, DELETE, TRUNCATE)
  - **Niveau 2** : DDL (CREATE, ALTER, DROP) - nouveau !
  - **Niveau 3** : Super admin (GRANT, SYSTEM, etc.)
- 🛡️ **Validation améliorée** : Parsing booléen robuste pour configuration
  - Support 'true', '1', 'yes', 'on' et équivalents négatifs
  - Tests de toutes les combinaisons de permissions
- 🔧 **Outils de développement** : Scripts d'automatisation et debug
  - `tests/scripts/docker-test-complete.sh` : Runner de test complet
  - `tests/scripts/run-docker-tests.php` : Alternative PHP
  - Nettoyage automatique des ressources Docker

### Technical
- **Breaking change mineur** : Nouveau système de permissions (backward compatible)
- **PHP 8.1+ requis** : Tests sur versions 8.1, 8.2, 8.3
- **Dependencies** : Ajout Codeception, Symfony Process pour tests
- **CI/CD** : Pipeline GitHub Actions pour qualité continue

### Migration Notes
Pour activer les nouvelles permissions DDL dans votre configuration :
```bash
# Autorise CREATE, ALTER, DROP
ALLOW_DDL_OPERATIONS=true

# Mode super admin (utiliser avec précaution)  
ALLOW_ALL_OPERATIONS=true
```

## [1.0.2] - 2025-08-06

### Added
- 🚀 **Nouvel outil `mysql_list_table_names`** : Liste ultra-économe des noms de tables (résout les dépassements de tokens)
  - Support de limite configurable (défaut: 100, max: 1000)
  - Retour minimal pour économiser les tokens
  - Idéal pour l'exploration de grandes bases de données

### Enhanced
- ⚡ **Amélioration de `mysql_list_tables`** : Gestion intelligente des tokens
  - **Nouveau paramètre `detailed`** : Mode simple par défaut (économe) vs mode détaillé (complet)
  - **Nouveau paramètre `limit`** : Limitation configurable (défaut: 50, max: 500)
  - **Mode simple** : Retourne seulement `{"name": "table_name"}` pour chaque table
  - **Mode détaillé** : Garde le comportement original avec toutes les métadonnées
  - **Informations de pagination** : `total_table_count`, `truncated`, `limited_to`
- 📊 **Protection contre dépassement de tokens** : Résout l'erreur "response exceeds maximum allowed tokens"

### Documentation
- 📚 **Mise à jour de la documentation MCP Tools** : Nouvelles fonctionnalités documentées avec exemples
- 🎯 **Guide de performance** : Recommandations d'usage selon le besoin en tokens

### Technical
- 🧪 **Nouveau script de test** : `tests/test_improved_tables.php` pour valider les améliorations
- 🔄 **Compatibilité descendante** : Aucun changement breaking, tous les appels existants fonctionnent

**Impact Performance** :
- `mysql_list_table_names` : ~200-500 tokens (vs ~33,940 avant)
- `mysql_list_tables` mode simple : ~500-1000 tokens (vs ~33,940 avant)
- `mysql_list_tables` mode détaillé : Comme avant mais limité automatiquement

## [1.0.1] - 2025-08-05

### Fixed
- 🧹 **Nettoyage des variables de configuration** : Suppression de 7 variables déclarées mais non implémentées
- 📚 **Documentation cohérente** : Mise à jour de tous les exemples et guides pour correspondre aux fonctionnalités réelles
- 🎯 **Expérience utilisateur améliorée** : Suppression des options confuses qui ne fonctionnaient pas

### Removed
- `ENABLE_QUERY_LOGGING` - Pas de système de logging des requêtes implémenté
- `CACHE_TTL` - Pas de système de cache implémenté
- `ENABLE_QUERY_CACHE` - Pas de fonctionnalité de cache de requêtes
- `ENABLE_TRANSACTIONS` - Pas de gestion des transactions implémentée
- `ENABLE_SCHEMA_INTROSPECTION` - Fonctionnalité non utilisée dans l'implémentation
- `ENABLE_EXPORT_TOOLS` - Pas d'outils d'export implémentés
- `LOG_FILE` - Le logging se fait uniquement vers stdout/stderr

### Documentation
- Mise à jour du template `.env.example`
- Nettoyage du guide de configuration MCP
- Correction de tous les exemples dans la documentation
- Suppression des références aux fonctionnalités non implémentées

**Note** : Aucun changement breaking, toutes les fonctionnalités existantes sont préservées.

## [1.0.0] - 2025-08-05

### Added
- 🎉 **Release initiale stable** du serveur MySQL MCP
- 🔧 **Serveur MCP complet** avec support de toutes les opérations MySQL (SELECT, INSERT, UPDATE, DELETE)
- 🛡️ **Système de sécurité avancé** avec contrôles de permissions configurables
- ⚙️ **Configuration par variables d'environnement** pour tous les paramètres
- 📡 **Support multi-transport** MCP (stdio, http, websocket)
- 🔄 **Connection pooling et cache** pour optimiser les performances
- 📝 **Gestion d'erreurs et logging** complets et structurés
- 📚 **Documentation exhaustive** avec guides d'installation et démarrage rapide
- 🔧 **Guide de configuration MCP** pour tous les clients (Claude Code, Cursor, Zed)
- 📖 **Exemples d'usage** et configuration multi-projets
- 🚨 **Guide de dépannage** et bonnes pratiques
- 🧪 **Utilitaires de test** pour connexion et validation de configuration
- 📦 **Package Composer** prêt pour publication sur Packagist
- ⚖️ **Licence MIT** pour usage commercial
- 🛡️ **Sécurité renforcée** : pas de credentials dans le repository
- 🎯 **Tests complets** : connexion, configuration MCP, serveur

### Technical
- Support PHP 8.1+
- Intégration avec php-mcp/server v3.3
- Architecture modulaire avec services et outils MCP
- Validation des requêtes et sanitisation
- Gestion des timeouts et limites de résultats
- Dependency injection container
- PSR-12 coding standards
- Package Composer pour installation via `composer require morgan/mysql-mcp`
- Scripts d'installation automatiques
- Badges de documentation dans README

### Changed
- Restructuration en dossiers : bin/, tests/, scripts/, examples/
- Amélioration du README avec installation Composer
- Noms de package standardisés

### Removed
- CONFIGURATION_GUIDE.md redondant (fusionné dans docs/)

## [1.0.0] - 2025-08-05

### Added
- Serveur MCP MySQL complet avec 9 outils
- Configuration par variables d'environnement
- Support multi-projets
- Documentation complète dans docs/
- Tests automatisés
- Exemples de configuration
- Sécurité avec validation SQL
- Gestion de pool de connexions
- Support pour MySQL 5.7+ et 8.0+

### Features
- **Base de données** : list_databases, list_tables, describe_table, server_status
- **Requêtes** : mysql_select avec sécurité anti-injection
- **Écriture** : mysql_insert, mysql_update, mysql_delete avec permissions
- **Avancé** : mysql_execute_query pour requêtes personnalisées
- **Configuration** : .env, variables MCP, arguments CLI
- **Multi-projets** : wrapper pour configurations séparées