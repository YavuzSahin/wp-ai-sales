// frontend.js – Tüm sistemi yöneten ana dosya

// 🧠 Global durum
const state = {
    activeLeadId: null,
    lastMessageId: 0,
    agentId: typeof CURRENT_AGENT_ID !== 'undefined' ? CURRENT_AGENT_ID : 1,
    ws: null,
    audio: null,
    audioAllowed: false
};

// 🧱 DOM referansları
const leadList = document.getElementById("leadList");
const messageList = document.querySelector(".messages");
const chatInput = document.getElementById("chatForm");
const sendBtn = document.getElementById("sendMessageBtn");
const mediaUrlInput = document.getElementById("mediaFileUrl");
const mediaTypeInput = document.getElementById("mediaFileType");
const leadIdInput = document.getElementById("leadIdInput");
const agentIdInput = document.getElementById("agentId");
const chatContainer = document.querySelector("#chat-container"); // ✅ chat-container ID ile referans

// 🔔 Sesli uyarı
function playNotificationSound() {
    if (state.audioAllowed) {
        if (!state.audio) {
            state.audio = new Audio("https://www.bariatricistanbul.com/crm/whatsapp/sounds/bip2.mp3");
        }
        state.audio.play().catch(e => console.warn("🔇 Ses çalamadı:", e));
    }
}

// 🔌 WebSocket başlat
function initWebSocket() {
    state.ws = new WebSocket("wss://wss.bariatricistanbul.com.tr:9443");
    state.ws.onopen = () => console.log("✅ WebSocket bağlandı");
    state.ws.onmessage = handleWebSocketMessage;
    state.ws.onerror = (e) => console.error("❌ WebSocket hatası", e);
}

// 📥 WebSocket mesaj yönetimi
function handleWebSocketMessage(event) {
    const msg = JSON.parse(event.data);
    console.log("📡 Yeni mesaj WebSocket ile geldi:", msg);

    if (msg.type === 'new_message') {
        const leadId = msg.patient_id;

        // Aynı ID varsa DOM'a ekleme
        if (!msg.id || document.querySelector(`li[data-id='${msg.id}']`)) return;

        if (leadId == state.activeLeadId) {
            appendMessage(msg);
            state.lastMessageId = msg.id;
            scrollToBottom();
            markAsRead(leadId);
            setTimeout(() => loadLeads(), 1000); // ✅ Okundu sonrası güncelleme
        } else {
            increaseBadge(leadId);
            updateLeadPreview(leadId, msg.content || '[Yeni mesaj]');
        }
        playNotificationSound();
        checkAgentAndTriggerAI(leadId);
    }

    if (msg.type === 'new_lead') {
        const lead = msg.lead;
        if ((state.agentId === 1 || lead.agent_id == state.agentId)) {
            drawLead(lead);
            playNotificationSound();
        }
    }
}

// 📄 Lead listesi yükle
function loadLeads() {
    fetch("https://api.bariatricistanbul.com.tr/work/list_leads")
        .then(res => res.json())
        .then(data => {
            leadList.innerHTML = "";
            (data.leads || []).forEach(drawLead);
        });
}

// 👤 Lead render
function drawLead(lead) {
    const existing = document.getElementById("lead-" + lead.id);
    if (existing) {
        updateLeadPreview(lead.id, lead.last_message || '');
        return;
    }
    const li = document.createElement("li");
    li.className = "chat-item pe-1";
    li.dataset.id = lead.id;
    li.id = "lead-" + lead.id;
    li.innerHTML = `
    <a href="javascript:;" class="d-flex align-items-center">
      <figure class="mb-0 me-2">
        <img src="https://www.bariatricistanbul.com.tr/images/user-default.jpg" class="img-xs rounded-circle">
        <div class="status online"></div>
      </figure>
      <div class="d-flex justify-content-between flex-grow-1 border-bottom">
        <div>
          <p class="text-body fw-bolder">${lead.name}</p>
          <p class="text-secondary fs-13px lead-message">${lead.last_message?.slice(0,47) || 'No message yet'}</p>
        </div>
        <div class="d-flex flex-column align-items-end lead-meta">
          <p class="text-secondary fs-13px mb-1">${lead.last_interaction_at || ''}</p>
          <span class="unread-badge">${lead.last_unread_count || 0}</span>
        </div>
      </div>
    </a>`;
    li.onclick = () => selectLead(lead.id);
    leadList.prepend(li);
}

// 🖱️ Lead seçildiğinde
function selectLead(leadId) {
    state.activeLeadId = leadId;
    leadIdInput.value = leadId;
    loadMessages(leadId);
    resetBadge(leadId);
    markAsRead(leadId);
    if (chatContainer) chatContainer.style.display = "block";
    setTimeout(() => loadLeads(), 1000);
}

// 📬 Mesajları yükle
function loadMessages(leadId) {
    fetch(`https://api.bariatricistanbul.com.tr/work/load_messages?lead_id=${leadId}`)
        .then(res => res.json())
        .then(messages => {
            messageList.innerHTML = "";
            (messages || []).forEach(msg => {
                appendMessage(msg);
                if (msg.id > state.lastMessageId) state.lastMessageId = msg.id;
            });
            scrollToBottom();
        });
}

// 🧠 AI tetiklemesi
function checkAgentAndTriggerAI(leadId) {
    fetch(`https://api.bariatricistanbul.com.tr/work/check_agent_status?lead_id=${leadId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'offline') {
                fetch('https://api.bariatricistanbul.com.tr/work/ai_response', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `lead_id=${leadId}`
                })
                    .then(res => res.json())
                    .then(ai => {
                        if (ai.status === 'ok') {
                            sendMessage(ai.reply, leadId, 0);
                        }
                    });
            }
        });
}

// 📤 Mesaj gönder
function sendMessage(text, leadId, agentId = state.agentId) {
    const mediaUrl = mediaUrlInput.value;
    const mediaType = mediaTypeInput.value;
    if (!text && !mediaUrl) return;

    const tempId = Date.now();
    state.lastMessageId = tempId;

    const data = new URLSearchParams({
        lead_id: leadId,
        text: text,
        agent_id: agentId,
        media_url: mediaUrl,
        media_type: mediaType
    });

    fetch('https://api.bariatricistanbul.com.tr/work/send_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data
    }).then(res => res.json()).then(() => {
        chatInput.value = "";
        mediaUrlInput.value = "";
        mediaTypeInput.value = "";
        scrollToBottom();

        appendMessage({
            id: tempId,
            message: text,
            media_url: mediaUrl,
            media_type: mediaType,
            way: 1,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            agent_name: 'You'
        });
    });
}

// 💬 Mesajı DOM'a ekle
function appendMessage(msg) {
    if (!msg || !messageList) return;
    if (document.querySelector(`li[data-id='${msg.id}']`)) return;
    const sender = msg.way === 1 ? 'me' : 'friend';
    const li = document.createElement("li");
    li.className = `message-item ${sender}`;
    li.dataset.id = msg.id;

    let content = '';
    if (msg.media_url && msg.media_type === 'image') {
        content += `<img src="${msg.media_url}" class="img-fluid mb-2" style="max-width:200px;">`;
    } else if (msg.media_url && msg.media_type === 'pdf') {
        content += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-danger btn-sm mb-2">PDF</a>`;
    }
    if (msg.message) {
        content += `<p>${msg.message}</p>`;
    }
    if (!content) return;

    li.innerHTML = `
    <img src="https://www.bariatricistanbul.com.tr/images/${sender === 'me' ? 'logo-social' : 'user-default'}.jpg" class="img-xs rounded-circle">
    <div class="content">
      <div class="message">
        <div class="bubble">${content}</div>
        <span><small class="text-muted">${msg.agent_name || ''} ${msg.time || ''}</small></span>
      </div>
    </div>
  `;

    messageList.appendChild(li);
    scrollToBottom();
}

// ✅ Badge & Scroll & Read
function resetBadge(leadId) {
    const el = document.querySelector(`#lead-${leadId} .unread-badge`);
    if (el) el.textContent = '0';
}

function markAsRead(leadId) {
    fetch('https://api.bariatricistanbul.com.tr/work/read_messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `lead_id=${leadId}`
    }).then(() => {
        const el = document.querySelector(`#lead-${leadId} .unread-badge`);
        if (el) el.textContent = '0';
    });
}

function scrollToBottom() {
    if (messageList) {
        messageList.scrollTop = messageList.scrollHeight;
    }
}

function increaseBadge(leadId) {
    const badge = document.querySelector(`#lead-${leadId} .unread-badge`);
    if (badge) {
        let count = parseInt(badge.textContent || '0');
        badge.textContent = count + 1;
    }
}

function updateLeadPreview(leadId, preview) {
    const leadItem = document.querySelector(`#lead-${leadId} .lead-message`);
    if (leadItem) {
        leadItem.textContent = preview.length > 47 ? preview.slice(0, 47) + '...' : preview;
    }
}

// 🚀 Başlat
window.addEventListener("DOMContentLoaded", () => {
    initWebSocket();
    loadLeads();

    document.body.addEventListener("click", () => {
        state.audioAllowed = true;
    }, { once: true });

    setInterval(() => {
        if (state.activeLeadId) {
            fetch('https://api.bariatricistanbul.com.tr/work/check_new_messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `lead_id=${state.activeLeadId}&last_id=${state.lastMessageId}`
            }).then(res => res.json())
                .then(data => {
                    (data.messages || []).forEach(msg => {
                        appendMessage(msg);
                        if (msg.id > state.lastMessageId) state.lastMessageId = msg.id;
                    });
                });
        }
    }, 3000);

    setInterval(() => {
        fetch("https://api.bariatricistanbul.com.tr/work/check_updates", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${state.agentId}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.new_lead && !document.getElementById("lead-" + data.new_lead.id)) {
                    drawLead({
                        id: data.new_lead.id,
                        name: data.new_lead.name,
                        last_message: data.new_lead.last_message,
                        last_interaction_at: data.new_lead.time,
                        last_unread_count: 1,
                        agent_id: state.agentId
                    });
                    playNotificationSound();
                }
            });
    }, 5000);

    chatInput?.addEventListener('keypress', function (e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendBtn.click();
        }
    });
});

sendBtn?.addEventListener("click", () => {
    const text = chatInput.value.trim();
    const mediaUrl = mediaUrlInput.value;
    if (!text && !mediaUrl) return;
    sendMessage(text, state.activeLeadId);
});
