<?php
// Activation de l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration de la base de données
define('DB_HOST', 'localhost:8889');
define('DB_NAME', 'eventpass_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Configuration de l'application
define('SITE_NAME', 'EventPass');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Créer la table event_registrations si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, user_id)
    )");

    // Vérifier si la colonne registered existe dans la table events
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'registered'");
    if ($stmt->rowCount() === 0) {
        // Si la colonne n'existe pas, l'ajouter
        $pdo->exec("ALTER TABLE events ADD COLUMN registered INT DEFAULT 0");
    }

} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonctions utilitaires
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1;
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isEventCreator($eventId) {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT creator_id FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    return $event && $event['creator_id'] === $_SESSION['user_id'];
}

function canManageEvent($eventId) {
    return isAdmin() || isEventCreator($eventId);
}

function isRegisteredForEvent($eventId) {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eventId, $_SESSION['user_id']]);
    return $stmt->rowCount() > 0;
}

// Fonction pour afficher un message
function showMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Fonction pour récupérer les messages
function getMessages() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Fonction pour vérifier si un événement est complet
function isEventFull($eventId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT capacity, registered FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    return $event && $event['registered'] >= $event['capacity'];
}

// Fonction pour vérifier si l'utilisateur est déjà inscrit à un événement
function isUserRegistered($userId, $eventId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$userId, $eventId]);
    return $stmt->fetchColumn() > 0;
}

// Fonction pour récupérer les événements d'un utilisateur
function getUserEvents($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT e.* 
        FROM events e 
        JOIN event_registrations er ON e.id = er.event_id 
        WHERE er.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Fonction pour récupérer les participants d'un événement
function getEventParticipants($eventId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.email 
        FROM users u 
        JOIN event_registrations er ON u.id = er.user_id 
        WHERE er.event_id = ?
    ");
    $stmt->execute([$eventId]);
    return $stmt->fetchAll();
}

// Fonction pour vérifier la capacité d'un événement
function checkEventCapacity($eventId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT capacity, registered FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    return $event && $event['registered'] < $event['capacity'];
}

// Fonction pour gérer les erreurs
function handleError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
?> 