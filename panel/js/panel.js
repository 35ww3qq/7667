// Panel functionality
async function showBacklinks(siteId) {
    try {
        const response = await fetch(`api/backlinks.php?site_id=${siteId}`);
        const data = await response.json();
        
        const container = document.getElementById('backlinks-container');
        container.innerHTML = data.backlinks.map(backlink => `
            <div class="border p-4 rounded">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium">${backlink.target_url}</p>
                        <p class="text-sm text-gray-600">Anchor: ${backlink.anchor_text}</p>
                    </div>
                    <span class="px-2 py-1 rounded text-sm ${getStatusClass(backlink.status)}">
                        ${backlink.status}
                    </span>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error fetching backlinks:', error);
    }
}

function getStatusClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        removed: 'bg-red-100 text-red-800'
    };
    return classes[status] || '';
}

async function addBacklink(siteId) {
    const targetUrl = prompt('Enter target URL:');
    const anchorText = prompt('Enter anchor text:');
    
    if (!targetUrl || !anchorText) return;
    
    try {
        const response = await fetch('api/backlinks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                site_id: siteId,
                target_url: targetUrl,
                anchor_text: anchorText
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showBacklinks(siteId);
        } else {
            alert(result.message || 'Failed to add backlink');
        }
    } catch (error) {
        console.error('Error adding backlink:', error);
    }
}

async function addSite() {
    const domain = prompt('Enter site domain (e.g., example.com):');
    if (!domain) return;
    
    try {
        const response = await fetch('api/sites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ domain })
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Failed to add site');
        }
    } catch (error) {
        console.error('Error adding site:', error);
    }
}