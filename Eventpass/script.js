// Gestion des événements
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des modales
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close');

    // Fermeture des modales en cliquant sur le bouton de fermeture
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });
    
    // Fermeture des modales en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    // Gestion des boutons de connexion et d'inscription partout sur la page
    // (nav-link ou pas)
    document.querySelectorAll('[data-action]').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            if (action === 'login') {
                document.getElementById('loginModal').style.display = 'block';
            } else if (action === 'register') {
                document.getElementById('registerModal').style.display = 'block';
            }
        });
    });

    // Gestion du formulaire de connexion
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de la connexion');
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON:', e);
                    alert('Une erreur est survenue lors de la connexion');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la connexion');
            });
        });
    }

    // Gestion du formulaire d'inscription
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de l\'inscription');
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON:', e);
                    alert('Une erreur est survenue lors de l\'inscription');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'inscription');
            });
        });
    }

    // Gestion de la barre de recherche et des filtres
    const searchInput = document.getElementById('search-input');
    const eventCards = document.querySelectorAll('.event-card');
    const themeButtons = document.querySelectorAll('.theme-btn');
    
    // Fonction de filtrage des événements
    function filterEvents() {
        const searchTerm = searchInput?.value.toLowerCase() || '';
        const activeButton = document.querySelector('.theme-btn.active');
        const selectedCategory = activeButton?.dataset.category || '';
        
        eventCards.forEach(card => {
            const title = card.querySelector('.event-title').textContent.toLowerCase();
            const description = card.querySelector('.event-description').textContent.toLowerCase();
            const category = card.dataset.category;
            
            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;
            
            card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
        });
    }
    
    // Ajout des écouteurs d'événements pour la recherche
    if (searchInput) {
        searchInput.addEventListener('input', filterEvents);
    }
    
    // Ajout des écouteurs d'événements pour les boutons de thème
    themeButtons.forEach(button => {
        button.addEventListener('click', function() {
            themeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterEvents();
        });
    });

    // Gestion de l'inscription aux événements
    window.registerForEvent = function(eventId) {
        const formData = new FormData();
        formData.append('action', 'register_event');
        formData.append('event_id', eventId);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            try {
                const result = JSON.parse(data);
                if (result.success) {
                    alert('Inscription réussie !');
                    window.location.reload();
                } else {
                    alert(result.message || 'Erreur lors de l\'inscription à l\'événement');
                }
            } catch (e) {
                console.error('Erreur de parsing JSON:', e);
                alert('Une erreur est survenue lors de l\'inscription à l\'événement');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'inscription à l\'événement');
        });
    };

    // Gestion du formulaire d'ajout d'événement
    const addEventForm = document.getElementById('add-event-form');
    if (addEventForm) {
        addEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        alert('Événement ajouté avec succès !');
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de l\'ajout de l\'événement');
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON:', e);
                    alert('Une erreur est survenue lors de l\'ajout de l\'événement');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            });
        });
    }

    // Gestion des utilisateurs
    document.querySelectorAll('.change-role').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const currentRole = this.dataset.currentRole;
            const newRole = currentRole === 'admin' ? 'user' : 'admin';
            
            if (confirm(`Voulez-vous changer le rôle de cet utilisateur en ${newRole} ?`)) {
                const formData = new FormData();
                formData.append('action', 'change_role');
                formData.append('user_id', userId);
                formData.append('new_role', newRole);
                
                fetch('index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    try {
                        const result = JSON.parse(data);
                        if (result.success) {
                            window.location.reload();
                        } else {
                            alert(result.message || 'Erreur lors du changement de rôle');
                        }
                    } catch (e) {
                        console.error('Erreur de parsing JSON:', e);
                        alert('Une erreur est survenue lors du changement de rôle');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue');
                });
            }
        });
    });

    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            
            if (confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userId);
                
                fetch('index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    try {
                        const result = JSON.parse(data);
                        if (result.success) {
                            window.location.reload();
                        } else {
                            alert(result.message || 'Erreur lors de la suppression de l\'utilisateur');
                        }
                    } catch (e) {
                        console.error('Erreur de parsing JSON:', e);
                        alert('Une erreur est survenue lors de la suppression de l\'utilisateur');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue');
                });
            }
        });
    });

    // Gestion de la modale d'édition d'événement
    window.editEvent = function(eventId) {
        // Récupérer les infos de la carte événement
        const card = document.querySelector('.event-card button[onclick="editEvent(' + eventId + ')"]').closest('.event-card');
        document.getElementById('edit-event-id').value = eventId;
        document.getElementById('edit-event-title').value = card.querySelector('.event-title').textContent.trim();
        document.getElementById('edit-event-description').value = card.querySelector('.event-description').textContent.trim();
        document.getElementById('edit-event-category').value = card.dataset.category;
        document.getElementById('edit-event-price').value = card.querySelector('.event-price').textContent.includes('Gratuit') ? 0 : parseFloat(card.querySelector('.event-price').textContent);
        // Pour l'image, la date et l'heure, il faut les stocker dans des data-attributes sur la carte
        document.getElementById('edit-event-image').value = card.querySelector('img').getAttribute('src');
        // On suppose que la date et l'heure sont dans .event-date sous forme "dd/mm/yyyy hh:mm"
        const dateText = card.querySelector('.event-date').textContent.trim();
        const [datePart, timePart] = dateText.split(' ');
        const [day, month, year] = datePart.split('/');
        document.getElementById('edit-event-date').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        document.getElementById('edit-event-time').value = timePart;
        document.getElementById('edit-event-capacity').value = card.dataset.capacity || '';
        // Ouvre la modale
        const modal = document.getElementById('edit-event-modal');
        modal.classList.add('show');
        modal.style.display = 'flex';
    }
    // Fermeture de la modale d'édition
    const editEventCloseBtn = document.querySelector('#edit-event-modal .close');
    if (editEventCloseBtn) {
        editEventCloseBtn.onclick = () => {
            const modal = document.getElementById('edit-event-modal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        };
    }
    // Soumission du formulaire d'édition
    const editEventForm = document.getElementById('edit-event-form');
    if (editEventForm) {
        editEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        alert('Événement modifié avec succès !');
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de la modification');
                    }
                } catch (e) {
                    alert('Erreur lors de la modification');
                }
            })
            .catch(() => alert('Erreur lors de la modification'));
        });
    }

    // Gestion de l'inscription aux événements
    const addEventBtn = document.getElementById('add-event-btn');
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => {
            const addEventModal = document.getElementById('addEventModal');
            if (addEventModal) {
                addEventModal.style.display = 'block';
            }
        });
    }

    // Gestion du défilement fluide
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Suppression d'un événement (admin)
    window.deleteEvent = function(eventId) {
        if (confirm('Voulez-vous vraiment supprimer cet événement ?')) {
            const formData = new FormData();
            formData.append('action', 'delete_event');
            formData.append('event_id', eventId);
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        alert('Événement supprimé avec succès !');
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de la suppression de l\'événement');
                    }
                } catch (e) {
                    alert('Erreur lors de la suppression de l\'événement');
                }
            })
            .catch(() => alert('Erreur lors de la suppression de l\'événement'));
        }
    };

    // Désinscription d'un événement
    window.unregisterFromEvent = function(eventId) {
        if (confirm('Voulez-vous vraiment vous désinscrire de cet événement ?')) {
            const formData = new FormData();
            formData.append('action', 'unregister_event');
            formData.append('event_id', eventId);
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        alert('Vous avez été désinscrit de l\'événement.');
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erreur lors de la désinscription.');
                    }
                } catch (e) {
                    alert('Erreur lors de la désinscription.');
                }
            })
            .catch(() => alert('Erreur lors de la désinscription.'));
        }
    };
});

// Fonction utilitaire pour afficher les messages
function showMessage(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.insertBefore(alert, document.body.firstChild);
    setTimeout(() => alert.remove(), 5000);
} 