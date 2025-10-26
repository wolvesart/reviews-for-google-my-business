# Google My Business Reviews

Plugin WordPress pour afficher et gérer vos avis Google My Business avec OAuth 2.0.

## Description

**Google My Business Reviews** est un plugin WordPress complet qui vous permet d'afficher vos avis Google My Business sur votre site web de manière élégante et personnalisable.

### Fonctionnalités principales

✅ **Authentification OAuth 2.0** - Connexion sécurisée à l'API Google My Business
✅ **Système de catégories** - Organisez vos avis par catégories (Formation, Coaching, Design, etc.)
✅ **Catégories multiples** - Assignez plusieurs catégories à un même avis
✅ **Shortcode flexible** - Affichez tous les avis ou filtrez par catégorie
✅ **Personnalisation avancée** - Couleurs, bordures, étoiles personnalisables
✅ **Interface admin intuitive** - Cases à cocher pour les catégories
✅ **Champs personnalisés** - Ajoutez le poste de chaque reviewer
✅ **Cache intelligent** - Optimisation des performances (1h de cache)
✅ **Responsive** - S'adapte à tous les écrans

## Installation

1. Téléchargez le plugin
2. Décompressez l'archive dans `/wp-content/plugins/`
3. Activez le plugin depuis l'admin WordPress
4. Allez dans **Avis Google → Configuration** pour configurer l'API

## Configuration de l'API Google

### 1. Créer un projet Google Cloud

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet ou sélectionnez-en un existant

### 2. Activer les APIs

Activez les APIs suivantes :
- Google My Business API
- My Business Account Management API
- My Business Business Information API

### 3. Configurer OAuth 2.0

1. Allez dans "APIs et services" → "Identifiants"
2. Créez un "ID client OAuth 2.0"
3. Type : Application Web
4. Ajoutez l'URI de redirection fournie dans la configuration

### 4. Entrer les identifiants

Dans WordPress, allez dans **Avis Google → Configuration** et entrez :
- Client ID
- Client Secret
- Redirect URI

## Utilisation

### Créer des catégories

1. Allez dans **Avis Google → Catégories**
2. Entrez le nom de la catégorie
3. Cliquez sur "Créer la catégorie"

### Assigner des catégories aux avis

1. Allez dans **Avis Google → Liste des avis**
2. Pour chaque avis, cochez les catégories souhaitées
3. Cliquez sur "Enregistrer"

### Afficher les avis sur votre site

#### Shortcode de base

```php
[gmb_reviews]
```
Affiche tous les avis (max 50)

#### Filtrer par catégorie

```php
[gmb_reviews category="formation"]
```
Affiche uniquement les avis de la catégorie "formation"

#### Limiter le nombre d'avis

```php
[gmb_reviews limit="10"]
```
Affiche 10 avis

#### Combiner les paramètres

```php
[gmb_reviews category="coaching" limit="5"]
```
Affiche 5 avis de la catégorie "coaching"

#### Avis sans catégorie

```php
[gmb_reviews category=""]
```
Affiche uniquement les avis sans catégorie

## Paramètres du shortcode

| Paramètre | Type | Description | Défaut |
|-----------|------|-------------|--------|
| `limit` | int | Nombre d'avis à afficher | 50 |
| `category` | string | Slug de la catégorie à filtrer | null (toutes) |

## Personnalisation

Allez dans **Avis Google → Configuration → Personnalisation** pour modifier :

- Couleur de fond des cartes
- Arrondi des bordures
- Couleur des étoiles
- Couleur du texte
- Couleur d'accent
- Couleur du résumé

## Structure du plugin

```
google-my-business-reviews/
├── google-my-business-reviews.php    # Fichier principal
├── includes/                  # Fonctions PHP
│   ├── config.php
│   ├── api.php
│   ├── database.php
│   ├── categories.php
│   ├── helpers.php
│   ├── admin.php
│   └── shortcode.php
├── templates/                 # Templates admin
│   ├── admin-page.php
│   ├── categories-page.php
│   ├── manage-reviews-page.php
│   └── reviews-display.php
├── assets/
│   ├── css/                  # CSS compilés
│   ├── js/                   # JavaScript
│   └── scss/                 # Sources SCSS
└── README.md
```

## Base de données

Le plugin crée 3 tables personnalisées :

### wp_gmb_review_categories
Stocke les catégories d'avis

### wp_gmb_reviews_custom
Stocke les données personnalisées (poste)

### wp_gmb_review_category_relations
Table de relation many-to-many (avis ↔ catégories)

## Hooks disponibles

### Actions

- `wolves_gmb_after_review_save` - Après la sauvegarde d'un avis
- `wolves_gmb_before_review_display` - Avant l'affichage d'un avis
- `wolves_gmb_after_category_create` - Après la création d'une catégorie

### Filtres

- `wolves_gmb_review_data` - Modifier les données d'un avis
- `wolves_gmb_reviews_query` - Modifier la requête de récupération des avis
- `wolves_gmb_custom_css` - Personnaliser le CSS généré

## Compatibilité

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+

## Support

Pour toute question ou problème :
- 📧 Email : contact@wolvesart.com
- 🌐 Site web : https://wolvesart.com

## Changelog

### 1.0.0 - 2025-01-21
- Version initiale
- Système de catégories multiples
- Interface avec cases à cocher
- Page dédiée à la gestion des catégories
- Personnalisation avancée
- Documentation complète

## Licence

GPL v2 or later

## Crédits

Développé avec ❤️ par [Wolvesart](https://wolvesart.com)
