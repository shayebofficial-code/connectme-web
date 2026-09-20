/**
 * ConnectMe - Client-Side Interactive Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    initPhotoPreview();
    initChatEngine();
    initDiscoverSwipe();
});

/**
 * Image Upload Preview Handler
 */
function initPhotoPreview() {
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    photoPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Discover Page - Like / Pass API Integration
 */
function sendProfileReaction(toUserId, action) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/api/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken || ''
        },
        body: new URLSearchParams({
            'to_user_id': toUserId,
            'action': action,
            'csrf_token': csrfToken || ''
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.is_match) {
                showMatchModal(data.matched_user);
            }
            // Animate card removal & load next profile
            removeCurrentCardAndNext();
        } else {
            alert(data.error || 'Action failed');
        }
    })
    .catch(err => console.error('Reaction Error:', err));
}

function removeCurrentCardAndNext() {
    const card = document.querySelector('.profile-card');
    if (card) {
        card.style.transform = 'translateY(-100px) opacity(0)';
        card.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            if (typeof loadNextDiscoverCard === 'function') {
                loadNextDiscoverCard();
            } else {
                window.location.reload();
            }
        }, 300);
    }
}

/**
 * Match Trigger Popup Modal
 */
function showMatchModal(user) {
    const modalHtml = `
        <div id="matchModal" class="modal-overlay" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.9); z-index:9999; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
            <div style="background:var(--surface); border:1px solid var(--primary); padding:30px; border-radius:24px; text-align:center; max-width:380px; width:90%;">
                <h2 style="font-size:2rem; color:var(--primary); margin-bottom:10px;">🎉 It's a Match!</h2>
                <p style="color:var(--text-secondary); margin-bottom:20px;">You and <strong>${escapeHtml(user.name)}</strong> liked each other!</p>
                <img src="/uploads/profiles/${escapeHtml(user.photo)}" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); margin-bottom:20px;">
                <div style="display:flex; gap:10px; justify-content:center;">
                    <a href="/user/chat.php?match_id=${user.user_id}" class="btn-primary-custom">Send Message</a>
                    <button onclick="document.getElementById('matchModal').remove()" class="btn-secondary-custom">Keep Swiping</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

/**
 * Real-time AJAX Chat Polling Engine
 */
let chatPollInterval = null;

function initChatEngine() {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const messagesContainer = document.getElementById('messagesContainer');
    const receiverIdInput = document.getElementById('receiverId');

    if (!chatForm || !messagesContainer || !receiverIdInput) return;

    const receiverId = receiverIdInput.value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Scroll to bottom initially
    scrollToBottom(messagesContainer);

    // Form submit handler
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;

        fetch('/api/messages.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken || ''
            },
            body: new URLSearchParams({
                'action': 'send',
                'receiver_id': receiverId,
                'body': text,
                'csrf_token': csrfToken || ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                appendMessage(data.message, true);
                scrollToBottom(messagesContainer);
            } else {
                alert(data.error || 'Failed to send message');
            }
        });
    });

    // Start polling every 3 seconds for new messages
    chatPollInterval = setInterval(() => {
        fetch(`/api/messages.php?action=fetch&receiver_id=${receiverId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                messagesContainer.innerHTML = '';
                data.messages.forEach(msg => {
                    const isSent = msg.sender_id != receiverId;
                    appendMessage(msg, isSent);
                });
                scrollToBottom(messagesContainer);
            }
        })
        .catch(err => console.error('Polling Error:', err));
    }, 3000);
}

function appendMessage(msg, isSent) {
    const container = document.getElementById('messagesContainer');
    if (!container) return;

    const bubbleHtml = `
        <div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}">
            <div class="message-text">${escapeHtml(msg.body)}</div>
            <div class="message-time">${escapeHtml(msg.created_at || 'Just now')}</div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', bubbleHtml);
}

function scrollToBottom(el) {
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initDiscoverSwipe() {
    // Placeholder for swipe gesture extensions if needed
}
