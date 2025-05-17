// Product image modal functionality with carousel for bags collection
document.addEventListener('DOMContentLoaded', function() {
    // Store all products information globally
    window.allProducts = [];
    window.currentProductIndex = 0;
    
    // Add click handlers to all product images in the bags collection
    const productItems = document.querySelectorAll('.product-item');
    
    // First, collect all product information
    productItems.forEach(function(item, index) {
        const productImg = item.querySelector('.product-image-img');
        const productName = item.querySelector('.product-name').textContent;
        const productPrice = item.querySelector('.product-price').textContent;
        const productId = item.querySelector('input[name="idproduk"]').value;
        
        // Store product info
        window.allProducts.push({
            index: index,
            imageSrc: productImg.src,
            name: productName,
            price: productPrice,
            id: productId
        });
    });
    
    console.log("Loaded " + window.allProducts.length + " bag products");
    
    // Then add click handlers
    productItems.forEach(function(item, index) {
        const productImg = item.querySelector('.product-image-img');
        
        productImg.addEventListener('click', function(e) {
            // Prevent default link behavior if inside an <a> tag
            e.preventDefault();
            e.stopPropagation();
            
            // Create modal if it doesn't exist
            if (!document.getElementById('productModal')) {
                createProductModal();
            }
            
            // Set current product index
            window.currentProductIndex = index;
            
            // Display the current product
            displayProductInModal(window.currentProductIndex);
            
            // Show modal
            document.getElementById('productModal').style.display = 'block';
        });
    });
    
    // Function to display the current product in the modal
    function displayProductInModal(index) {
        if (index >= 0 && index < window.allProducts.length) {
            const product = window.allProducts[index];
            const modal = document.getElementById('productModal');
            const modalImg = document.getElementById('modalImage');
            const productInfo = document.querySelector('.modal-product-info');
            
            // Set image and product info
            modalImg.src = product.imageSrc;
            productInfo.innerHTML = `
                <h3>${product.name}</h3>
                <p class="modal-price">${product.price}</p>
                <a href="detail_produk.php?id=${product.id}" class="view-details-btn">View Details</a>
            `;
            
            // Update navigation info
            const navInfo = document.getElementById('modalNavInfo');
            if (navInfo) {
                navInfo.textContent = `Product ${index + 1} of ${window.allProducts.length}`;
            }
        }
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
        
        // Add navigation info display
        const navInfo = document.createElement('div');
        navInfo.id = 'modalNavInfo';
        navInfo.className = 'modal-nav-info';
        
        modalImgContainer.appendChild(modalImg);
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(leftNavBtn);
        modalContent.appendChild(modalImgContainer);
        modalContent.appendChild(rightNavBtn);
        modalContent.appendChild(productInfo);
        modalContent.appendChild(navInfo);
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
            navigateProducts('prev');
        });
        
        rightNavBtn.addEventListener('click', function() {
            navigateProducts('next');
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (modal.style.display === 'block') {
                if (event.key === 'ArrowLeft') {
                    navigateProducts('prev');
                } else if (event.key === 'ArrowRight') {
                    navigateProducts('next');
                } else if (event.key === 'Escape') {
                    modal.style.display = 'none';
                }
            }
        });
    }
    
    // Helper function to generate bag placeholder images
    function generateBagPlaceholder(viewType, productName) {
        // Format product name for display
        const displayName = productName.length > 25 ? productName.substring(0, 22) + '...' : productName;
        
        // Format view type for display
        const formattedViewType = viewType.charAt(0).toUpperCase() + viewType.slice(1) + ' View';
        
        return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='800' viewBox='0 0 800 800'%3E%3Crect width='800' height='800' fill='%23f8f8f8'/%3E%3Crect x='200' y='150' width='400' height='500' rx='10' fill='%23e0e0e0'/%3E%3Cpath d='M350,150 L350,120 Q350,80 400,80 Q450,80 450,120 L450,150' stroke='%23d0d0d0' stroke-width='10' fill='none'/%3E%3Ctext x='400' y='400' font-family='Arial' font-size='24' text-anchor='middle' fill='%23808080'%3E${encodeURIComponent(displayName)}%3C/text%3E%3Ctext x='400' y='440' font-family='Arial' font-size='20' text-anchor='middle' fill='%23a0a0a0'%3E${formattedViewType}%3C/text%3E%3C/svg%3E`;
    }
    
    // Function to navigate between different products
    function navigateProducts(direction) {
        if (window.allProducts && window.allProducts.length > 1) {
            if (direction === 'next') {
                window.currentProductIndex = (window.currentProductIndex + 1) % window.allProducts.length;
            } else {
                window.currentProductIndex = (window.currentProductIndex - 1 + window.allProducts.length) % window.allProducts.length;
            }
            
            console.log(`Navigating to product ${window.currentProductIndex + 1}/${window.allProducts.length}`);
            
            // Update the modal with the new product
            displayProductInModal(window.currentProductIndex);
        }
    }
    
    // Expose the navigateProducts function globally
    window.navigateProducts = navigateProducts;
});