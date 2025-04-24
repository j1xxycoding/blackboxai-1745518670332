document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const nextButton = document.querySelector('.carousel-control.next');
    const prevButton = document.querySelector('.carousel-control.prev');
    const slideWidth = slides[0].getBoundingClientRect().width + 32; // including margin

    let currentIndex = 0;

    const moveToSlide = (index) => {
        track.style.transform = 'translateX(-' + (slideWidth * index) + 'px)';
        currentIndex = index;
        updateButtons();
    };

    const updateButtons = () => {
        if (currentIndex === 0) {
            prevButton.disabled = true;
            prevButton.style.opacity = '0.3';
        } else {
            prevButton.disabled = false;
            prevButton.style.opacity = '1';
        }

        if (currentIndex >= slides.length - 1) {
            nextButton.disabled = true;
            nextButton.style.opacity = '0.3';
        } else {
            nextButton.disabled = false;
            nextButton.style.opacity = '1';
        }
    };

    prevButton.addEventListener('click', () => {
        if (currentIndex > 0) {
            moveToSlide(currentIndex - 1);
        }
    });

    nextButton.addEventListener('click', () => {
        if (currentIndex < slides.length - 1) {
            moveToSlide(currentIndex + 1);
        }
    });

    updateButtons();
});
