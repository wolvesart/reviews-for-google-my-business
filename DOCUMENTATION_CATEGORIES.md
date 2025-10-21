# Documentation - Système de catégories pour les avis Google My Business

## Vue d'ensemble

Le système de catégories permet d'organiser vos avis Google My Business en différentes catégories personnalisables. Vous pouvez ensuite afficher les avis filtrés par catégorie sur votre site web.

## Gestion des catégories

### Créer une catégorie

1. Allez dans **Avis Google** > **Liste des avis** dans l'administration WordPress
2. Dans la section "Gestion des catégories", entrez le nom de votre catégorie
3. Cliquez sur "Créer une catégorie"

Exemples de catégories :
- Formation
- Coaching
- Design
- Développement
- Branding

### Assigner des catégories à un avis

1. Dans la liste des avis, trouvez l'avis que vous souhaitez catégoriser
2. Dans la colonne "Catégorie", cochez une ou plusieurs cases correspondant aux catégories souhaitées
3. Cliquez sur "Enregistrer"

**Interface :** Les catégories sont présentées sous forme de cases à cocher empilées :
- ☐ Catégorie 1
- ☑ Catégorie 2 (cochée)
- ☐ Catégorie 3

**Note :** Vous pouvez assigner plusieurs catégories à un même avis. Par exemple, un avis peut être à la fois dans "Formation" et "Design". Les catégories sélectionnées sont mises en surbrillance bleue.

### Supprimer une catégorie

1. Dans la section "Gestion des catégories", cliquez sur "Supprimer" à côté de la catégorie
2. Confirmez la suppression
3. La catégorie sera retirée de tous les avis qui l'utilisent

## Utilisation du shortcode

### Syntaxe de base

```
[gmb_reviews]
```

Affiche tous les avis (maximum 50 par défaut).

### Paramètres disponibles

#### `limit` - Nombre d'avis à afficher

```
[gmb_reviews limit="10"]
```

Limite l'affichage à 10 avis.

#### `category` - Filtrer par catégorie

```
[gmb_reviews category="formation"]
```

Affiche uniquement les avis de la catégorie "formation" (utiliser le **slug** de la catégorie).

**Note :** Le slug est généré automatiquement à partir du nom de la catégorie. Par exemple :
- "Formation Figma" → slug: `formation-figma`
- "Design Web" → slug: `design-web`
- "Coaching" → slug: `coaching`

Vous pouvez voir le slug dans la liste des catégories dans l'admin.

#### Combiner plusieurs paramètres

```
[gmb_reviews category="formation" limit="5"]
```

Affiche les 5 premiers avis de la catégorie "formation".

#### Afficher uniquement les avis sans catégorie

```
[gmb_reviews category=""]
```

Affiche uniquement les avis qui n'ont pas de catégorie assignée.

### Exemples d'utilisation

#### Exemple 1 : Page de formations

Sur votre page de formations Figma, affichez uniquement les avis liés aux formations :

```
[gmb_reviews category="formation-figma" limit="6"]
```

#### Exemple 2 : Page d'accueil

Sur la page d'accueil, affichez tous les avis :

```
[gmb_reviews limit="12"]
```

#### Exemple 3 : Section coaching

Dans une section dédiée au coaching :

```
[gmb_reviews category="coaching" limit="3"]
```

## Structure de la base de données

Le système utilise trois tables personnalisées :

### Table `wp_gmb_review_categories`

Stocke les catégories d'avis :
- `id` : Identifiant unique
- `name` : Nom de la catégorie
- `slug` : Slug utilisé dans le shortcode
- `created_at` : Date de création

### Table `wp_gmb_reviews_custom`

Stocke les données personnalisées des avis :
- `id` : Identifiant unique
- `review_id` : ID de l'avis Google
- `reviewer_name` : Nom du reviewer
- `job` : Poste de la personne
- `created_at` : Date de création
- `updated_at` : Date de mise à jour

### Table `wp_gmb_review_category_relations`

Table de relation many-to-many entre avis et catégories (permet d'assigner plusieurs catégories à un avis) :
- `id` : Identifiant unique
- `review_id` : ID de l'avis Google
- `category_id` : ID de la catégorie
- `created_at` : Date de création

## Fonctions disponibles

Si vous souhaitez utiliser les catégories dans votre code PHP personnalisé :

### `gmb_get_all_categories()`

Récupère toutes les catégories.

```php
$categories = gmb_get_all_categories();
foreach ($categories as $category) {
    echo $category->name . ' (' . $category->slug . ')';
}
```

### `gmb_get_category_by_slug($slug)`

Récupère une catégorie par son slug.

```php
$category = gmb_get_category_by_slug('formation');
if ($category) {
    echo $category->name;
}
```

### `gmb_filter_reviews_by_category($reviews, $category_slug)`

Filtre une liste d'avis par catégorie.

```php
$data = gmb_fetch_reviews();
$filtered_reviews = gmb_filter_reviews_by_category($data['reviews'], 'formation');
```

## Notes importantes

1. **Slug automatique** : Le slug est généré automatiquement à partir du nom de la catégorie (caractères spéciaux remplacés par des tirets, minuscules)

2. **Cache** : Les avis sont mis en cache pendant 1 heure. Si vous modifiez les catégories, le cache sera automatiquement vidé.

3. **Unicité** : Vous ne pouvez pas créer deux catégories avec le même nom (car le slug doit être unique).

4. **Suppression** : La suppression d'une catégorie retire automatiquement cette catégorie de tous les avis qui l'utilisent.

5. **Catégories multiples** : Un avis peut avoir plusieurs catégories. Par exemple, un avis peut être catégorisé à la fois comme "Formation" et "Design". Le filtrage par shortcode affichera cet avis dans les deux catégories.

## Support

Pour toute question ou problème, consultez la documentation principale du système GMB Reviews ou contactez le développeur du thème.
