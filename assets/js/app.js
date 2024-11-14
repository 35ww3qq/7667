if (typeof window.app !== 'undefined') {
    console.warn('app is already defined');
}

window.app = {
    // Utility functions
    async request(url, options = {}) {
        try {
            // Base URL'i al - /5/ alt dizinini ekle
            const baseUrl = window.location.pathname.includes('/5/panel/admin/') 
                ? '../../panel/api/' 
                : '../../../panel/api/';
                
            // URL'i düzelt
            const apiUrl = url.startsWith('http') 
                ? url 
                : baseUrl + url.replace(/^(?:\.\.\/)*(?:panel\/)?(?:api\/)?/, '');

            // Debug için URL'i logla
            console.log('API Request URL:', apiUrl);

            // CSRF token'ı al
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
            
            const response = await fetch(apiUrl, {
                ...options,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                }
            });
            
            // Session kontrolü
            if (response.status === 401) {
                window.location.href = window.location.pathname.includes('/5/panel/admin/')
                    ? '../../../login.php'
                    : '../../login.php';
                return;
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Bir hata oluştu');
            }
            
            return data;
        } catch (error) {
            console.error('Request error:', error);
            if (error.message === 'Failed to fetch') {
                alert('Sunucuya bağlanılamadı. Lütfen internet bağlantınızı kontrol edin.');
            } else {
                alert(error.message);
            }
            throw error;
        }
    },

    // User management
    users: {
        async add() {
            const modal = document.getElementById('editModal');
            if (!modal) {
                console.error('Edit modal not found');
                return;
            }

            try {
                // Reset form
                document.getElementById('editForm').reset();
                document.getElementById('editUserId').value = '';
                
                // Show modal
                modal.style.display = 'block';
            } catch (error) {
                console.error('Error in add:', error);
                alert('Kullanıcı ekleme formu açılırken bir hata oluştu');
            }
        },

        async edit(userId) {
            try {
                const modal = document.getElementById('editModal');
                if (!modal) {
                    throw new Error('Edit modal not found');
                }

                const data = await this.get(userId);
                
                if (data.success && data.data) {
                    const user = data.data;
                    
                    // Fill form
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('editUsername').value = user.username;
                    document.getElementById('editEmail').value = user.email;
                    document.getElementById('editCredits').value = user.credits;
                    document.getElementById('editIsAdmin').checked = user.is_admin == 1;
                    
                    // Show modal
                    modal.style.display = 'block';
                }
            } catch (error) {
                console.error('Error in edit:', error);
                alert('Kullanıcı bilgileri yüklenirken bir hata oluştu');
            }
        },

        async get(userId) {
            return await app.request(`users.php?id=${userId}`);
        },

        async submitEdit() {
            try {
                const userId = document.getElementById('editUserId').value;
                const data = {
                    id: userId,
                    username: document.getElementById('editUsername').value,
                    email: document.getElementById('editEmail').value,
                    password: document.getElementById('editPassword').value,
                    credits: document.getElementById('editCredits').value,
                    is_admin: document.getElementById('editIsAdmin').checked ? 1 : 0
                };

                const response = await app.request('users.php', {
                    method: userId ? 'PUT' : 'POST',
                    body: JSON.stringify(data)
                });

                if (response.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error in submitEdit:', error);
                alert('Kullanıcı kaydedilirken bir hata oluştu');
            }
        },

        async delete(userId) {
            try {
                if (!confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                    return;
                }

                const response = await app.request('users.php', {
                    method: 'DELETE',
                    body: JSON.stringify({ id: userId })
                });

                if (response.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error in delete:', error);
                alert('Kullanıcı silinirken bir hata oluştu');
            }
        }
    },

    // Initialize app
    init() {
        try {
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modals = document.getElementsByClassName('modal');
                for (let modal of modals) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
            };

            // Make global functions available
            window.editUser = (userId) => app.users.edit(userId);
            window.deleteUser = (userId) => app.users.delete(userId);
            window.addUser = () => app.users.add();

            console.log('App initialized successfully');
        } catch (error) {
            console.error('Error initializing app:', error);
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => app.init());