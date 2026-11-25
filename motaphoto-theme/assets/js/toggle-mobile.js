// Récupération des éléments du menu mobile.
const btnMobile = document.querySelector(".btn-menu-mobile");
const siteNav = document.querySelector(".site-navigation");

// Initialisation du menu si les éléments nécessaires sont présents.
if (btnMobile && siteNav) {
  let scrollPosition = 0;

  // Ouvre ou ferme le menu et met à jour son état d'accessibilité.
  const setMenuOpen = (isOpen) => {
    siteNav.classList.toggle("is-visible", isOpen);
    btnMobile.classList.toggle("is-active", isOpen);
    btnMobile.setAttribute("aria-expanded", String(isOpen));
    btnMobile.setAttribute(
      "aria-label",
      isOpen ? "Fermer le menu" : "Ouvrir le menu",
    );

    if (isOpen) {
      // Mémorise la position verticale actuelle avant de bloquer le défilement.
      scrollPosition = window.scrollY;

      // Empêche le document HTML de défiler pendant que le menu est ouvert.
      document.documentElement.classList.add("menu-open");

      // Conserve la position de défilement dans une variable CSS utilisée par le body fixe.
      document.body.style.setProperty(
        "--menu-scroll-offset",
        `-${scrollPosition}px`,
      );

      // Fixe le body et applique le décalage mémorisé pour éviter un saut visuel.
      document.body.classList.add("menu-open");

      // Arrête la fonction pour ne pas exécuter le code de fermeture du menu.
      return;
    }

    document.documentElement.classList.remove("menu-open");
    document.body.classList.remove("menu-open");
    document.body.style.removeProperty("--menu-scroll-offset");

    window.scrollTo(0, scrollPosition);
  };

  // Gestion du clic sur le bouton hamburger.
  btnMobile.addEventListener("click", () => {
    setMenuOpen(!siteNav.classList.contains("is-visible"));
  });

  // Fermeture du menu avec la touche Échap.
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && siteNav.classList.contains("is-visible")) {
      setMenuOpen(false);
      btnMobile.focus();
    }
  });

  // Fermeture automatique du menu lors du passage en vue desktop.
  window.addEventListener("resize", () => {
    if (
      window.matchMedia("(min-width: 961px)").matches &&
      siteNav.classList.contains("is-visible")
    ) {
      setMenuOpen(false);
    }
  });
}
