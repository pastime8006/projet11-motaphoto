// 1. Création de la fenêtre
const lightbox = document.createElement('div')
lightbox.id = 'lightbox'
lightbox.setAttribute('role', 'dialog')
lightbox.setAttribute('aria-modal', 'true')
lightbox.setAttribute('aria-label', 'Galerie photo')
document.body.appendChild(lightbox)

// 2. Bouton de fermeture
const closeButton = document.createElement('button')
closeButton.type = 'button'
closeButton.className = 'lightbox-close'
closeButton.setAttribute('aria-label', 'Fermer la lightbox')
closeButton.addEventListener('click', () => lightbox.classList.remove('active'))

// 3. Structure du contenu
const content = document.createElement('div')
content.className = 'lightbox-content'

const imageWrapper = document.createElement('div')
imageWrapper.className = 'lightbox-image-wrapper'

const previousButton = document.createElement('button')
previousButton.type = 'button'
previousButton.className = 'lightbox-arrow lightbox-arrow--previous'
previousButton.setAttribute('aria-label', 'Photo précédente')
const previousIcon = document.createElement('i')
previousIcon.className = 'fa-solid fa-chevron-left'
previousIcon.setAttribute('aria-hidden', 'true')
previousButton.append(previousIcon)

const enlargedImage = document.createElement('img')

// Création du bouton et de l'icône pour afficher la photo suivante.
const nextButton = document.createElement('button')
nextButton.type = 'button'
nextButton.className = 'lightbox-arrow lightbox-arrow--next'
nextButton.setAttribute('aria-label', 'Photo suivante')
const nextIcon = document.createElement('i')
nextIcon.className = 'fa-solid fa-chevron-right'
nextIcon.setAttribute('aria-hidden', 'true')
nextButton.append(nextIcon)

imageWrapper.append(previousButton, enlargedImage, nextButton)

// 4. Informations de la photo et assemblage
const metadata = document.createElement('div')
metadata.className = 'lightbox-metadata'

const referenceLabel = document.createElement('p')
referenceLabel.className = 'lightbox-reference'

const categoryLabel = document.createElement('p')
categoryLabel.className = 'lightbox-category'

metadata.append(referenceLabel, categoryLabel)
content.append(imageWrapper, metadata)
lightbox.append(content, closeButton)

// 5. État de la galerie
let imagesGalerie = []
let indexActuel = 0

// 6. Affichage et navigation entre les photos
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

// 7. Ouverture de la lightbox
function ouvrirLightbox(imageCliquee) {
    const galerie = imageCliquee.closest('.photos-page-content, .photos-parent')
    if (!galerie) return

    // Récupère uniquement les images de la grille de cette galerie.
    imagesGalerie = Array.from(
        galerie.querySelectorAll('.photos-grid .img-lightbox')
    )
    indexActuel = imagesGalerie.indexOf(imageCliquee)

    if (indexActuel === -1) return

    afficherPhoto(indexActuel)
    lightbox.classList.add('active')
}

// 8. Boutons précédent et suivant
previousButton.addEventListener('click', () => afficherPhoto(indexActuel - 1))
nextButton.addEventListener('click', () => afficherPhoto(indexActuel + 1))

// 9. Clic sur une photo de la page
// Écoute le document plutôt que les éléments actuels afin de prendre en compte
// les nouvelles cartes ajoutées dynamiquement par les filtres et l'AJAX.
document.addEventListener('click', event => {
    // Recherche la zone cliquée, y compris si le clic touche une image enfant.
    const zone = event.target.closest(
        '.photos-page-content .container-ellipse, .photos-parent .container-ellipse'
    )

    // Ignore les clics qui ne concernent pas une zone de lightbox.
    if (!zone) return

    // Retrouve la carte et l'image associées à la zone cliquée.
    const carte = zone.closest('.photo-image')
    const image = carte?.querySelector('.img-lightbox')

    if (image) ouvrirLightbox(image)
})

// 10. Commandes au clavier
document.addEventListener('keydown', event => {
    if (!lightbox.classList.contains('active')) return

    if (event.key === 'Escape') lightbox.classList.remove('active')
    if (event.key === 'ArrowLeft') afficherPhoto(indexActuel - 1)
    if (event.key === 'ArrowRight') afficherPhoto(indexActuel + 1)
})
