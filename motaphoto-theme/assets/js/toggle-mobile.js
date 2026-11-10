const btnMobile = document.querySelector(".btn-menu-mobile");
const siteNav = document.querySelector(".site-navigation");

if (btnMobile && siteNav) {
  let scrollPosition = 0;

  const setMenuOpen = (isOpen) => {
    siteNav.classList.toggle("is-visible", isOpen);
    btnMobile.classList.toggle("is-active", isOpen);
    btnMobile.setAttribute("aria-expanded", String(isOpen));
    btnMobile.setAttribute(
      "aria-label",
      isOpen ? "Fermer le menu" : "Ouvrir le menu",
    );

    if (isOpen) {
      scrollPosition = window.scrollY;
      document.documentElement.classList.add("menu-open");
      document.body.style.setProperty(
        "--menu-scroll-offset",
        `-${scrollPosition}px`,
      );
      document.body.classList.add("menu-open");
      return;
    }

    document.documentElement.classList.remove("menu-open");
    document.body.classList.remove("menu-open");
    document.body.style.removeProperty("--menu-scroll-offset");
    window.scrollTo(0, scrollPosition);
  };

  btnMobile.addEventListener("click", () => {
    setMenuOpen(!siteNav.classList.contains("is-visible"));
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && siteNav.classList.contains("is-visible")) {
      setMenuOpen(false);
      btnMobile.focus();
    }
  });

  window.addEventListener("resize", () => {
    if (
      window.matchMedia("(min-width: 961px)").matches &&
      siteNav.classList.contains("is-visible")
    ) {
      setMenuOpen(false);
    }
  });
}