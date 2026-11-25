jQuery(function ($) {
  const $gallery = $("[data-gallery]");

  //=========================================
  // MENUS DÉROULANTS PERSONNALISÉS
  //=========================================

  $gallery.find(".sorting-form select").each(function () {
    const select = this; // le select natif reste la source de vérité
    let active = select.selectedIndex;

    //=========================================
    // CREATION DU MENU DEROULANT
    //=========================================

    // CREATION DU BOUTON
    const $trigger = $(
      '<button type="button" class="custom-select__trigger" role="combobox" aria-haspopup="listbox" aria-expanded="false">',
    ).attr({
      "aria-controls": `${select.id}-options`,
      "aria-label": select.getAttribute("aria-label") || select.name,
    });

    // CREATION DE LA LISTE
    const $list = $(
      '<ul class="custom-select__options" role="listbox" hidden>',
    ).attr("id", `${select.id}-options`);

    // CREATION DES CHOIX
    const $items = $(
      Array.from(
        select.options,
        (opt, i) =>
          $('<li class="custom-select__option" role="option">')
            .attr({
              id: `${select.id}-option-${i}`,
              "aria-disabled": opt.disabled || null,
            })
            .text(opt.textContent.trim())[0],
      ),
    );

    // CREATION DU CONTENEUR
    const $wrapper = $("<div>")
      .addClass(`custom-select custom-select--${select.name}`)
      .append($trigger, $list.append($items));

    // SYNCHRONISER LE MENU DÉROULANT PERSONNALISÉ AVEC LE <SELECT> D’ORIGINE.
    const isOpen = () => !$list.prop("hidden");

    // la fonction qui suit met à jour le texte du bouton et indique quelle option est sélectionnée.
    function sync() {
      $trigger.text(select.selectedOptions[0]?.textContent.trim() ?? "");
      $items.each((i, li) =>
        li.setAttribute("aria-selected", i === select.selectedIndex),
      );
    }

    // MET EN ÉVIDENCE L’OPTION SITUÉE À LA POSITION I DANS LE MENU PERSONNALISÉ,
    function highlight(i) {
      if (!$items[i] || select.options[i].disabled) return;
      active = i;
      $items
        .removeClass("is-active")
        .eq(i)
        .addClass("is-active")[0]
        .scrollIntoView({ block: "nearest" });
      $trigger.attr("aria-activedescendant", $items[i].id);
    }

    // ouvre ou ferme la liste d’options et met à jour les informations d’accessibilité du bouton.
    function setOpen(open) {
      $list.prop("hidden", !open);
      $trigger.attr("aria-expanded", String(open));
      if (open) highlight(select.selectedIndex);
      else $trigger.removeAttr("aria-activedescendant");
    }

    // sert à choisir une option dans le menu personnalisé, puis à synchroniser ce choix avec le <select> d’origine.
    function choose(i) {
      const opt = select.options[i];
      if (!opt || opt.disabled) return;
      const changed = select.selectedIndex !== i;
      select.selectedIndex = i;
      sync();
      setOpen(false);
      $trigger.trigger("focus");
      if (changed) $(select).trigger("change"); // remonte jusqu'à $gallery
    }

    // ajoute les réactions du menu personnalisé au clic et au passage du pointeur

    $trigger.on("click", () => setOpen(!isOpen()));

    $list.on("pointerover click", ".custom-select__option", function (e) {
      const i = $items.index(this);
      if (e.type === "click") choose(i);
      else highlight(i);
    });

    // Prépare la navigation au clavier dans le menu personnalisé.

    $trigger.on("keydown", (e) => {
      const enabled = Array.from(select.options, (o, i) => i).filter(
        (i) => !select.options[i].disabled,
      );
      const n = enabled.length;
      if (!n) return;

      // détermine quelle option doit devenir active quand l’utilisateur appuie sur Flèche bas, Flèche haut, Début ou Fin.

      const pos = Math.max(enabled.indexOf(active), 0);
      const target = {
        ArrowDown: enabled[(pos + 1) % n],
        ArrowUp: enabled[(pos - 1 + n) % n],
        Home: enabled[0],
        End: enabled[n - 1],
      }[e.key];

      //complète la navigation au clavier du menu personnalisé : il réagit aux flèches, à Entrée, à Espace, à Échap et à Tab.

      if (target !== undefined) {
        e.preventDefault();
        if (!isOpen()) setOpen(true);
        highlight(target);
      } else if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        if (isOpen()) choose(active);
        else setOpen(true);
      } else if ((e.key === "Escape" || e.key === "Tab") && isOpen()) {
        setOpen(false);
      }
    });

    // Écoute deux événements pour garder le menu synchronisé et pouvoir le fermer.
    $wrapper.on("menu:close", () => setOpen(false));
    $(select).on("change", sync);

    // /termine la mise en place du menu personnalisé pour le <select> en cours de traitement.
    sync();
    select.hidden = true;
    $(select).after($wrapper);
  });

  // Ferme les menus personnalisés ouverts quand l’utilisateur clique ou appuie sur le pointeur à l’extérieur de ceux-ci.
  $(document).on("pointerdown", (e) => {
    $(".custom-select")
      .not($(e.target).closest(".custom-select"))
      .trigger("menu:close");
  });

  //=========================================
  // CHARGEMENT AJAX DE LA GALERIE
  //=========================================

  //prépare et envoie une requête AJAX à WordPress pour charger ou filtrer les photos de la galerie sans recharger la page.

  let request = null;

  function loadGallery(page = 1, append = false) {
    if (!$gallery.length || typeof motaphotoGallery === "undefined") return;

    if (request) request.abort(); // on ne garde que la requête la plus récente

    const val = (name) => $gallery.find(`[name='${name}']`).val();
    const $results = $gallery
      .find(".gallery-results")
      .attr("aria-busy", "true");
    const $button = $gallery.find(".gallery-load-more");
    if (append) $button.prop("disabled", true).text("Chargement…");

    // "current" permet à chaque callback de savoir s'il est encore d'actualité.
    const current = (request = $.post(
      motaphotoGallery.ajaxUrl,
      {
        action: "motaphoto_filter_gallery",
        nonce: motaphotoGallery.nonce,
        categorie: val("categorie"),
        format: val("format"),
        tri: val("tri"),
        paged: page,
        append: append ? 1 : 0,
      },
      null,
      "json",
    ));

    //traite la réponse de la requête AJAX, affiche les photos reçues et remet l’interface en état une fois la requête terminée.

    current
      .done((res) => {
        if (!res?.success)
          return console.error("Réponse de filtrage invalide.", res);
        const { grid, pagination } = res.data;

        if (append) {
          // On n'ajoute que les nouvelles photos, puis on remplace la pagination.
          const $tmp = $("<div>").html(grid);
          const $newGrid = $tmp.find(".photos-grid");
          $results
            .find(".photos-grid")
            .append($newGrid.length ? $newGrid.children() : $tmp.children());
          $results.find(".gallery-pagination").remove();
          $results.append(pagination);
        } else {
          $results.html(grid + pagination);
        }
      })
      .fail((xhr, status, error) => {
        if (status !== "abort")
          console.error("Échec du filtrage Ajax.", status, error);
      })
      .always(() => {
        if (request !== current) return; // une requête plus récente a pris le relais
        request = null;
        $results.attr("aria-busy", "false");
        $button.prop("disabled", false).text("Charger plus");
      });
  }

  // relie les menus de tri et de filtrage au chargement de la galerie.
  $gallery.on("change", ".sorting-form select", () => loadGallery(1));
  // Gere le clic sur le bouton « Charger plus » de la galerie.
  $gallery.on("click", ".gallery-load-more", function () {
    loadGallery(Number($(this).data("page")), true);
  });

  //=========================================
  // MODALE DE CONTACT
  //=========================================

  const $popup = $(".popup-overlay");
  $(document).on(
    "click",
    'a[href="#contact"], .btn-contact-infos',
    function (e) {
      e.preventDefault();
      $(".popup-contact [name='ref-photos']").val(
        $(this).data("reference") || "",
      );
      $popup.css("display", "flex");
    },
  );

  // Clic sur le fond uniquement (pas sur le contenu de la modale).
  $popup.on("click", (e) => {
    if (e.target === e.currentTarget) $popup.hide();
  });

  $(document).on("keydown", (e) => {
    if (e.key === "Escape") $popup.hide();
  });
});
