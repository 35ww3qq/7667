// Credit management functions
function buyCredits(packageId) {
    if (!confirm('Bu kredi paketini satın almak istediğinizden emin misiniz?')) return;
    
    fetch('../api/credits.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ package_id: packageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPaymentSuccess(data.transaction_id);
            setTimeout(() => location.reload(), 2000);
        } else {
            alert(data.error || 'Ödeme işlemi başarısız');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function showPaymentSuccess(transactionId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 text-center">
            <div class="text-green-500 mb-4">
                <i class="fas fa-check-circle text-5xl"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Ödeme Başarılı!</h3>
            <p class="text-gray-600 mb-4">Kredi bakiyeniz güncellendi.</p>
            <p class="text-sm text-gray-500">İşlem No: ${transactionId}</p>
            <button onclick="this.closest('.fixed').remove()" 
                    class="mt-6 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Tamam
            </button>
        </div>
    `;
    document.body.appendChild(modal);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        new Tooltip(element, {
            placement: 'top',
            trigger: 'hover'
        });
    });
});