# Hauteur de la fenêtre en CSS

Dans `assets/scss/layout/_header.scss`, le menu mobile utilise :

```scss
height: 100vh;
height: 100dvh;
```

`dvh` signifie *dynamic viewport height* (hauteur dynamique de la fenêtre visible). `100dvh` correspond à toute la hauteur disponible à l’écran et s’ajuste lorsque les barres du navigateur mobile, comme la barre d’adresse, apparaissent ou disparaissent.

La déclaration `100vh` placée avant sert de solution de repli pour les navigateurs qui ne prennent pas en charge `dvh`. Les navigateurs compatibles appliquent ensuite `100dvh`.
