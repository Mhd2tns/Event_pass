<?php
// Démarrer la session
session_start();

// Inclusion du fichier de configuration
require_once 'config.php';

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = ($user['role'] === 'admin') ? 1 : 0;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            }
            exit;

        case 'register':
            $email = clean($_POST['email']);
            $password = $_POST['password'];
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
                exit;
            }
            
            // Créer le nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')");
            if ($stmt->execute([$email, $hashedPassword])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = 0;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
            }
            exit;

        case 'register_event':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
                exit;
            }
            
            $eventId = (int)$_POST['event_id'];
            $userId = $_SESSION['user_id'];
            
            // Vérifier si l'événement existe et n'est pas plein
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                echo json_encode(['success' => false, 'message' => 'Événement non trouvé']);
                exit;
            }
            
            // Vérifier si l'utilisateur est déjà inscrit
            if (isUserRegistered($userId, $eventId)) {
                echo json_encode(['success' => false, 'message' => 'Vous êtes déjà inscrit à cet événement']);
                exit;
            }
            
            // Inscrire l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)");
            if ($stmt->execute([$userId, $eventId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
            }
            exit;

        case 'add_event':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            }

            $title = clean($_POST['title']);
            $description = clean($_POST['description']);
            $category = clean($_POST['category']);
            $price = (float)$_POST['price'];
            $capacity = (int)$_POST['capacity'];
            $image_url = clean($_POST['image_url']);
            $event_date = clean($_POST['event_date']);
            $event_time = clean($_POST['event_time']);

            $stmt = $pdo->prepare("
                INSERT INTO events (title, description, category, price, capacity, image_url, event_date, event_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$title, $description, $category, $price, $capacity, $image_url, $event_date, $event_time])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'événement']);
            }
            exit;

        case 'change_role':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            }

            $userId = (int)$_POST['user_id'];
            $newRole = clean($_POST['new_role']);

            if (!in_array($newRole, ['admin', 'user'])) {
                echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$newRole, $userId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du changement de rôle']);
            }
            exit;

        case 'delete_user':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            }

            $userId = (int)$_POST['user_id'];

            // Ne pas permettre la suppression du dernier admin
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user['role'] === 'admin') {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $stmt->execute();
                $adminCount = $stmt->fetchColumn();

                if ($adminCount <= 1) {
                    echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le dernier administrateur']);
                    exit;
                }
            }

            // Supprimer les inscriptions de l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
            }
            exit;

        case 'delete_event':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            }
            $eventId = (int)$_POST['event_id'];
            // Supprimer les inscriptions liées à l'événement
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE event_id = ?");
            $stmt->execute([$eventId]);
            // Supprimer l'événement
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt->execute([$eventId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
            }
            exit;

        case 'edit_event':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            }
            $eventId = (int)$_POST['event_id'];
            $title = clean($_POST['title']);
            $description = clean($_POST['description']);
            $category = clean($_POST['category']);
            $price = (float)$_POST['price'];
            $capacity = (int)$_POST['capacity'];
            $image_url = clean($_POST['image_url']);
            $event_date = clean($_POST['event_date']);
            $event_time = clean($_POST['event_time']);
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, category = ?, price = ?, capacity = ?, image_url = ?, event_date = ?, event_time = ? WHERE id = ?");
            if ($stmt->execute([$title, $description, $category, $price, $capacity, $image_url, $event_date, $event_time, $eventId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification de l\'événement']);
            }
            exit;

        case 'unregister_event':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
                exit;
            }
            $eventId = (int)$_POST['event_id'];
            $userId = $_SESSION['user_id'];
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?");
            if ($stmt->execute([$userId, $eventId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la désinscription']);
            }
            exit;
    }
}

// Récupérer la page demandée
$page = $_GET['page'] ?? 'home';

// Récupérer les événements
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $stmt->fetchAll();

// Récupérer les statistiques pour la page admin
if ($page === 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    $totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalRegistrations = $pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPass - Plateforme d'événements</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <a href="?page=home" class="logo">EventPass</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?page=my-events" class="nav-link">Mes événements</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1): ?>
                        <a href="?page=admin" class="nav-link">Admin</a>
                    <?php endif; ?>
                    <a href="?page=contact" class="nav-link">Contact</a>
                    <a href="?action=logout" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <button class="nav-link" data-action="login">Connexion</button>
                    <button class="nav-link" data-action="register">Inscription</button>
                    <a href="?page=contact" class="nav-link">Contact</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($page === 'home'): ?>
        <!-- Bannière -->
        <div class="banner">
            <div class="banner-content">
                <h1>Découvrez des événements exceptionnels</h1>
                <p>Rejoignez la communauté EventPass et vivez des moments inoubliables</p>
                <div class="banner-buttons">
                    <a href="#events" class="btn">Explorer les événements</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <button class="btn" data-action="register">Créer un compte</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters">
            <div class="filter-group">
                <input type="text" id="search-input" class="form-control" placeholder="Rechercher un événement (titre ou description)...">
            </div>
            <div class="theme-buttons">
                <button class="theme-btn" data-category="">Tous</button>
                <button class="theme-btn" data-category="musique">Musique</button>
                <button class="theme-btn" data-category="sport">Sport</button>
                <button class="theme-btn" data-category="art">Art</button>
                <button class="theme-btn" data-category="technologie">Technologie</button>
            </div>
        </div>

        <!-- Liste des événements -->
        <div id="events" class="event-grid">
            <?php if (empty($events)): ?>
                <p class="no-events">Aucun événement disponible pour le moment.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card" data-category="<?php echo htmlspecialchars($event['category']); ?>">
                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date"><?php echo date('d/m/Y H:i', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            <p class="event-price"><?php echo $event['price'] == 0 ? 'Gratuit' : $event['price'] . '€'; ?></p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-primary" onclick="registerForEvent(<?php echo $event['id']; ?>)">
                                    S'inscrire
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" data-action="login">
                                    Connectez-vous pour vous inscrire
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php elseif ($page === 'my-events' && isset($_SESSION['user_id'])): ?>
        <!-- Page Mes événements -->
        <div class="page-container">
            <h2>Mes événements</h2>
            <div class="event-grid">
                <?php
                $stmt = $pdo->prepare("
                    SELECT e.* FROM events e
                    JOIN event_registrations er ON e.id = er.event_id
                    WHERE er.user_id = ?
                    ORDER BY e.event_date ASC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $userEvents = $stmt->fetchAll();
                
                if (empty($userEvents)): ?>
                    <p class="no-events">Vous n'êtes inscrit à aucun événement.</p>
                <?php else: ?>
                    <?php foreach ($userEvents as $event): ?>
                        <div class="event-card">
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-date"><?php echo date('d/m/Y H:i', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p class="event-price"><?php echo $event['price'] == 0 ? 'Gratuit' : $event['price'] . '€'; ?></p>
                                <button class="btn btn-danger" onclick="unregisterFromEvent(<?php echo $event['id']; ?>)" data-event-id="<?php echo $event['id']; ?>">Se désinscrire</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($page === 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <!-- Page Admin -->
        <div class="admin-wrapper">
            <h2 class="admin-title">Panneau d'administration</h2>
            <div class="admin-stats">
                <div class="stat-card">
                    <h3>Total des événements</h3>
                    <p><?php echo $totalEvents; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total des utilisateurs</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total des inscriptions</h3>
                    <p><?php echo $totalRegistrations; ?></p>
                </div>
            </div>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="document.getElementById('add-event-modal').style.display='block'">
                    Ajouter un événement
                </button>
                <button class="btn btn-secondary" onclick="document.getElementById('manage-users-modal').style.display='block'">
                    Gérer les utilisateurs
                </button>
            </div>
            <div class="admin-events">
                <h3>Liste des événements</h3>
                <div class="event-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-date"><?php echo date('d/m/Y H:i', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p class="event-price"><?php echo $event['price'] == 0 ? 'Gratuit' : $event['price'] . '€'; ?></p>
                                <div class="event-actions">
                                    <button class="btn btn-small" onclick="editEvent(<?php echo $event['id']; ?>)">Modifier</button>
                                    <button class="btn btn-small btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'contact'): ?>
        <!-- Page Contact -->
        <div class="contact-wrapper">
            <h2 class="contact-title">Contactez-nous</h2>
            <div class="contact-cards">
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <h3>Téléphone</h3>
                    <p>01 23 45 67 89</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>contact@eventpass.fr</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Adresse</h3>
                    <p>123 Avenue des Événements<br>75001 Paris</p>
                </div>
            </div>
            <form class="contact-form">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Votre nom" required class="form-control">
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Votre email" required class="form-control">
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Votre message" required class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Modal de connexion -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Connexion</h2>
                <button class="close" onclick="document.getElementById('loginModal').style.display='none'">&times;</button>
            </div>
            <form id="loginForm" method="post">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" class="form-control" placeholder="Votre email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Mot de passe</label>
                    <input type="password" id="login-password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>

    <!-- Modal d'inscription -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Inscription</h2>
                <button class="close" onclick="document.getElementById('registerModal').style.display='none'">&times;</button>
            </div>
            <form id="registerForm" method="post">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" class="form-control" placeholder="Votre email" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Mot de passe</label>
                    <input type="password" id="register-password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>
        </div>
    </div>

    <!-- Modal d'ajout d'événement -->
    <div id="add-event-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Ajouter un événement</h2>
                <button class="close" onclick="document.getElementById('add-event-modal').style.display='none'">&times;</button>
            </div>
            <form id="add-event-form" method="post">
                <input type="hidden" name="action" value="add_event">
                <div class="add-event-grid">
                    <div class="form-group">
                        <label for="event-title">Titre</label>
                        <input type="text" id="event-title" name="title" class="form-control" placeholder="Titre de l'événement" required>
                    </div>
                    <div class="form-group">
                        <label for="event-description">Description</label>
                        <textarea id="event-description" name="description" class="form-control" placeholder="Description de l'événement" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="event-category">Catégorie</label>
                        <select id="event-category" name="category" class="form-control" required>
                            <option value="">Sélectionner une catégorie</option>
                            <option value="musique">Musique</option>
                            <option value="sport">Sport</option>
                            <option value="art">Art</option>
                            <option value="technologie">Technologie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event-price">Prix (€)</label>
                        <input type="number" id="event-price" name="price" min="0" step="0.01" class="form-control" placeholder="0 pour gratuit" required>
                    </div>
                    <div class="form-group">
                        <label for="event-capacity">Capacité</label>
                        <input type="number" id="event-capacity" name="capacity" min="1" class="form-control" placeholder="Nombre de places" required>
                    </div>
                    <div class="form-group">
                        <label for="event-image">URL de l'image</label>
                        <input type="text" id="event-image" name="image_url" class="form-control" placeholder="Lien de l'image" required>
                    </div>
                    <div class="form-group">
                        <label for="event-date">Date</label>
                        <input type="date" id="event-date" name="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="event-time">Heure</label>
                        <input type="time" id="event-time" name="event_time" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary add-event-btn">Ajouter l'événement</button>
            </form>
        </div>
    </div>

    <!-- Modal de gestion des utilisateurs -->
    <div id="manage-users-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Gérer les utilisateurs</h2>
                <button class="close" onclick="document.getElementById('manage-users-modal').style.display='none'">&times;</button>
            </div>
            <div class="users-list">
                <?php
                $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                foreach ($users as $user):
                ?>
                <div class="user-card">
                    <div class="user-info">
                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="user-role">Rôle: <?php echo htmlspecialchars($user['role']); ?></p>
                        <p class="user-date">Inscrit le: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="user-actions">
                        <button class="btn btn-small change-role" data-user-id="<?php echo $user['id']; ?>" data-current-role="<?php echo $user['role']; ?>">
                            Changer le rôle
                        </button>
                        <button class="btn btn-small btn-danger delete-user" data-user-id="<?php echo $user['id']; ?>">
                            Supprimer
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de modification d'événement -->
    <div id="edit-event-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier l'événement</h2>
                <button class="close" onclick="document.getElementById('edit-event-modal').style.display='none'">&times;</button>
            </div>
            <form id="edit-event-form" method="post">
                <input type="hidden" name="action" value="edit_event">
                <input type="hidden" id="edit-event-id" name="event_id">
                <div class="add-event-grid">
                    <div class="form-group">
                        <label for="edit-event-title">Titre</label>
                        <input type="text" id="edit-event-title" name="title" class="form-control" placeholder="Titre de l'événement" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-description">Description</label>
                        <textarea id="edit-event-description" name="description" class="form-control" placeholder="Description de l'événement" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-category">Catégorie</label>
                        <select id="edit-event-category" name="category" class="form-control" required>
                            <option value="musique">Musique</option>
                            <option value="sport">Sport</option>
                            <option value="art">Art</option>
                            <option value="technologie">Technologie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-price">Prix (€)</label>
                        <input type="number" id="edit-event-price" name="price" min="0" step="0.01" class="form-control" placeholder="0 pour gratuit" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-capacity">Capacité</label>
                        <input type="number" id="edit-event-capacity" name="capacity" min="1" class="form-control" placeholder="Nombre de places" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-image">URL de l'image</label>
                        <input type="text" id="edit-event-image" name="image_url" class="form-control" placeholder="Lien de l'image" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-date">Date</label>
                        <input type="date" id="edit-event-date" name="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-time">Heure</label>
                        <input type="time" id="edit-event-time" name="event_time" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary add-event-btn">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>EventPass est votre plateforme de gestion d'événements en ligne.</p>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@eventpass.com</p>
                <p>Tél: +33 1 23 45 67 89</p>
            </div>
            <div class="footer-section">
                <h3>Suivez-nous</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="legal-bar">
            <div class="legal-content">
                <div class="legal-links">
                    <a href="#" class="legal-link">Mentions légales</a>
                    <a href="#" class="legal-link">Politique de confidentialité</a>
                    <a href="#" class="legal-link">Conditions d'utilisation</a>
                    <a href="#" class="legal-link">Cookies</a>
                </div>
                <div class="copyright">
                    © 2024 EventPass. Tous droits réservés.
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html> 