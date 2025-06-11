// assets/js/scripts.js
document.addEventListener('DOMContentLoaded', function() {
    // Sepet işlemleri
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            addToCart(productId);
        });
    });

    // Miktar değişiklikleri
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            updateCart(this);
        });
    });

    // Dropdown menüler
    document.querySelectorAll('.dropdown-toggle').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
    });
});

function addToCart(productId) {
    fetch('../ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Sepet sayacını güncelle
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = data.cart_count;
            });
            
            // Bildirim göster
            showAlert('Ürün sepete eklendi!', 'success');
        } else {
            showAlert('Hata: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Bir hata oluştu, lütfen tekrar deneyin.', 'danger');
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}