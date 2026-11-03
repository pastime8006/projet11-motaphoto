const lightbox = document.createElement('div')
lightbox.id = 'lightbox'
lightbox.setAttribute('role', 'dialog')
lightbox.setAttribute('aria-modal', 'true')
lightbox.setAttribute('aria-label', 'Galerie photo')
document.body.appendChild(lightbox)

const closeButton = document.createElement('button')
closeButton.type = 'button'
closeButton.className = 'lightbox-close'
closeButton.setAttribute('aria-label', 'Fermer la lightbox')
closeButton.addEventListener('click', () => {
    lightbox.classList.remove('active')
})

let galleryImages = []
let currentImageIndex = 0
let enlargedImage
let categoryLabel
let referenceLabel

const showImage = index => {
    currentImageIndex = (index + galleryImages.length) % galleryImages.length
    const image = galleryImages[currentImageIndex]
    enlargedImage.src = image.dataset.largeSrc || image.src
    enlargedImage.alt = image.alt
    categoryLabel.textContent = image.dataset.photoCategory || ''
    referenceLabel.textContent = image.dataset.photoReference
        ? `RÉFÉRENCE : ${image.dataset.photoReference}`
        : ''
}

const previousButton = document.createElement('button')
previousButton.type = 'button'
previousButton.className = 'lightbox-arrow lightbox-arrow--previous'
previousButton.setAttribute('aria-label', 'Photo précédente')
previousButton.addEventListener('click', () => showImage(currentImageIndex - 1))

const nextButton = document.createElement('button')
nextButton.type = 'button'
nextButton.className = 'lightbox-arrow lightbox-arrow--next'
nextButton.setAttribute('aria-label', 'Photo suivante')
nextButton.addEventListener('click', () => showImage(currentImageIndex + 1))

const openLightbox = image => {
    const gallery = image.closest('.photos-page-content')
    galleryImages = Array.from(gallery?.querySelectorAll('.photos-grid .img-lightbox') || [])
    currentImageIndex = galleryImages.indexOf(image)

    if (currentImageIndex === -1) return

    const imageWrapper = document.createElement('div')
    imageWrapper.className = 'lightbox-image-wrapper'

    const content = document.createElement('div')
    content.className = 'lightbox-content'

    enlargedImage = document.createElement('img')
    categoryLabel = document.createElement('p')
    categoryLabel.className = 'lightbox-category'
    referenceLabel = document.createElement('p')
    referenceLabel.className = 'lightbox-reference'
    showImage(currentImageIndex)

    imageWrapper.append(previousButton, enlargedImage, nextButton)

    const metadata = document.createElement('div')
    metadata.className = 'lightbox-metadata'
    metadata.append(referenceLabel, categoryLabel)
    content.append(imageWrapper, metadata)

    lightbox.replaceChildren(content, closeButton)
    lightbox.classList.add('active')
}

document.querySelectorAll('.photos-page-content .container-ellipse').forEach(container => {
    container.addEventListener('click', () => {
        const image = container.closest('.photo-image')?.querySelector('.img-lightbox')
        if (image) openLightbox(image)
    })
})

lightbox.addEventListener('click', event => {
    if (event.target === lightbox) lightbox.classList.remove('active')
})

document.addEventListener('keydown', event => {
    if (!lightbox.classList.contains('active')) return

    if (event.key === 'Escape') lightbox.classList.remove('active')
    if (event.key === 'ArrowLeft') showImage(currentImageIndex - 1)
    if (event.key === 'ArrowRight') showImage(currentImageIndex + 1)
})
