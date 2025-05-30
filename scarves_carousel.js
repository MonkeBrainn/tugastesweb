/**
 * scarves_carousel.js - Standalone product carousel/modal for scarf products
 * This file manages the creation and operation of a modal carousel
 * for browsing through all scarf products on the page.
 */

class ScarvesCarousel {
    constructor() {
        this.products = [];
        this.currentIndex = 0;
        this.modalElement = null;
        this.initialized = false;
    }

    /**
     * Initialize the carousel by finding all products on the page
     */
    init() {
        if (this.initialized) return;
        
        // Find all product items on the page
        const productItems = document.querySelectorAll('.product-card');
        if (productItems.length === 0) {
            console.error('No product items found on the page');
            return;
        }
        
        // Store information about each product
        productItems.forEach((item, index) => {
            try {
                const productImg = item.querySelector('.product-image img');
                const productLink = item.querySelector('.product-info h3 a');
                const productPrice = item.querySelector('.product-price');
                const productIdInput = item.querySelector('input[name="idproduk"]');
                
                if (!productImg || !productLink || !productPrice) {
                    console.error(`Product at index ${index} is missing required elements`);
                    return;
                }
                
                const productId = productIdInput ? productIdInput.value : `product-${index}`;
                
                this.products.push({
                    index: index,
                    id: productId,
                    name: productLink.textContent,
                    price: productPrice.textContent,
                    imageSrc: productImg.src,
                    detailUrl: productLink.href
                });
            } catch (e) {
                console.error(`Error processing product at index ${index}:`, e);
            }
        });
        
        console.log(`ScarvesCarousel: Loaded ${this.products.length} products`);
        
        // Create modal if it doesn't exist yet
        this.createModal();
        
        // Add click handlers to all product images
        this.addClickHandlers();
        
        this.initialized = true;
    }
    
    /**
     * Add click handlers to all product images
     */
    addClickHandlers() {
        const productItems = document.querySelectorAll('.product-card');
        
        productItems.forEach((item, index) => {
            const productImg = item.querySelector('.product-image img');
            if (!productImg) return;
            
            productImg.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                this.currentIndex = index;
                this.openModal(index);
            });
        });
    }
    
    /**
     * Create the modal element and append it to the document body
     */
    createModal() {
        // Create modal container
        const modal = document.createElement('div');
        modal.id = 'scarvesCarouselModal';
        modal.className = 'scarves-carousel-modal';
        
        // Create modal content
        modal.innerHTML = `
            <div class="scarves-carousel-content">
                <span class="scarves-carousel-close">&times;</span>
                
                <div class="scarves-carousel-image-container">
                    <img id="scarvesCarouselImage" src="" alt="Product Image">
                </div>
                
                <div class="scarves-carousel-nav">
                    <button class="scarves-carousel-prev" aria-label="Previous product">&lsaquo;</button>
                    <span class="scarves-carousel-counter">Product 1 of 1</span>
                    <button class="scarves-carousel-next" aria-label="Next product">&rsaquo;</button>
                </div>
                
                <div class="scarves-carousel-info">
                    <h3 id="scarvesCarouselName"></h3>
                    <p id="scarvesCarouselPrice"></p>
                    <a id="scarvesCarouselLink" href="#" class="scarves-carousel-details">View Details</a>
                </div>
            </div>
        `;
        
        // Append modal to body
        document.body.appendChild(modal);
        this.modalElement = modal;
        
        // Add event listeners
        const closeBtn = modal.querySelector('.scarves-carousel-close');
        const prevBtn = modal.querySelector('.scarves-carousel-prev');
        const nextBtn = modal.querySelector('.scarves-carousel-next');
        
        closeBtn.addEventListener('click', () => this.closeModal());
        prevBtn.addEventListener('click', () => this.navigate('prev'));
        nextBtn.addEventListener('click', () => this.navigate('next'));
        
        // Close modal when clicking outside content
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.closeModal();
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.isModalOpen()) return;
            
            switch(e.key) {
                case 'Escape':
                    this.closeModal();
                    break;
                case 'ArrowLeft':
                    this.navigate('prev');
                    break;
                case 'ArrowRight':
                    this.navigate('next');
                    break;
            }
        });
    }
    
    /**
     * Open the modal and display a specific product
     */
    openModal(index) {
        if (!this.modalElement || this.products.length === 0) return;
        
        // Update modal content with product info
        this.updateModalContent(index);
        
        // Show modal
        this.modalElement.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
    
    /**
     * Close the modal
     */
    closeModal() {
        if (!this.modalElement) return;
        
        this.modalElement.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    /**
     * Check if modal is currently open
     */
    isModalOpen() {
        return this.modalElement && this.modalElement.style.display === 'flex';
    }
    
    /**
     * Navigate to the previous or next product
     */
    navigate(direction) {
        if (this.products.length <= 1) return;
        
        if (direction === 'next') {
            this.currentIndex = (this.currentIndex + 1) % this.products.length;
        } else {
            this.currentIndex = (this.currentIndex - 1 + this.products.length) % this.products.length;
        }
        
        this.updateModalContent(this.currentIndex);
    }
    
    /**
     * Update the modal content with product information
     */
    updateModalContent(index) {
        if (index < 0 || index >= this.products.length) return;
        
        const product = this.products[index];
        const modal = this.modalElement;
        
        // Update image
        const imageElement = modal.querySelector('#scarvesCarouselImage');
        imageElement.src = product.imageSrc;
        imageElement.alt = product.name;
        
        // Update product info
        modal.querySelector('#scarvesCarouselName').textContent = product.name;
        modal.querySelector('#scarvesCarouselPrice').textContent = product.price;
        
        // Update link
        const detailLink = modal.querySelector('#scarvesCarouselLink');
        detailLink.href = product.detailUrl;
        
        // Update counter
        modal.querySelector('.scarves-carousel-counter').textContent = 
            `Product ${index + 1} of ${this.products.length}`;
    }
}

// Initialize carousel when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create and initialize the carousel
    window.scarvesCarousel = new ScarvesCarousel();
    window.scarvesCarousel.init();
    
    console.log('Scarves carousel initialized');
});