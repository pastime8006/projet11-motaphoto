# Analyse du filtrage Ajax de la galerie

## Objectif

La galerie permet de filtrer les photos par catégorie et par format, de choisir un ordre de tri, puis de parcourir les résultats par pages. Ces actions mettent à jour les photos sans recharger la page.

La requête passe par le mécanisme Ajax de WordPress, à l’adresse `admin-ajax.php`. La réponse contient le HTML de la grille et de sa pagination. Le navigateur remplace ensuite le contenu de `.gallery-results`.

## Parcours d’une demande

1. Le visiteur change un des menus ou clique sur un numéro de page.
2. `assets/js/scripts.js` lit les choix du formulaire et envoie une requête `POST`.
3. WordPress déclenche `motaphoto_ajax_filter_gallery()` grâce à l’action `motaphoto_filter_gallery`.
4. Le serveur vérifie le nonce, nettoie les valeurs reçues et construit une requête `WP_Query`.
5. Le serveur génère les cartes avec le même modèle que lors du premier affichage, puis renvoie la grille et la pagination en JSON.
6. JavaScript insère les résultats reçus dans la page. La lightbox continue de fonctionner grâce à son écouteur de clic délégué.

## Étapes, fichiers et code correspondant

### 1. Afficher les filtres et la galerie initiale

**Fichier :** `templates_part/content-gallery.php`

Le formulaire et son conteneur permettent au JavaScript de repérer la galerie et ses filtres :

```php
<section class="photos-page-content" data-gallery>
    <form class="sorting-form" method="get" action="...">
        <select name="categorie" id="categorie">...</select>
        <select name="format" id="format">...</select>
        <select name="tri" id="tri">...</select>
    </form>

    <div class="gallery-results" aria-live="polite" aria-busy="false">
        ...
    </div>
</section>
```

La première grille est aussi produite côté PHP avec `motaphoto_get_gallery_query()` et le modèle `templates_part/content-photo-card.php`.

### 2. Détecter un changement de filtre ou un clic sur une page

**Fichier :** `assets/js/scripts.js`

Les écouteurs sont attachés au conteneur de galerie. Celui de pagination est délégué pour fonctionner aussi après le remplacement des boutons :

```js
$gallery.on("change", ".sorting-form select", function () {
  chargerGalerie(1);
});

$gallery.on("click", ".gallery-page-link", function () {
  chargerGalerie($(this).data("page"));
});
```

### 3. Fournir l’URL Ajax et le nonce au navigateur

**Fichier :** `functions.php`

WordPress charge le script puis lui transmet l’adresse de `admin-ajax.php` et un nonce :

```php
wp_localize_script(
    'motaphoto-theme-scripts',
    'motaphotoGallery',
    array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'motaphoto_gallery_nonce' ),
    )
);
```

### 4. Envoyer les filtres et la page demandée

**Fichier :** `assets/js/scripts.js`

`chargerGalerie()` envoie les sélections et le numéro de page en `POST` :

```js
$.ajax({
  url: motaphotoGallery.ajaxUrl,
  method: "POST",
  dataType: "json",
  data: {
    action: "motaphoto_filter_gallery",
    nonce: motaphotoGallery.nonce,
    categorie: $gallery.find("[name='categorie']").val(),
    format: $gallery.find("[name='format']").val(),
    tri: $gallery.find("[name='tri']").val(),
    paged: page,
  },
});
```

### 5. Recevoir la requête et valider ses données

**Fichier :** `functions.php`

Les deux hooks WordPress rendent l’action accessible aux utilisateurs connectés et aux visiteurs. Le gestionnaire vérifie le nonce puis nettoie les valeurs reçues :

```php
add_action( 'wp_ajax_motaphoto_filter_gallery', 'motaphoto_ajax_filter_gallery' );
add_action( 'wp_ajax_nopriv_motaphoto_filter_gallery', 'motaphoto_ajax_filter_gallery' );

function motaphoto_ajax_filter_gallery() {
    check_ajax_referer( 'motaphoto_gallery_nonce', 'nonce' );

    $category = isset( $_POST['categorie'] ) ? sanitize_title( wp_unslash( $_POST['categorie'] ) ) : '';
    $format   = isset( $_POST['format'] ) ? sanitize_title( wp_unslash( $_POST['format'] ) ) : '';
    $sort     = isset( $_POST['tri'] ) ? sanitize_key( wp_unslash( $_POST['tri'] ) ) : 'date_desc';
    $page     = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
}
```

### 6. Construire et exécuter la requête de photos

**Fichier :** `functions.php`

Le gestionnaire transmet les critères à la fonction commune de requête :

```php
$query = motaphoto_get_gallery_query( $category, $format, $sort, $page );
```

Cette fonction construit les arguments de `WP_Query`, ajoute les taxonomies sélectionnées et exécute la recherche :

```php
$args = array_merge(
    array(
        'post_type'      => 'photo',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'paged'          => max( 1, absint( $page ) ),
    ),
    $sort_options[ $sort ]
);

return new WP_Query( $args );
```

### 7. Générer les cartes et la pagination

**Fichiers :** `functions.php`, `templates_part/content-photo-card.php`

Pour chaque résultat, le gestionnaire réutilise le modèle des cartes. La pagination est produite par `motaphoto_render_gallery_pagination()` :

```php
while ( $query->have_posts() ) {
    $query->the_post();
    get_template_part( 'templates_part/content', 'photo-card' );
}

$pagination_html = motaphoto_render_gallery_pagination( $query, max( 1, $page ) );
```

### 8. Renvoyer les résultats au navigateur

**Fichier :** `functions.php`

La réponse Ajax est envoyée au format JSON avec le HTML de la grille et de la pagination :

```php
wp_send_json_success(
    array(
        'grid'       => $grid_html,
        'pagination' => $pagination_html,
    )
);
```

### 9. Remplacer les résultats sans recharger la page

**Fichier :** `assets/js/scripts.js`

À la réception d’une réponse valide, JavaScript remplace le contenu de `.gallery-results` :

```js
.done(function (response) {
  if (!response || !response.success || !response.data) {
    console.error("La réponse du filtrage de la galerie est invalide.", response);
    return;
  }

  $results.html(response.data.grid + response.data.pagination);
});
```

## Rôle des fichiers

| Fichier | Rôle |
| --- | --- |
| `templates_part/content-gallery.php` | Affiche les menus, la première grille et les premiers boutons de pagination. |
| `functions.php` | Fournit au JavaScript l’URL Ajax et le nonce, prépare la requête de photos et traite la demande Ajax. |
| `templates_part/content-photo-card.php` | Rend une carte photo. Il est partagé par l’affichage initial et par la réponse Ajax. |
| `assets/js/scripts.js` | Détecte les changements de filtres et les clics de pagination, envoie les données et remplace les résultats. |
| `assets/js/lightbox.js` | Ouvre les cartes de la galerie dans la lightbox, y compris celles chargées après coup. |

## Données envoyées

La requête transmet les paramètres suivants :

```text
action=motaphoto_filter_gallery
nonce=<nonce WordPress>
categorie=<slug de catégorie ou vide>
format=<slug de format ou vide>
tri=<clé de tri>
paged=<numéro de page>
```

Les options de tri disponibles sont définies dans `motaphoto_gallery_sort_options()` :

| Clé | Ordre |
| --- | --- |
| `date_all` | Toutes les dates, de la plus récente à la plus ancienne (choix par défaut) |
| `date_desc` | Date, de la plus récente à la plus ancienne |
| `date_asc` | Date, de la plus ancienne à la plus récente |
| `title_asc` | Titre, de A à Z |
| `title_desc` | Titre, de Z à A |
| `annee_asc` | Champ `annee`, par ordre croissant |
| `annee_desc` | Champ `annee`, par ordre décroissant |

Le serveur n’accepte que ces clés. L’option « Toutes les dates » n’applique aucun filtre de période et affiche les photos dans l’ordre chronologique décroissant. Une clé inconnue revient au tri par date décroissante.

## Construction de la requête WordPress

`motaphoto_get_gallery_query()` est utilisée aussi bien pour la première grille que pour Ajax. Elle limite les résultats aux contenus `photo` publiés, avec huit photos par page.

Quand une catégorie et un format sont sélectionnés, les deux critères sont réunis avec la relation `AND` : une photo doit satisfaire les deux filtres. Si un menu est laissé sur sa valeur vide, son critère n’est pas ajouté.

Les valeurs reçues sont nettoyées avant d’être utilisées. Le nonce protège le traitement contre les requêtes intersites forgées. Les actions `wp_ajax_...` et `wp_ajax_nopriv_...` rendent le traitement accessible aux comptes connectés comme aux visiteurs.

## Pagination

Changer un filtre ou le tri relance la recherche à la page 1, pour éviter de demander une page qui n’existe plus avec les nouveaux critères. Cliquer sur un numéro envoie cette page tout en reprenant les sélections courantes.

La réponse reconstruit la pagination en fonction du nombre de pages obtenu par `WP_Query`. Les boutons étant remplacés avec les résultats, JavaScript écoute leurs clics sur le conteneur parent : ce mécanisme fonctionne aussi pour les boutons nouvellement créés.

## Lightbox et contenu chargé dynamiquement

Les cartes injectées après un appel Ajax n’existaient pas au chargement initial du document. `assets/js/lightbox.js` écoute donc les clics au niveau du document, puis repère si le clic provient de `.container-ellipse`. Il peut ainsi ouvrir les photos de la nouvelle grille sans réinitialiser les gestionnaires après chaque réponse.

## En cas de problème

- **Aucune requête dans l’onglet Réseau du navigateur** : vérifier que `assets/js/scripts.js` est chargé et que les menus sont dans un élément `[data-gallery]`.
- **Réponse Ajax en erreur** : vérifier dans la console et l’onglet Réseau le statut HTTP et le JSON retourné par `admin-ajax.php`.
- **Erreur de vérification du nonce** : vérifier que `wp_localize_script()` transmet `motaphotoGallery.nonce` au script et que la page n’est pas restée ouverte au-delà de la durée de validité du nonce.
- **Aucun résultat** : vérifier les slugs des taxonomies `categorie` et `format`, ainsi que les clés du champ personnalisé `annee`.
- **Tri par année inattendu** : les valeurs du champ `annee` doivent être numériques pour que `meta_value_num` donne l’ordre attendu.

## Ajouter une option de tri

1. Ajouter une clé et ses arguments `WP_Query` dans `motaphoto_gallery_sort_options()` dans `functions.php`.
2. Ajouter l’option correspondante dans le menu `tri` de `templates_part/content-gallery.php`.
3. Mettre à jour le tableau des options de tri de ce document.

La validation du tri côté serveur reste nécessaire même si le menu HTML ne propose que les options prévues, car les données envoyées par le navigateur peuvent être modifiées.
