```php
<button
    class="btn-menu-mobile"
    type="button"
    aria-label="Ouvrir le menu"
    aria-expanded="false"
>
    <span class="line"></span>
</button>

<nav class="site-navigation" aria-label="Navigation principale">
    <?php
    wp_nav_menu(
        array(
            'theme_location' => 'main-menu',
            'container'      => false,
            'menu_class'     => 'main-menu',
            'fallback_cb'    => false,
        )
    );
    ?>
</nav>
```

```scss
@use "../utils/utils" as utils;

.site-header {
  .btn-menu-mobile {
    display: none;
  }

  @include utils.mquery-960 {
    .btn-menu-mobile {
      display: block;
      position: absolute;
      top: 26px;
      right: 40px;
      width: 30px;
      height: 30px;
      border: none;
      background-color: transparent;
      padding: 0;

      &.is-active::before {
        transform: translate(-50%, -50%) rotate(45deg);
      }

      &.is-active::after {
        transform: translate(-50%, -50%) rotate(-45deg);
      }

      &.is-active .line {
        opacity: 0;
      }

      .line,
      &::before,
      &::after {
        position: absolute;
        left: 50%;
        width: 100%;
        height: 2px;
        border-radius: 5px;
        background-color: utils.$color-black;
        transition:
          transform 0.25s ease-in-out,
          opacity 0.2s ease-in-out;
      }

      .line {
        top: 50%;
        transform: translate(-50%, -50%);
      }

      &::before,
      &::after {
        content: "";
        top: 50%;
      }

      &::before {
        transform: translate(-50%, -50%) translateY(-7px);
      }

      &::after {
        transform: translate(-50%, -50%) translateY(7px);
      }
    }

    .site-navigation {
      position: absolute;
      top: 80px;
      right: 0;
      bottom: 0;
      left: 0;
      z-index: 200;
      width: 100vw;
      height: 100vh;
      height: calc(100dvh - 80px);
      padding: 0;
      box-sizing: border-box;
      display: flex;
      background-color: utils.$color-primary;
      justify-content: center;
      align-items: center;
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transform: translateY(-12px);
      transition:
        opacity 0.3s ease,
        transform 0.3s ease,
        visibility 0s linear 0.3s;

      &.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0);
        transition:
          opacity 0.3s ease,
          transform 0.3s ease,
          visibility 0s;
      }

      .main-menu {
        flex-direction: column;
        gap: 18px;

        .menu-item a {
          color: utils.$color-white;
          @include utils.font-space-mono(44px, 400);
        }
      }
    }
  }
}
```

```js
const btnMobile = document.querySelector(".btn-menu-mobile");
const siteNav = document.querySelector(".site-navigation");

if (btnMobile && siteNav) {
  btnMobile.addEventListener("click", () => {
    const isVisible = siteNav.classList.toggle("is-visible");
    btnMobile.classList.toggle("is-active", isVisible);
    btnMobile.setAttribute("aria-expanded", String(isVisible));
  });
}
```
