# Modifications CSS — 29 octobre 2026

Les styles de la galerie de la page Photos ont été ajoutés dans
[`assets/scss/layout/_photos-page.scss`](./assets/scss/layout/_photos-page.scss),
puis compilés dans [`assets/css/main.css`](./assets/css/main.css).

## Galerie photos

- Affichage en deux colonnes sur les écrans suffisamment larges.
- Largeur maximale de chaque colonne : `564px`, avec un espace de `40px`.
- Sur les écrans de `767px` ou moins, la galerie passe sur une seule colonne.

## Dimensions des images

- Le lien contenant chaque image garde le ratio `564 / 495`.
- Le contenu qui dépasse est masqué.
- L’image remplit la largeur et la hauteur du cadre (`width: 100%`, `height: 100%`).
- `object-fit: cover` recadre les images paysage ou portrait pour remplir le cadre sans les déformer.

Les colonnes et les images peuvent se réduire sur les écrans plus étroits afin de
rester dans la largeur disponible ; le ratio est conservé.
