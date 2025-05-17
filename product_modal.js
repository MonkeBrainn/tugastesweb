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
            
            // Identify product type from product name for better image mapping
            let productType = identifyProductType(productName.toLowerCase());
            
            // Clear any previously generated images if we have specific alternatives
            // to avoid duplicates with approach 1
            if (hasGeneratedImages && getSpecificProductImages(productName, productType).length > 0) {
                window.productImages = [currentImageSrc];
            }
            
            // Add specific alternatives based on product name or detected product type
            const specificImages = getSpecificProductImages(productName, productType);
            if (specificImages.length > 0) {
                window.productImages = window.productImages.concat(specificImages);
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
                
                // Add placeholder images with product type-specific labels
                window.productImages.push(generatePlaceholderImage(productType, 2));
                window.productImages.push(generatePlaceholderImage(productType, 3));
                
                // Add additional angle-specific views based on product type
                if (productType) {
                    const viewTypes = getProductViewTypes(productType);
                    for (let i = 0; i < viewTypes.length && window.productImages.length < 6; i++) {
                        window.productImages.push(generatePlaceholderImage(productType, null, viewTypes[i]));
                    }
                }
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
    
    // Helper function to identify product type from product name
    function identifyProductType(productName) {
        const productTypes = {
            'dress': ['dress', 'gown', 'frock'],
            'jacket': ['jacket', 'blazer', 'coat', 'bomber'],
            'cardigan': ['cardigan', 'sweater', 'knit'],
            'pants': ['pants', 'trousers', 'jeans', 'slacks', 'leggings'],
            'skirt': ['skirt'],
            'shirt': ['shirt', 'blouse', 'top'],
            'shoes': ['shoes', 'sneakers', 'boots', 'sandals', 'heels'],
            'accessory': ['bag', 'purse', 'scarf', 'hat', 'belt', 'jewelry', 'necklace', 'bracelet']
        };
        
        for (const type in productTypes) {
            for (const keyword of productTypes[type]) {
                if (productName.includes(keyword)) {
                    return type;
                }
            }
        }
        
        return 'general'; // Default type if no match
    }
    
    // Helper function to get specific images for known products
    function getSpecificProductImages(productName, productType) {
        const specificImages = [];
        const productNameLower = productName.toLowerCase();
        
        // Specific product mappings
        if (productNameLower.includes("underwater barocco silk mini shirt dress")) {
            specificImages.push("images/dress1_front.png");
            specificImages.push("images/dress1_back.png");
        } 
        else if (productNameLower.includes("leather biker jacket")) {
            specificImages.push("images/jacket2.png");
            specificImages.push("images/jacket3.png");
            specificImages.push("images/jacket_back.png");
        }
        else if (productNameLower.includes("Crew neck marinière sweater in cashmere")) {
            specificImages.push("images/cardigan1_front.png");
        }
        else if (productNameLower.includes("slim fit jeans")) {
            specificImages.push("images/jeans_back.png");
            specificImages.push("images/jeans_detail.png");
        }
        else if (productNameLower.includes("silk blouse")) {
            specificImages.push("images/blouse_back.png");
            specificImages.push("images/blouse_detail.png");
        }
        
        // Generic product type mappings as fallback
        if (specificImages.length === 0 && productType !== 'general') {
            // Default image patterns for common product types
            const defaultImages = {
                'dress': [
                    `images/${productType}_front.png`,
                    `images/${productType}_back.png`,
                    `images/${productType}_side.png`
                ],
                'jacket': [
                    `images/${productType}_front.png`,
                    `images/${productType}_back.png`,
                    `images/${productType}_side.png`,
                    `images/${productType}_detail.png`
                ],
                'cardigan': [
                    `images/${productType}_front.png`,
                ],
                'pants': [
                    `images/${productType}_front.png`,
                    `images/${productType}_back.png`,
                    `images/${productType}_detail.png`
                ],
                'skirt': [
                    `images/${productType}_front.png`,
                    `images/${productType}_back.png`,
                    `images/${productType}_side.png`
                ],
                'shirt': [
                    `images/${productType}_front.png`,
                    `images/${productType}_back.png`,
                    `images/${productType}_detail.png`
                ]
            };
            
            if (defaultImages[productType]) {
                specificImages.push(...defaultImages[productType]);
            }
        }
        
        return specificImages;
    }
    
    // Helper function to generate placeholder image with product type info
    function generatePlaceholderImage(productType, viewNumber, viewType) {
        // Default to view number if view type not specified
        const viewLabel = viewType || (viewNumber ? `View ${viewNumber}` : "Alternative View");
        const productLabel = productType !== 'general' ? productType.charAt(0).toUpperCase() + productType.slice(1) : "Product";
        
        return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect width='300' height='300' fill='%23f0f0f0'/%3E%3Ctext x='50%25' y='45%25' font-family='Arial' font-size='18' text-anchor='middle' dominant-baseline='middle' fill='%23999'%3E${productLabel}%3C/text%3E%3Ctext x='50%25' y='55%25' font-family='Arial' font-size='18' text-anchor='middle' dominant-baseline='middle' fill='%23999'%3E${viewLabel}%3C/text%3E%3C/svg%3E`;
    }
    
    // Helper function to get product-specific view types
    function getProductViewTypes(productType) {
        // Different view types based on product category
        const viewTypes = {
            'dress': ['Front View', 'Back View', 'Side View', 'Detail View'],
            'jacket': ['Front View', 'Back View', 'Side View', 'Collar Detail', 'Sleeve Detail'],
            'cardigan': ['Front View', 'Back View', 'Fabric Detail', 'Button Detail'],
            'pants': ['Front View', 'Back View', 'Side View', 'Waistband Detail'],
            'skirt': ['Front View', 'Back View', 'Side View', 'Detail View'],
            'shirt': ['Front View', 'Back View', 'Collar Detail', 'Fabric Detail'],
            'shoes': ['Top View', 'Side View', 'Back View', 'Sole View'],
            'accessory': ['Front View', 'Inside View', 'Detail View', 'Styling Example']
        };
        
        return viewTypes[productType] || ['Alternate View', 'Detail View'];
    }
});