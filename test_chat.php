<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    echo "Vous devez être connecté. <a href='login.php'>Se connecter</a>";
    exit;
}

$user = getUser();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Messagerie</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .conversation { background: #f0f0f0; padding: 10px; margin: 5px; cursor: pointer; }
        .message { background: #e0e0e0; padding: 10px; margin: 5px; }
        .chat-area { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 20px; }
        input, button { padding: 10px; margin: 5px; }
    </style>
</head>
<body>
<h2>Messagerie - <?= htmlspecialchars($user['name']) ?></h2>

<div id="conversations">
    <p>Chargement des conversations...</p>
</div>

<div id="chat" style="display:none;">
    <h3 id="chat-with"></h3>
    <div id="messages"></div>
    <div>
        <input type="text" id="message" placeholder="Votre message" style="width: 300px;">
        <button onclick="sendMessage()">Envoyer</button>
    </div>
</div>

<script>
const currentUserId = <?= $_SESSION['user_id'] ?>;

function loadConversations() {
    fetch('api/messages.php?action=conversations')
        .then(res => res.json())
        .then(data => {
            const div = document.getElementById('conversations');
            if (data.error) {
                div.innerHTML = '<p>Erreur: ' + data.error + '</p>';
                return;
            }
            if (data.length === 0) {
                div.innerHTML = '<p>Aucune conversation</p>';
                return;
            }
            let html = '';
            data.forEach(conv => {
                html += `<div class="conversation" onclick="loadMessages(${conv.user_id})">
                            <strong>${conv.name}</strong><br>
                            <small>${conv.last_message || 'Nouveau message'}</small>
                        </div>`;
            });
            div.innerHTML = html;
        })
        .catch(err => {
            document.getElementById('conversations').innerHTML = '<p>Erreur: ' + err.message + '</p>';
        });
}

function loadMessages(userId) {
    document.getElementById('chat').style.display = 'block';
    fetch(`api/messages.php?with=${userId}`)
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.forEach(msg => {
                const isMe = (msg.sender_id == currentUserId);
                html += `<div class="message" style="text-align: ${isMe ? 'right' : 'left'}">
                            <strong>${isMe ? 'Moi' : 'Lui'}:</strong> ${msg.message}
                            <small style="color:#999">${new Date(msg.created_at).toLocaleTimeString()}</small>
                         </div>`;
            });
            document.getElementById('messages').innerHTML = html;
            document.getElementById('chat-with').innerHTML = `Conversation avec utilisateur #${userId}`;
            window.currentReceiver = userId;
        });
}

function sendMessage() {
    const msg = document.getElementById('message').value;
    if (!msg || !window.currentReceiver) return;
    
    fetch('api/messages.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            receiver_id: window.currentReceiver,
            message: msg
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('message').value = '';
            loadMessages(window.currentReceiver);
        } else {
            alert('Erreur: ' + (data.error || 'Envoi échoué'));
        }
    });
}

loadConversations();
</script>
</body>
</html>