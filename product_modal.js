// Product image modal functionality with carousel
document.addEventListener('DOMContentLoaded', function() {
  // Store current image index and product images array
  let currentImageIndex = 0;
  let productImages = [];
  
  // Create modal elements if they don't exist
  if (!document.getElementById('productModal')) {
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
  
  // Define custom product image sources - used for demo carousel
  const customImageSources = {
    'jacket': ['images/jacket1.jpg', 'images/jacket2.jpg', 'images/jacket3.jpg'],
    'dress': ['images/dress1.jpg', 'images/dress2.jpg'],
    'shirt': ['images/shirt1.jpg', 'images/shirt2.jpg'],
    'coat': ['images/coat1.jpg', 'images/coat2.jpg'],
    'default': ['images/jacket2.jpg', 'images/jacket3.jpg'] // Default images if no match
  };
  
  // Function to navigate between images
  function navigateImages(direction) {
    const modal = document.getElementById('productModal');
    const modalImg = document.getElementById('modalImage');
    
    // If we're using custom carousel
    if (productImages.length > 0) {
      if (direction === 'next') {
        currentImageIndex = (currentImageIndex + 1) % productImages.length;
      } else {
        currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
      }
      
      // Update the image source
      modalImg.src = productImages[currentImageIndex];
    }
  }
  
  // Add click handlers to all product images
  const productImgElements = document.querySelectorAll('.product-image-img');
  productImgElements.forEach(function(img) {
    img.style.cursor = 'pointer';
    img.addEventListener('click', function() {
      const modal = document.getElementById('productModal');
      const modalImg = document.getElementById('modalImage');
      const productInfo = document.querySelector('.modal-product-info');
      
      // Get product details
      const productContainer = this.closest('.product-item');
      const productName = productContainer ? productContainer.querySelector('.product-name').textContent : '';
      const productPrice = productContainer ? productContainer.querySelector('.product-price').textContent : '';
      
      // Set the source to high resolution version (assuming larger images are in a "large" subfolder)
      // Or use the same image if no larger version exists
      let imgSrc = this.src;
      
      // If thumbnail size, try to load a larger version
      if (imgSrc.includes('thumbnails/')) {
        imgSrc = imgSrc.replace('thumbnails/', 'large/');
      }
      
      // Reset current image index
      currentImageIndex = 0;
      
      // Create an array of images for this product (for carousel)
      // First determine product type from name or src
      let productType = 'default';
      const imgFileName = imgSrc.split('/').pop().toLowerCase();
      
      if (productName.toLowerCase().includes('jacket') || imgFileName.includes('jacket')) {
        productType = 'jacket';
      } else if (productName.toLowerCase().includes('dress') || imgFileName.includes('dress')) {
        productType = 'dress';
      } else if (productName.toLowerCase().includes('shirt') || imgFileName.includes('shirt')) {
        productType = 'shirt';
      } else if (productName.toLowerCase().includes('coat') || imgFileName.includes('coat')) {
        productType = 'coat';
      }
      
      // Set current image as first in array, then add other images from the same category
      productImages = [imgSrc];
      
      // Add more images from the same category
      if (customImageSources[productType]) {
        customImageSources[productType].forEach(function(src) {
          if (src !== imgSrc) {
            productImages.push(src);
          }
        });
      }
      
      // Always ensure we have at least one more image
      if (productImages.length === 1) {
        productImages.push('images/jacket2.jpg');
      }
      
      modalImg.src = productImages[currentImageIndex];
      productInfo.innerHTML = `<h3>${productName}</h3><p class="modal-price">${productPrice}</p>`;
      
      // Show the modal
      modal.style.display = 'block';
    });
  });
});