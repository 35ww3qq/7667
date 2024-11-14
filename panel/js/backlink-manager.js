// Site selection
function changeSite(siteId) {
    if (siteId) {
        window.location.href = `backlinks.php?site_id=${siteId}`;
    } else {
        window.location.href = 'backlinks.php';
    }
}

// Backlink management
function addBacklink(siteId) {
    const targetUrl = prompt('Hedef URL (örn: https://example.com/page):');
    if (!targetUrl) return;
    
    const anchorText = prompt('Anchor Text:');
    if (!anchorText) return;
    
    fetch('../api/backlinks.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            site_id: siteId,
            target_url: targetUrl,
            anchor_text: anchorText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Backlink eklenirken bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function editBacklink(backlinkId) {
    fetch(`../api/backlinks.php?id=${backlinkId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const backlink = data.backlink;
                const targetUrl = prompt('Hedef URL:', backlink.target_url);
                if (!targetUrl) return;
                
                const anchorText = prompt('Anchor Text:', backlink.anchor_text);
                if (!anchorText) return;
                
                return fetch('../api/backlinks.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: backlinkId,
                        target_url: targetUrl,
                        anchor_text: anchorText
                    })
                });
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Backlink güncellenirken bir hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu');
        });
}

function deleteBacklink(backlinkId) {
    if (!confirm('Bu backlinki silmek istediğinizden emin misiniz?')) return;
    
    fetch('../api/backlinks.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: backlinkId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Backlink silinirken bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}