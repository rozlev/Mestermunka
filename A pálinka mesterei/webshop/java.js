document.addEventListener('DOMContentLoaded', () => {
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    const cartPopup = document.getElementById('cart-popup');
    const cartItemsContainer = document.getElementById('cart-items');
    let cart = [];
  
    addToCartBtns.forEach((btn, index) => {
      btn.addEventListener('click', () => {
        // Get the product name and price
        const productName = btn.previousElementSibling.innerText.split(' - ')[0];
        const productPrice = btn.previousElementSibling.innerText.split(' - ')[1];
        
        // Add to cart array
        cart.push({ name: productName, price: productPrice });
  
        // Update cart popup
        updateCartPopup();
      });
    });
  
    function updateCartPopup() {
      if (cart.length > 0) {
        cartItemsContainer.innerHTML = cart.map(item => `<p>${item.name} - ${item.price}</p>`).join('');
      } else {
        cartItemsContainer.innerHTML = 'Nincs termék a kosárban';
      }
    }
  
    // Show cart popup
    document.getElementById('cart-icon').addEventListener('click', () => {
      cartPopup.style.display = 'block';
    });
  
    // Close cart popup
    document.getElementById('close-btn').addEventListener('click', () => {
      cartPopup.style.display = 'none';
    });
  });
  