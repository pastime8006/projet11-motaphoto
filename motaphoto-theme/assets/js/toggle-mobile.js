const btnMobile = document.querySelector(".btn-menu-mobile");
const siteNav = document.querySelector(".site-navigation");

if (btnMobile && siteNav) {
  btnMobile.addEventListener("click", () => {
     const isVisible = siteNav.classList.toggle("is-visible");
   btnMobile.classList.toggle("is-active");
   
    btnMobile.setAttribute("aria-expanded", String(isVisible));

  });
}