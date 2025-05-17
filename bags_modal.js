// Product image modal functionality with carousel for bags collection
document.addEventListener('DOMContentLoaded', function() {
    // Store product images array as a global variable
    window.productImages = [];
    window.currentImageIndex = 0;
    
    // Add click handlers to all product images in the bags collection
    const productImgElements = document.querySelectorAll('.product-image-img');
    
    productImgElements.forEach(function(img) {
        img.addEventListener('click', function(e) {
            // Prevent default link behavior if inside an <a> tag
            e.preventDefault();
            e.stopPropagation();
            
            // Get parent container
            const productItem = this.closest('.product-item');
            
            // Find product details (these are hidden elements we added)
            const productName = productItem.querySelector('.product-name').textContent;
            const productPrice = productItem.querySelector('.product-price').textContent;
            const productId = productItem.querySelector('input[name="idproduk"]').value;
            
            // Create modal if it doesn't exist
            if (!document.getElementById('productModal')) {
                createProductModal();
            }
            
            // Reset current image index
            window.currentImageIndex = 0;
            
            // Use the current image as the base
            let currentImageSrc = this.src;
            
            // Create array of images for this product
            window.productImages = [];
            
            // First add the current image
            window.productImages.push(currentImageSrc);
            
            // Extract image base path and filename
            const basePath = currentImageSrc.substring(0, currentImageSrc.lastIndexOf('/') + 1);
            const fileName = currentImageSrc.substring(currentImageSrc.lastIndexOf('/') + 1);
            const extension = fileName.substring(fileName.lastIndexOf('.'));
            const baseName = fileName.substring(0, fileName.lastIndexOf('.'));
            
            // Generate additional bag images based on common patterns
            const bagTypes = ['front', 'back', 'side', 'inside', 'detail', 'styling'];
            
            // First try: Check if filename is like "bag1" or "tote_black" and generate variations
            const basePatternMatch = baseName.match(/^([a-zA-Z_]+)(\d*)(_[a-zA-Z]+)?$/);
            
            if (basePatternMatch) {
                const prefix = basePatternMatch[1];
                const number = basePatternMatch[2] || '';
                const suffix = basePatternMatch[3] || '';
                
                // Add default bag views
                for (let i = 0; i < bagTypes.length; i++) {
                    const newImageName = `${prefix}${number}_${bagTypes[i]}${suffix}${extension}`;
                    const newImage = basePath + newImageName;
                    
                    // Only add if it's not the current image
                    if (newImage !== currentImageSrc) {
                        window.productImages.push(newImage);
                    }
                }
                
                // Add color variations if this is a base product
                if (!baseName.includes('_')) {
                    const colors = ['black', 'brown', 'beige', 'red', 'blue'];
                    for (let i = 0; i < colors.length; i++) {
                        const colorVariant = `${prefix}${number}_${colors[i]}${extension}`;
                        window.productImages.push(basePath + colorVariant);
                    }
                }
            }
            
            // If the product name contains "bag" keywords, add some bag-specific views
            const bagKeywords = ['bag', 'tote', 'purse', 'pochette', 'boston', 'shoulder', 'puzzle'];
            const nameLower = productName.toLowerCase();
            
            let hasBagKeyword = false;
            for (const keyword of bagKeywords) {
                if (nameLower.includes(keyword)) {
                    hasBagKeyword = true;
                    break;
                }
            }
            
            if (hasBagKeyword) {
                // Add common bag view patterns
                const viewPositions = ['front', 'back', 'side', 'inside', 'closeup', 'strap'];
                
                for (const view of viewPositions) {
                    // Extract product identifier (typically starts with p followed by numbers)
                    const productIdMatch = baseName.match(/^(p\d+)/);
                    if (productIdMatch) {
                        const idPrefix = productIdMatch[1];
                        const viewImage = `${basePath}${idPrefix}_${view}${extension}`;
                        
                        // Don't add duplicates
                        if (!window.productImages.includes(viewImage)) {
                            window.productImages.push(viewImage);
                        }
                    }
                }
            }
            
            // If we couldn't generate many images, add placeholder SVGs
            if (window.productImages.length < 3) {
                // Keep only the first real image
                const firstImage = window.productImages[0];
                window.productImages = [firstImage];
                
                // Add placeholder SVGs for different bag views
                for (let i = 0; i < bagTypes.length; i++) {
                    const placeholderSrc = generateBagPlaceholder(bagTypes[i], productName);
                    window.productImages.push(placeholderSrc);
                }
            }
            
            console.log("Product images for modal:", window.productImages);
            
            // Set modal content
            const modal = document.getElementById('productModal');
            const modalImg = document.getElementById('modalImage');
            const productInfo = document.querySelector('.modal-product-info');
            
            // Set initial image and product info
            modalImg.src = window.productImages[window.currentImageIndex];
            productInfo.innerHTML = `
                <h3>${productName}</h3>
                <p class="modal-price">${productPrice}</p>
                <a href="detail_produk.php?id=${productId}" class="view-details-btn">View Details</a>
            `;
            
            // Show modal
            modal.style.display = 'block';
        });
    });
    
    // Helper function to generate bag placeholder images
    function generateBagPlaceholder(viewType, productName) {
        // Format product name for display
        const displayName = productName.length > 25 ? productName.substring(0, 22) + '...' : productName;
        
        // Format view type for display
        const formattedViewType = viewType.charAt(0).toUpperCase() + viewType.slice(1) + ' View';
        
        return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='800' viewBox='0 0 800 800'%3E%3Crect width='800' height='800' fill='%23f8f8f8'/%3E%3Crect x='200' y='150' width='400' height='500' rx='10' fill='%23e0e0e0'/%3E%3Cpath d='M350,150 L350,120 Q350,80 400,80 Q450,80 450,120 L450,150' stroke='%23d0d0d0' stroke-width='10' fill='none'/%3E%3Ctext x='400' y='400' font-family='Arial' font-size='24' text-anchor='middle' fill='%23808080'%3E${encodeURIComponent(displayName)}%3C/text%3E%3Ctext x='400' y='440' font-family='Arial' font-size='20' text-anchor='middle' fill='%23a0a0a0'%3E${formattedViewType}%3C/text%3E%3C/svg%3E`;
    }
    
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
        modalImg.onerror = function() {
            // If image fails to load, replace with a fallback
            this.src = generateBagPlaceholder('Alternative', 'Image Not Available');
        };
        
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
        const modalImg = document.getElementById('modalImage');
        
        if (window.productImages && window.productImages.length > 1) {
            if (direction === 'next') {
                window.currentImageIndex = (window.currentImageIndex + 1) % window.productImages.length;
            } else {
                window.currentImageIndex = (window.currentImageIndex - 1 + window.productImages.length) % window.productImages.length;
            }
            
            console.log(`Navigating ${direction} to image ${window.currentImageIndex + 1}/${window.productImages.length}`);
            
            // Update the image source
            modalImg.src = window.productImages[window.currentImageIndex];
        }
    }
    
    // Expose the navigateImages function globally
    window.navigateImages = navigateImages;
});