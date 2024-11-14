// Site management functions
function addSite() {
    const domain = prompt('Site domain adresini girin (örn: example.com):');
    if (!domain) return;

    fetch('../api/sites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ domain })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVerificationInstructions(data.verification_code);
        } else {
            alert(data.error || 'Site eklenirken bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function manageSite(siteId) {
    window.location.href = `backlinks.php?site_id=${siteId}`;
}

function verifySite(siteId) {
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
            alert(data.message || 'Site doğrulanırken bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function deleteSite(siteId) {
    if (!confirm('Bu siteyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) return;

    fetch('../api/sites.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ site_id: siteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Site silinirken bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
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