// Product image modal functionality with carousel
document.addEventListener('DOMContentLoaded', function() {
    // Store product images array as a global variable
    window.productImages = [];
    window.currentImageIndex = 0;
    
    // Function to create product modal with navigation buttons
    function createProductModal() {
        const modal = document.createElement('div');
        modal.id = 'productModal';
        modal.className = 'product-modal';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-modal';
        closeBtn.innerHTML = '&times;';
        
        // Add left navigation button
        const leftNavBtn = document.createElement('div');
        leftNavBtn.className = 'nav-btn prev-btn';
        leftNavBtn.innerHTML = '&#10094;'; // Left arrow
        
        // Add right navigation button
        const rightNavBtn = document.createElement('div');
        rightNavBtn.className = 'nav-btn next-btn';
        rightNavBtn.innerHTML = '&#10095;'; // Right arrow
        
        const modalImgContainer = document.createElement('div');
        modalImgContainer.className = 'modal-image-container';
        
        const modalImg = document.createElement('img');
        modalImg.id = 'modalImage';
        
        const productInfo = document.createElement('div');
        productInfo.className = 'modal-product-info';
        
        modalImgContainer.appendChild(modalImg);
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(leftNavBtn);
        modalContent.appendChild(modalImgContainer);
        modalContent.appendChild(rightNavBtn);
        modalContent.appendChild(productInfo);
        modal.appendChild(modalContent);
        
        document.body.appendChild(modal);
        
        // Close modal when clicking the X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside the image
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
        
        // Navigation button functionality
        leftNavBtn.addEventListener('click', function() {
            navigateImages('prev');
        });
        
        rightNavBtn.addEventListener('click', function() {
            navigateImages('next');
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (modal.style.display === 'block') {
                if (event.key === 'ArrowLeft') {
                    navigateImages('prev');
                } else if (event.key === 'ArrowRight') {
                    navigateImages('next');
                } else if (event.key === 'Escape') {
                    modal.style.display = 'none';
                }
            }
        });
    }
    
    // Function to navigate between images
    function navigateImages(direction) {
        console.log("Navigating: " + direction);
        console.log("Current images array:", window.productImages);
        console.log("Current index before:", window.currentImageIndex);
        
        const modalImg = document.getElementById('modalImage');
        
        if (window.productImages && window.productImages.length > 1) {
            if (direction === 'next') {
                window.currentImageIndex = (window.currentImageIndex + 1) % window.productImages.length;
            } else {
                window.currentImageIndex = (window.currentImageIndex - 1 + window.productImages.length) % window.productImages.length;
            }
            
            console.log("New index:", window.currentImageIndex);
            console.log("New image source:", window.productImages[window.currentImageIndex]);
            
            // Update the image source
            modalImg.src = window.productImages[window.currentImageIndex];
        } else {
            console.log("Not enough images to navigate");
        }
    }
    
    // Expose the navigateImages function globally
    window.navigateImages = navigateImages;
    
    // Add click handlers to all product images
    const productImgElements = document.querySelectorAll('.product-image-img');
    
    productImgElements.forEach(function(img) {
        img.addEventListener('click', function(e) {
            // Prevent default link behavior if inside an <a> tag
            e.preventDefault();
            
            // Get parent container
            const productItem = this.closest('.product-item');
            
            // Find product details (these are hidden elements we added)
            const productName = productItem.querySelector('.product-name').textContent;
            const productPrice = productItem.querySelector('.product-price').textContent;
            
            // Create modal if it doesn't exist
            if (!document.getElementById('productModal')) {
                createProductModal();
            }
            
            // Set modal content
            const modal = document.getElementById('productModal');
            const modalImg = document.getElementById('modalImage');
            const productInfo = document.querySelector('.modal-product-info');
            
            // Reset current image index
            window.currentImageIndex = 0;
            
            // Use the current image as the base
            let currentImageSrc = this.src;
            
            // Create array of images for this product
            // This approach uses a predictable naming pattern to find related images
            window.productImages = [];
            
            // First add the current image
            window.productImages.push(currentImageSrc);
            
            // Check if we need to generate additional images
            // Get the base name without extension
            let basePath = '';
            let baseName = '';
            let extension = '';
            
            // Extract the base path and file name
            if (currentImageSrc.includes('/')) {
                basePath = currentImageSrc.substring(0, currentImageSrc.lastIndexOf('/') + 1);
                let fileName = currentImageSrc.substring(currentImageSrc.lastIndexOf('/') + 1);
                
                // Extract extension
                if (fileName.includes('.')) {
                    extension = fileName.substring(fileName.lastIndexOf('.'));
                    baseName = fileName.substring(0, fileName.lastIndexOf('.'));
                } else {
                    baseName = fileName;
                }
            }
            
            console.log("Base path: " + basePath);
            console.log("Base name: " + baseName);
            console.log("Extension: " + extension);
            
            // *** APPROACH 1: Try to generate related images based on common patterns ***
            
            // If the image name contains a number, try to create variants
            let hasGeneratedImages = false;
            
            // Check if the filename has a number pattern like "dress1" or "dress_1"
            const numberMatch = baseName.match(/(\D+)[-_]?(\d+)/);
            if (numberMatch) {
                const prefix = numberMatch[1];
                const num = parseInt(numberMatch[2]);
                
                // Generate a few variants
                for (let i = 1; i <= 5; i++) {
                    if (i !== num) { // Skip the current image
                        const newImage = basePath + prefix + i + extension;
                        window.productImages.push(newImage);
                        hasGeneratedImages = true;
                    }
                }
            }
            
            // *** APPROACH 2: Add hardcoded alternatives for specific products ***
            
            // If we have a specific product, add known alternatives
            // more reliable approach for production
            if (productName.includes("Underwater Barocco Silk Mini Shirt Dress")) {
                // Clear any previously generated images to avoid duplicates
                if (hasGeneratedImages) {
                    window.productImages = [currentImageSrc];
                }
                
                // Add specific alternative images for this product
                // You would replace these with actual paths to your images
                window.productImages.push("images/dress2.png");
                window.productImages.push("images/dress3.png");
                
                hasGeneratedImages = true;
            }

            // *** APPROACH 3: Fallback to dummy images if nothing else works ***
            
            // If we couldn't generate any images, add some dummy alternatives
            if (!hasGeneratedImages || window.productImages.length < 2) {
                // Add dummy images as a last resort
                if (window.productImages.length === 1) {
                    // Only keep the first image (the one clicked)
                    const firstImage = window.productImages[0];
                    window.productImages = [firstImage];
                }
                
                // Add placeholder images
                window.productImages.push("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect width='300' height='300' fill='%23f0f0f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial' font-size='18' text-anchor='middle' dominant-baseline='middle' fill='%23999'%3EProduct View 2%3C/text%3E%3C/svg%3E");
                window.productImages.push("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect width='300' height='300' fill='%23f0f0f0'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial' font-size='18' text-anchor='middle' dominant-baseline='middle' fill='%23999'%3EProduct View 3%3C/text%3E%3C/svg%3E");
            }
            
            // Log the final image array
            console.log("Final product images array:", window.productImages);
            
            // Set initial image source and product info
            modalImg.src = window.productImages[window.currentImageIndex];
            productInfo.innerHTML = `<h3>${productName}</h3><p class="modal-price">${productPrice}</p>`;
            
            // Show modal
            modal.style.display = 'block';
        });
    });
});