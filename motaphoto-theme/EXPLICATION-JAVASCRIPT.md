# Explication du JavaScript de la modale

Le script concerné est `assets/js/scripts.js`. Il utilise jQuery pour ouvrir et
fermer la modale de contact.

## Initialisation jQuery

```js
jQuery(function($) {
    // Code exécuté lorsque le document est prêt.
});
```

Cette forme attend que le document HTML soit prêt avant d'attacher les
gestionnaires d'événements. Le paramètre `$` permet d'utiliser la syntaxe
jQuery à l'intérieur du bloc.

## Ouvrir la modale avec le lien Contact

```js
$(document).on('click', 'a[href="#contact"]', function(event) {
    event.preventDefault();
    $('.popup-overlay').show();
});
```

- Le sélecteur `a[href="#contact"]` cible un lien dont l'adresse est exactement
  `#contact`. Dans le menu WordPress, il faut donc configurer le lien
  personnalisé Contact avec cette URL.
- Le gestionnaire est délégué à `document` : il fonctionne même si le menu est
  rendu ou remplacé après l'exécution initiale du script.
- `event.preventDefault()` empêche le navigateur de suivre l'ancre et d'ajouter
  `#contact` à l'URL.
- `.show()` rend visible l'élément `.popup-overlay`.

## Fermer en cliquant à l'extérieur

```js
$('.popup-overlay').on('click', function(event) {
    if (event.target === this) {
        $(this).hide();
    }
});
```

Le clic est écouté sur tout l'overlay, mais la modale ne se ferme que si
`event.target === this` : la cible du clic doit être l'overlay lui-même, c'est-à-dire
le fond extérieur. Si le clic touche la fenêtre `.popup-contact` ou son
formulaire, cette condition est fausse et la modale reste ouverte.

`$(this).hide()` masque l'overlay courant.

## Fermer avec Échap

```js
$(document).on('keydown', function(event) {
    if (event.key === 'Escape') {
        $('.popup-overlay').hide();
    }
});
```

Le gestionnaire écoute les touches du clavier dans le document. Si la touche
pressée est `Escape`, jQuery masque l'overlay.

## Comportement actuel et points à retenir

- Le script n'ajoute pas de classe de fermeture et n'anime pas la modale.
- `.show()` et `.hide()` affichent et masquent immédiatement l'overlay.
- Le lien ne sera intercepté que si son `href` est exactement `#contact`.
- Pour que la modale ne soit pas visible au chargement, son style initial doit
  la masquer en CSS ; le script seul ne la masque pas au chargement.
- Le clic sur le fond et la touche Échap la ferment sans modifier l'URL.
