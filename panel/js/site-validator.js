// Site validation functions
function validateSite(siteId) {
    fetch('../api/verify-site.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ site_id: siteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVerificationInstructions(data.verification_code);
        } else {
            alert(data.error || 'Site doğrulama başlatılamadı');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function showVerificationInstructions(code) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Site Doğrulama Adımları</h3>
            
            <ol class="space-y-4 mb-6">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-2">1</span>
                    <div>
                        <p class="font-medium">Doğrulama dosyası oluşturun</p>
                        <p class="text-sm text-gray-600">backlink-verify.txt adında bir dosya oluşturun</p>
                    </div>
                </li>
                
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-2">2</span>
                    <div>
                        <p class="font-medium">Doğrulama kodunu ekleyin</p>
                        <p class="text-sm text-gray-600">Dosyanın içine aşağıdaki kodu yapıştırın:</p>
                        <pre class="mt-2 bg-gray-50 p-2 rounded text-sm">${code}</pre>
                    </div>
                </li>
                
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-2">3</span>
                    <div>
                        <p class="font-medium">Dosyayı yükleyin</p>
                        <p class="text-sm text-gray-600">Dosyayı sitenizin ana dizinine yükleyin</p>
                    </div>
                </li>
            </ol>
            
            <div class="flex justify-end space-x-3">
                <button onclick="this.closest('.fixed').remove()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Kapat
                </button>
                <button onclick="checkVerification(${siteId})" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Doğrulamayı Kontrol Et
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function checkVerification(siteId) {
    fetch('../api/verify-site.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            site_id: siteId,
            action: 'check'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Site başarıyla doğrulandı!');
            location.reload();
        } else {
            alert(data.error || 'Doğrulama başarısız. Lütfen adımları kontrol edip tekrar deneyin.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}