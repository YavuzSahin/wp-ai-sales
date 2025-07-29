// Final frontend.js dosyası
// Tüm fonksiyonlar entegre edildi ve eksik kalanlar tamamlandı
// WebSocket, mesaj, medya, okundu bilgisi, reaction ve UI güncellemeleri dahil

// Tüm global değişkenler
let activeLeadId = null;
let lastMessageId = 0;
let audioReady = false;
let notificationAudio = null;
let pickerVisible = false;
const unreadCounts = new Map();

// Emoji picker
const trigger = document.getElementById('emojiTrigger');
const pickerContainer = document.getElementById('emoji-picker');
const input = document.getElementById('chatForm');
const picker = new EmojiMart.Picker({
    onEmojiSelect: emoji => input.value += emoji.native,
    style: { position: 'absolute', zIndex: 100 },
    theme: 'light'
});

// Ses izni tetikleyici
if (!notificationAudio) notificationAudio = new Audio('/crm/whatsapp/sounds/bip2.mp3');
document.addEventListener('click', () => {
    notificationAudio.play().then(() => {
        notificationAudio.pause();
        notificationAudio.currentTime = 0;
        audioReady = true;
    });
}, { once: true });

function playNotificationSound() {
    if (audioReady) notificationAudio.play().catch(() => {});
}

// WebSocket bağlantısı
const ws = new WebSocket('wss://wss.bariatricistanbul.com.tr/');
ws.onmessage = function (event) {
    const data = JSON.parse(event.data);
    if (data.type === 'new_message') {
        if (parseInt(data.patient_id) === parseInt(activeLeadId)) {
            appendMessage(data);
            markAsRead(data.message_sid);
        } else {
            increaseBadge(data.patient_id);
        }
        bringLeadToTop(data.patient_id);
        playNotificationSound();
    }
};

// Yeni mesaj ekleme fonksiyonu
function appendMessage(msg) {
    if (!msg || (!msg.message && !msg.media_url)) return;
    const sender = msg.way === 1 ? 'me' : 'friend';
    const avatar = sender === 'me' ? 'logo-social' : 'user-default';
    const time = msg.created_at ? msg.created_at.split(' ')[1].slice(0, 5) : '';
    let messageContent = '';

    if (msg.media_url) {
        if (msg.media_type === 'image') {
            messageContent += `<img src="${msg.media_url}" class="img-fluid rounded mb-2" style="max-width:200px;" alt="image">`;
        } else if (msg.media_type === 'pdf') {
            messageContent += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">PDF</a><br>`;
        } else {
            messageContent += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">Dosya</a><br>`;
        }
    }
    if (msg.message) messageContent += `<p>${msg.message}</p>`;
    if (msg.reaction) messageContent += `<p><i class='fas fa-heart text-danger'></i> ${msg.reaction}</p>`;

    const messageHTML = `
    <li class="message-item ${sender}" data-id="${msg.id || 0}">
      <img src="https://www.bariatricistanbul.com.tr/images/${avatar}.jpg" class="img-xs rounded-circle" alt="avatar">
      <div class="content">
        <div class="message">
          <div class="bubble">${messageContent}</div>
          <span><small class="text-muted">${msg.agent_name || ''} | ${time}</small></span>
        </div>
      </div>
    </li>`;

    document.querySelector('ul.messages').insertAdjacentHTML('beforeend', messageHTML);
    lastMessageId = Math.max(lastMessageId, msg.id || 0);
    scrollToBottom();
}

// Scroll fonksiyonu
function scrollToBottom() {
    const container = document.querySelector(".chat-body .messages");
    container.scrollTop = container.scrollHeight;
}

function bringLeadToTop(leadId) {
    const item = document.querySelector(`#lead-${leadId}`);
    if (item) {
        item.parentNode.prepend(item);
        item.classList.add('shake');
        setTimeout(() => item.classList.remove('shake'), 1000);
    }
}

function increaseBadge(patientId) {
    const el = document.querySelector(`#lead-${patientId} .unread-badge`);
    if (el) {
        let count = parseInt(el.textContent || '0');
        el.textContent = count + 1;
        el.classList.remove('d-none');
    }
}

function markAsRead(messageSid) {
    // Okundu bilgisi için mesaj SID üzerinden API isteği
    fetch('https://api.bariatricistanbul.com.tr/work/mark_as_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sid: messageSid })
    }).then(res => res.json()).then(res => {
        if (res.status !== 'ok') console.warn("❌ Okundu bilgisi gönderilemedi");
    }).catch(() => console.error("❌ Okundu bilgisi hatası"));
}

// Emoji picker kontrolü
trigger?.addEventListener('click', () => {
    pickerVisible = !pickerVisible;
    pickerContainer.innerHTML = pickerVisible ? '' : pickerContainer.innerHTML;
    if (pickerVisible) pickerContainer.appendChild(picker);
});