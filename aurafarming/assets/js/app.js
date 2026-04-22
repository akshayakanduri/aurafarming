// assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {
    // Dynamic Pricing for Farmers
    const qtyInput = document.getElementById('qty');
    const categorySelect = document.getElementById('category');
    const saleTypeSelect = document.getElementById('sale_type');
    const suggestedPriceSpan = document.getElementById('suggested_price');

    if (qtyInput && categorySelect && saleTypeSelect && suggestedPriceSpan) {
        const updatePrice = async () => {
            const qty = qtyInput.value;
            const category = categorySelect.value;
            const urgency = saleTypeSelect.value === 'urgent' ? 'yes' : 'no';
            
            if (!qty || !category) return;

            try {
                const res = await fetch(`api/endpoints.php?action=suggest_price&category=${category}&qty=${qty}&urgency=${urgency}`);
                const data = await res.json();
                if (data.status === 'success') {
                    suggestedPriceSpan.textContent = `₹${data.suggested_price} per kg`;
                }
            } catch (err) {
                console.error(err);
            }
        };

        qtyInput.addEventListener('input', updatePrice);
        categorySelect.addEventListener('change', updatePrice);
        saleTypeSelect.addEventListener('change', updatePrice);
    }

    // Farmer Add Crop Form is now handled by standard PHP POST submission in dashboard.php

    // Marketplace Filter & Load Logic (REMOVED - Now handled by PHP server-side rendering)
    // We only retain the Cart Logic here
    
    let cart = JSON.parse(localStorage.getItem('farmCart')) || [];
    updateCartCount();

    // Cart Handlers
    window.addToCart = (id, name, price, qty) => {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.quantity += qty;
        } else {
            cart.push({ id, name, price, quantity: qty });
        }
        localStorage.setItem('farmCart', JSON.stringify(cart));
        updateCartCount();
        alert(`${name} added to cart!`);
    };

    function updateCartCount() {
        const countSpan = document.getElementById('cartCount');
        if (countSpan) {
            // Replaced sum of quantities with count of unique items (equivalent to count($_SESSION['cart']))
            countSpan.textContent = cart.length;
        }
    }

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            if (cart.length === 0) {
                alert("Your cart is empty!");
                return;
            }
            try {
                const res = await fetch('api/endpoints.php?action=place_order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: cart })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alert(`Order placed successfully! Total cost: ₹${data.total}`);
                    cart = [];
                    localStorage.removeItem('farmCart');
                    updateCartCount();
                    location.reload();
                } else {
                    alert('Error placing order: ' + data.message);
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // Cart Modal (Simple Implementation for Marketplace)
    const cartIcon = document.getElementById('cartIcon');
    const cartModal = document.getElementById('cartModal');
    const closeCart = document.getElementById('closeCart');
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');

    if (cartIcon && cartModal) {
        cartIcon.addEventListener('click', () => {
            renderCart();
            cartModal.style.display = 'block';
        });

        closeCart.addEventListener('click', () => {
            cartModal.style.display = 'none';
        });

        window.renderCart = () => {
            cartItems.innerHTML = '';
            let total = 0;
            if (cart.length === 0) {
                cartItems.innerHTML = '<p>Your cart is empty.</p>';
            } else {
                cart.forEach((item, index) => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    cartItems.innerHTML += `
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                            <span>${item.name} (${item.quantity}kg x ₹${item.price})</span>
                            <span>₹${itemTotal}</span>
                            <button onclick="removeFromCart(${index})" style="background:none;border:none;color:red;cursor:pointer;">&times;</button>
                        </div>
                    `;
                });
            }
            cartTotal.textContent = total;
        };

        window.removeFromCart = (index) => {
            cart.splice(index, 1);
            localStorage.setItem('farmCart', JSON.stringify(cart));
            updateCartCount();
            renderCart();
        };
    }
});
