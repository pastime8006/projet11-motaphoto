// Crée la fenêtre de visualisation et l'ajoute à la page.
const lightbox = document.createElement('div')
lightbox.id = 'lightbox'
lightbox.setAttribute('role', 'dialog')
lightbox.setAttribute('aria-modal', 'true')
lightbox.setAttribute('aria-label', 'Galerie photo')
document.body.appendChild(lightbox)

// Crée le bouton qui ferme la fenêtre.
const closeButton = document.createElement('button')
closeButton.type = 'button'
closeButton.className = 'lightbox-close'
closeButton.setAttribute('aria-label', 'Fermer la lightbox')
closeButton.addEventListener('click', () => lightbox.classList.remove('active'))

// Prépare les éléments qui seront réutilisés pour chaque photo ouverte.
const content = document.createElement('div')
content.className = 'lightbox-content'

const imageWrapper = document.createElement('div')
imageWrapper.className = 'lightbox-image-wrapper'

const previousButton = document.createElement('button')
previousButton.type = 'button'
previousButton.className = 'lightbox-arrow lightbox-arrow--previous'
previousButton.setAttribute('aria-label', 'Photo précédente')

const enlargedImage = document.createElement('img')

const nextButton = document.createElement('button')
nextButton.type = 'button'
nextButton.className = 'lightbox-arrow lightbox-arrow--next'
nextButton.setAttribute('aria-label', 'Photo suivante')

imageWrapper.append(previousButton, enlargedImage, nextButton)

const metadata = document.createElement('div')
metadata.className = 'lightbox-metadata'

const referenceLabel = document.createElement('p')
referenceLabel.className = 'lightbox-reference'

const categoryLabel = document.createElement('p')
categoryLabel.className = 'lightbox-category'

metadata.append(referenceLabel, categoryLabel)
content.append(imageWrapper, metadata)
lightbox.append(content, closeButton)

// Mémorise les photos de la galerie affichée et l'index de la photo courante.
let imagesGalerie = []
let indexActuel = 0

// Met à jour l'image agrandie, sa catégorie et sa référence.
function afficherPhoto(index) {
    if (imagesGalerie.length === 0) return

    if (index < 0) {
        indexActuel = imagesGalerie.length - 1
    } else if (index >= imagesGalerie.length) {
        indexActuel = 0
    } else {
        indexActuel = index
    }

    const image = imagesGalerie[indexActuel]

    // Utilise la version haute résolution si elle est définie, sinon l'image d'origine.
    enlargedImage.src = image.dataset.largeSrc || image.src
    // Conserve la description de l'image pour l'accessibilité.
    enlargedImage.alt = image.alt
    // Affiche la catégorie et la référence si elles sont renseignées.
    categoryLabel.textContent = image.dataset.photoCategory || ''
    referenceLabel.textContent = image.dataset.photoReference
        ? `RÉFÉRENCE : ${image.dataset.photoReference}`
        : ''
}

// Charge la galerie qui contient la photo cliquée, puis ouvre la lightbox.
function ouvrirLightbox(imageCliquee) {
    const galerie = imageCliquee.closest('.photos-page-content, .photos-parent')
    if (!galerie) return

    // Récupère uniquement les images de la grille contenant la photo cliquée.
    imagesGalerie = Array.from(
        galerie.querySelectorAll('.photos-grid .img-lightbox')
    )
    indexActuel = imagesGalerie.indexOf(imageCliquee)

    if (indexActuel === -1) return

    afficherPhoto(indexActuel)
    lightbox.classList.add('active')
}

// Les boutons changent l'index courant d'une position.
previousButton.addEventListener('click', () => afficherPhoto(indexActuel - 1))
nextButton.addEventListener('click', () => afficherPhoto(indexActuel + 1))

// Au clic sur la commande d'une carte, ouvre l'image de cette même carte.
document.querySelectorAll(
    '.photos-page-content .container-ellipse, .photos-parent .container-ellipse'
).forEach(zone => {
    zone.addEventListener('click', () => {
        const carte = zone.closest('.photo-image')
        if (!carte) return

        const image = carte.querySelector('.img-lightbox')

        if (image) ouvrirLightbox(image)
    })
})

 //Ferme la fenêtre uniquement si le clic touche le fond extérieur.
 lightbox.addEventListener('click', event => {
  if (event.target === lightbox) lightbox.classList.remove('active')
});

// Permet de fermer ou de naviguer au clavier tant que la lightbox est ouverte.
document.addEventListener('keydown', event => {
    if (!lightbox.classList.contains('active')) return

    if (event.key === 'Escape') lightbox.classList.remove('active')
    if (event.key === 'ArrowLeft') afficherPhoto(indexActuel - 1)
    if (event.key === 'ArrowRight') afficherPhoto(indexActuel + 1)
})
