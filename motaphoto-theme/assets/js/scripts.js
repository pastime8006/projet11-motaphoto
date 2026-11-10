// Attend que le document soit prêt et récupère jQuery sous le nom $.
jQuery(function ($) {
  
  const $gallery = $("[data-gallery]");
  let openCustomSelect = null;

  // Pour chaque select du formulaire de tri de la galerie.
  $gallery.find(".sorting-form select").each(function () {
    // Le select natif reste la source de vérité pour le formulaire et Ajax.
    const select = this;
    const wrapper = document.createElement("div");
    const trigger = document.createElement("button");
    const list = document.createElement("ul");
    let activeIndex = select.selectedIndex;

    // Pose plusieurs attributs accessibles sur un élément.
    const setAttrs = (element, attrs) =>
      Object.entries(attrs).forEach(([key, value]) => element.setAttribute(key, value));

    // Retourne les indices des options qui ne sont pas désactivées.
    const enabled = () =>
      Array.from(select.options).flatMap((option, index) => (option.disabled ? [] : [index]));

    // Construit le bouton visible du menu.
    wrapper.className = `custom-select custom-select--${select.name}`;
    trigger.type = "button";
    trigger.className = "custom-select__trigger";
    setAttrs(trigger, {
      role: "combobox",
      "aria-haspopup": "listbox",
      "aria-expanded": "false",
      "aria-controls": `${select.id}-options`,
      "aria-label": select.getAttribute("aria-label") || select.name,
    });

    // Construit la liste qui s'ouvre sous le bouton.
    list.className = "custom-select__options";
    list.id = `${select.id}-options`;
    list.hidden = true;
    list.setAttribute("role", "listbox");

    // Reproduit les options natives sous forme de lignes HTML stylisables.
    const items = Array.from(select.options, (option, index) => {
      const item = document.createElement("li");
      item.className = "custom-select__option";
      item.id = `${select.id}-option-${index}`;
      item.setAttribute("role", "option");
      item.textContent = option.textContent.trim();
      if (option.disabled) item.setAttribute("aria-disabled", "true");
      return item;
    });
    list.append(...items);

    // Synchronise le texte du bouton et la sélection annoncée aux lecteurs d'écran.
    function sync() {
      const selected = select.options[select.selectedIndex];
      trigger.textContent = selected ? selected.textContent.trim() : "";
      items.forEach((item, index) =>
        item.setAttribute("aria-selected", String(index === select.selectedIndex))
      );
    }

    // Met une option en surbrillance sans modifier la valeur sélectionnée.
    function setActive(index) {
      const item = items[index];
      if (!item || select.options[index].disabled) return;

      activeIndex = index;
      items.forEach((optionItem, itemIndex) =>
        optionItem.classList.toggle("is-active", itemIndex === index)
      );
      trigger.setAttribute("aria-activedescendant", item.id);
      item.scrollIntoView({ block: "nearest" });
    }

    // Ouvre ou ferme la liste et garde son état accessible à jour.
    function toggle(open) {
      if (open && openCustomSelect && openCustomSelect !== toggle) {
        openCustomSelect(false);
      }

      list.hidden = !open;
      trigger.setAttribute("aria-expanded", String(open));
      if (open) {
        openCustomSelect = toggle;
        setActive(select.selectedIndex);
      } else {
        trigger.removeAttribute("aria-activedescendant");
        if (openCustomSelect === toggle) openCustomSelect = null;
      }
    }

    // Met à jour le select natif, puis avertit le gestionnaire Ajax si la valeur change.
    function choose(index) {
      if (index < 0 || !select.options[index] || select.options[index].disabled) return;

      const changed = select.selectedIndex !== index;
      select.selectedIndex = index;
      sync();
      toggle(false);
      if (changed) select.dispatchEvent(new Event("change", { bubbles: true }));
      trigger.focus();
    }

    // Délègue les événements à la liste et ignore les clics hors des options.
    const indexOf = (event) => {
      const item = event.target.closest(".custom-select__option");
      return item ? items.indexOf(item) : -1;
    };
    list.addEventListener("pointerover", (event) => setActive(indexOf(event)));
    list.addEventListener("click", (event) => choose(indexOf(event)));

    // Le bouton inverse l'état actuel du menu.
    trigger.addEventListener("click", () => toggle(list.hidden));

    // Gère la navigation clavier entre les options.
    trigger.addEventListener("keydown", (event) => {
      const indices = enabled();
      if (!indices.length) return;

      const position = Math.max(indices.indexOf(activeIndex), 0);
      const moves = {
        ArrowDown: indices[(position + 1) % indices.length],
        ArrowUp: indices[(position - 1 + indices.length) % indices.length],
        Home: indices[0],
        End: indices[indices.length - 1],
      };

      if (event.key in moves) {
        event.preventDefault();
        if (list.hidden) toggle(true);
        setActive(moves[event.key]);
      } else if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        if (list.hidden) toggle(true);
        else choose(activeIndex);
      } else if ((event.key === "Escape" || event.key === "Tab") && !list.hidden) {
        toggle(false);
      }
    });

    // Synchronise si un autre code modifie la valeur du select natif.
    select.addEventListener("change", sync);
    // Ferme le menu au clic extérieur.
    document.addEventListener("pointerdown", (event) => {
      if (!wrapper.contains(event.target)) toggle(false);
    });

    // Insère le faux menu après le select, qui reste présent pour les formulaires.
    sync();
    select.after(wrapper);
    wrapper.append(trigger, list);
    select.hidden = true;
  });

  let galleryRequest = null;
  let galleryRequestId = 0;

  // Charge une page de photos; les pages suivantes s'ajoutent à la grille.
  function chargerGalerie(page = 1, append = false) {
    if (!$gallery.length || typeof motaphotoGallery === "undefined") return;

    const requestId = ++galleryRequestId;
    if (galleryRequest) {
      galleryRequest.abort();
    }

    const $results = $gallery.find(".gallery-results");
    const $button = $gallery.find(".gallery-load-more");
    $results.attr("aria-busy", "true");

    if (append) {
      $button.prop("disabled", true).text("Chargement…");
    }

    galleryRequest = $.ajax({
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
        append: append ? 1 : 0,
      },
    })
      .done(function (response) {
        if (!response || !response.success || !response.data) {
          console.error("La réponse du filtrage de la galerie est invalide.", response);
          return;
        }

        if (append) {
          const $responseContent = $("<div>").html(response.data.grid);
          const $responseGrid = $responseContent.find(".photos-grid").first();
          const $newPhotos = $responseGrid.length
            ? $responseGrid.children()
            : $responseContent.contents();

          $results.find(".photos-grid").append($newPhotos);
          $results.find(".gallery-pagination").remove();
          $results.append(response.data.pagination);
        } else {
          $results.html(response.data.grid + response.data.pagination);
        }
      })
      .fail(function (xhr, status, error) {
        if (status !== "abort") {
          console.error("Échec du filtrage Ajax de la galerie.", status, error);
          if (requestId === galleryRequestId && append) {
            $button.prop("disabled", false).text("Charger plus");
          }
        }
      })
      .always(function () {
        if (requestId !== galleryRequestId) return;

        galleryRequest = null;
        $results.attr("aria-busy", "false");

        const $currentButton = $gallery.find(".gallery-load-more");
        if (append && Number($currentButton.data("page")) === page) {
          $currentButton.prop("disabled", false);
        }
        $currentButton.text("Charger plus");
      });
  }

  // Un changement de filtre recharge la première page et remplace les anciennes photos.
  $gallery.on("change", ".sorting-form select", function () {
    chargerGalerie(1);
  });

  // Le bouton ajoute la page suivante à la grille existante.
  $gallery.on("click", ".gallery-load-more", function () {
    chargerGalerie(Number($(this).data("page")), true);
  });

  // Ouvre la modale depuis le lien Contact ou les boutons de contact des photos.
  $(document).on(
    "click",
    'a[href="#contact"], .btn-contact-infos',
    function (event) {
      // Empêche le lien de naviguer vers une ancre ou de recharger la page.
      event.preventDefault();

      // Récupère la référence attachée au bouton (ou une chaîne vide si elle manque).
      const reference = $(this).data("reference") || "";
      // Insère la référence dans le champ du formulaire de contact.
      $(".popup-contact [name='ref-photos']").val(reference);

      // Affiche la modale de contact.
      $(".popup-overlay").css("display", "flex");
    }
  );

  // Ferme la modale uniquement si le clic porte sur le fond extérieur.
  $(".popup-overlay").on("click", function (event) {
    // Ne ferme pas la modale si l'utilisateur clique sur son contenu.
    if (event.target === this) {
      $(this).hide();
    }
  });

  // Ferme la modale lorsque l'utilisateur appuie sur la touche Échap.
  $(document).on("keydown", function (event) {
    // Vérifie que la touche Échap a été pressée.
    if (event.key === "Escape") {
      $(".popup-overlay").hide();
    }
  });
});
