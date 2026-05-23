// Fonction pour échapper les caractères HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

let currentReceiverId = null;
let currentReceiverName = '';
let refreshInterval = null;

// Charger les conversations
function loadConversations() {
    fetch('api/messages.php?action=conversations')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('conversations-list');
            if (!container) return;
            
            if (!data || data.length === 0) {
                container.innerHTML = '<div style="padding: 1rem; text-align: center; color: #666;">Aucune conversation</div>';
                return;
            }
            
            let html = '';
            data.forEach(conv => {
                const activeClass = (currentReceiverId == conv.user_id) ? 'active' : '';
                html += `
                    <div class="conversation-item ${activeClass}" onclick="loadMessages(${conv.user_id}, '${escapeHtml(conv.name)}')">
                        <div class="conversation-name">${escapeHtml(conv.name)}</div>
                        <div class="conversation-last">${escapeHtml(conv.last_message || 'Nouvelle conversation')}</div>
                    </div>
                `;
            });
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur chargement conversations:', error);
            const container = document.getElementById('conversations-list');
            if (container) container.innerHTML = '<div style="padding: 1rem; text-align: center; color: red;">Erreur de chargement</div>';
        });
}

// Charger les messages avec un utilisateur
function loadMessages(userId, userName) {
    currentReceiverId = userId;
    currentReceiverName = userName;
    
    // Mettre à jour le header
    const chatHeader = document.getElementById('chat-header');
    if (chatHeader) {
        chatHeader.innerHTML = `💬 Conversation avec ${escapeHtml(userName)}`;
    }
    
    // Afficher la zone de saisie
    const inputArea = document.getElementById('chat-input-area');
    if (inputArea) {
        inputArea.style.display = 'flex';
    }
    
    // Charger les messages
    fetch(`api/messages.php?with=${userId}`)
        .then(response => response.json())
        .then(messages => {
            const container = document.getElementById('chat-messages');
            if (!container) return;
            
            if (!messages || messages.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;">Aucun message. Commencez la conversation !</div>';
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
            
            // Scroll en bas
            container.scrollTop = container.scrollHeight;
        })
        .catch(error => {
            console.error('Erreur chargement messages:', error);
            const container = document.getElementById('chat-messages');
            if (container) container.innerHTML = '<div style="text-align: center; padding: 2rem; color: red;">Erreur de chargement des messages</div>';
        });
}

// Formater l'heure
function formatTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

// Envoyer un message
function sendMessage() {
    const input = document.getElementById('message-input');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message || !currentReceiverId) {
        if (!currentReceiverId) alert('Sélectionnez une conversation d\'abord');
        return;
    }
    
    fetch('api/messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            receiver_id: currentReceiverId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentReceiverId, currentReceiverName);
        } else {
            alert('Erreur lors de l\'envoi du message');
        }
    })
    .catch(error => {
        console.error('Erreur envoi:', error);
        alert('Erreur de connexion');
    });
}

// Démarrer le rafraîchissement
function startRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        if (currentReceiverId) {
            loadMessages(currentReceiverId, currentReceiverName);
        }
    }, 3000);
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si on est sur la page chat
    if (document.getElementById('conversations-list')) {
        loadConversations();
        startRefresh();
        
        // Écouter le bouton d'envoi
        const sendBtn = document.getElementById('send-btn');
        if (sendBtn) {
            sendBtn.onclick = sendMessage;
        }
        
        // Écouter la touche Entrée
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
    }
});