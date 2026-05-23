<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$current_user = getUser();
$with_id = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$receiver_name = '';

if ($with_id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$with_id]);
    $receiver = $stmt->fetch();
    if ($receiver) {
        $receiver_name = $receiver['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Bini-Dang Market</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-container {
            display: flex;
            height: 70vh;
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        .chat-sidebar {
            width: 30%;
            border-right: 1px solid #e5e7eb;
            background: #f9fafb;
            overflow-y: auto;
        }
        .chat-sidebar h3 {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            margin: 0;
        }
        .conversation-item {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .conversation-item:hover {
            background: #f3f4f6;
        }
        .conversation-item.active {
            background: #f97316;
            color: white;
        }
        .conversation-name {
            font-weight: bold;
        }
        .conversation-last {
            font-size: 0.7rem;
            color: #6b7280;
            margin-top: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }
        .unread-badge {
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: white;
            font-weight: bold;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #f9fafb;
        }
        .message {
            margin-bottom: 1rem;
        }
        .message-me {
            text-align: right;
        }
        .message-me .bubble {
            background: #000000;
            color: white;
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            max-width: 70%;
        }
        .message-other .bubble {
            background: white;
            border: 1px solid #e5e7eb;
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            max-width: 70%;
        }
        .message-time {
            font-size: 0.6rem;
            color: #9ca3af;
            margin-top: 0.2rem;
        }
        .chat-input {
            display: flex;
            gap: 0.5rem;
            padding: 1rem;
            background: white;
            border-top: 1px solid #e5e7eb;
        }
        .chat-input input {
            flex: 1;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 40px;
            outline: none;
        }
        .chat-input input:focus {
            border-color: #f97316;
        }
        .chat-input button {
            background: #000000;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 0.6rem 1.2rem;
            cursor: pointer;
        }
        .chat-input button:hover {
            background: #333333;
        }
        .empty-chat {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            .chat-sidebar {
                width: 100%;
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">💬 Messagerie</h1>
    
    <div class="chat-container">
        <div class="chat-sidebar">
            <h3>💬 Conversations</h3>
            <div id="conversations-list">
                <div style="padding: 1rem; text-align: center;">Chargement...</div>
            </div>
        </div>
        
        <div class="chat-main">
            <div id="chat-header" class="chat-header">
                <?php if ($with_id > 0 && $receiver_name): ?>
                    💬 Conversation avec <?= htmlspecialchars($receiver_name) ?>
                <?php else: ?>
                    Sélectionnez une conversation
                <?php endif; ?>
            </div>
            <div id="chat-messages" class="chat-messages">
                <?php if ($with_id > 0): ?>
                    <div style="text-align: center; padding: 2rem;">Chargement...</div>
                <?php else: ?>
                    <div class="empty-chat">👈 Cliquez sur une conversation</div>
                <?php endif; ?>
            </div>
            <div id="chat-input-area" class="chat-input" style="<?= $with_id > 0 ? 'display: flex;' : 'display: none;' ?>">
                <input type="text" id="message-input" placeholder="Écrivez votre message...">
                <button id="send-btn">Envoyer</button>
            </div>
        </div>
    </div>
</div>

<script>
const currentUserId = <?= $_SESSION['user_id'] ?>;
let currentReceiverId = <?= $with_id ?: 0 ?>;
let currentReceiverName = '<?= addslashes($receiver_name) ?>';

function loadConversations() {
    fetch('api/messages.php?action=conversations')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('conversations-list');
            if (data.error) {
                container.innerHTML = `<div style="padding:1rem;color:red;">Erreur: ${data.error}</div>`;
                return;
            }
            if (!data || data.length === 0) {
                container.innerHTML = '<div style="padding:1rem;text-align:center;">Aucune conversation</div>';
                return;
            }
            let html = '';
            data.forEach(conv => {
                const activeClass = (currentReceiverId == conv.other_user_id) ? 'active' : '';
                const unreadBadge = conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : '';
                html += `
                    <div class="conversation-item ${activeClass}" onclick="selectConversation(${conv.other_user_id}, '${escapeHtml(conv.name)}')">
                        <div>
                            <div class="conversation-name">${escapeHtml(conv.name)}</div>
                            <div class="conversation-last">${escapeHtml(conv.last_message || 'Nouvelle conversation')}</div>
                        </div>
                        ${unreadBadge}
                    </div>
                `;
            });
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('conversations-list').innerHTML = '<div style="padding:1rem;color:red;">Erreur de chargement</div>';
        });
}

function selectConversation(userId, userName) {
    currentReceiverId = userId;
    currentReceiverName = userName;
    document.getElementById('chat-header').innerHTML = `💬 Conversation avec ${escapeHtml(userName)}`;
    document.getElementById('chat-input-area').style.display = 'flex';
    
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    if (event && event.target) {
        event.target.closest('.conversation-item').classList.add('active');
    }
    
    loadMessages(userId);
}

function loadMessages(userId) {
    fetch(`api/messages.php?with=${userId}`)
        .then(res => res.json())
        .then(messages => {
            const container = document.getElementById('chat-messages');
            if (messages.error) {
                container.innerHTML = `<div class="empty-chat">Erreur: ${messages.error}</div>`;
                return;
            }
            if (!messages || messages.length === 0) {
                container.innerHTML = '<div class="empty-chat">Aucun message. Commencez la conversation !</div>';
                return;
            }
            let html = '';
            messages.forEach(msg => {
                if (msg.sender_id == currentUserId) {
                    html += `
                        <div class="message message-me">
                            <div class="bubble">${escapeHtml(msg.message)}</div>
                            <div class="message-time">${formatTime(msg.created_at)}</div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="message message-other">
                            <div class="bubble">${escapeHtml(msg.message)}</div>
                            <div class="message-time">${formatTime(msg.created_at)}</div>
                        </div>
                    `;
                }
            });
            container.innerHTML = html;
            container.scrollTop = container.scrollHeight;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('chat-messages').innerHTML = '<div class="empty-chat">Erreur de chargement des messages</div>';
        });
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message) {
        alert('Écrivez un message d\'abord');
        return;
    }
    
    if (!currentReceiverId) {
        alert('Sélectionnez d\'abord une conversation');
        return;
    }
    
    fetch('api/messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ receiver_id: currentReceiverId, message: message })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentReceiverId);
            loadConversations();
        } else {
            alert('Erreur: ' + (data.error || 'Envoi échoué'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    });
}

function formatTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Événements
const sendBtn = document.getElementById('send-btn');
if (sendBtn) {
    sendBtn.onclick = sendMessage;
}
const messageInput = document.getElementById('message-input');
if (messageInput) {
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
}

// Chargement initial
loadConversations();

if (currentReceiverId > 0 && currentReceiverName) {
    setTimeout(() => {
        loadMessages(currentReceiverId);
    }, 500);
}

// Rafraîchir toutes les 3 secondes
setInterval(() => {
    if (currentReceiverId) {
        loadMessages(currentReceiverId);
    }
    loadConversations();
}, 3000);
</script>

<?php include 'includes/footer.php'; ?>