const WSS_URL = 'wss://wss.bariatricistanbul.com.tr:9443';
let CURRENT_LEAD_ID = null;

const socket = new WebSocket(WSS_URL);

socket.onopen = () => {
    console.log("✅ WebSocket bağlantısı kuruldu");
};

socket.onmessage = function (event) {
    try {
        const msg = JSON.parse(event.data);

        if (msg.type === 'system') {
            console.log("💬 Sistem mesajı:", msg.message);
            return;
        }

        if (msg.type === 'reaction') {
            const msgEl = document.querySelector(`li[data-sid="${msg.message_sid}"]`);
            if (msgEl) {
                let r = msgEl.querySelector('.reaction');
                if (!r) {
                    r = document.createElement('div');
                    r.className = 'reaction';
                    msgEl.appendChild(r);
                }
                r.textContent = msg.reaction;
            }
        }

        if (msg.type === 'new_message') {
            const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const pid = msg.patient_id;

            // 🛑 Geçersiz mesaj ID kontrolü
            if (msg.id === null || msg.id === undefined) {
                console.warn("⚠️ Geçersiz mesaj ID’si geldi. Atlanıyor.", msg);
                return;
            }

            // 🔁 Duplicate mesaj kontrolü
            if (lastMessageIds[pid] === msg.id) return;

            lastMessageIds[pid] = msg.id;

            // 🎯 Aktif konuşmadaki mesaj
            if (parseInt(pid) === parseInt(CURRENT_LEAD_ID)) {
                CURRENT_LEAD_ID = pid;
                console.log("📨 Yeni mesaj (aktif):", msg.content);

                appendMessage(msg);

                $.post('https://api.bariatricistanbul.com.tr/work/read_messages', {
                    lead_id: CURRENT_LEAD_ID
                });

                bringLeadToTop(msg.patient_id);
                scrollToBottom();
                playNotificationSound();
            } else {
                // 🧾 Medya türü için fallback içerik
                let previewText = msg.content?.trim()
                    ? (msg.content.length > 50 ? msg.content.slice(0, 47) + "..." : msg.content)
                    : `[${msg.media_type || 'message'} received]`;

                const msgPreviewEl = document.querySelector(`#lead-${msg.patient_id} .lead-message`);
                if (msgPreviewEl) msgPreviewEl.textContent = previewText;

                const badgeEl = document.querySelector(`#lead-${msg.patient_id} .unread-badge`);
                if (badgeEl) {
                    let count = parseInt(badgeEl.textContent || "0");
                    badgeEl.textContent = count + 1;
                    badgeEl.classList.remove("d-none");
                }

                bringLeadToTop(msg.patient_id);
                playNotificationSound();
            }

            const timeEl = document.querySelector(`#lead-${msg.patient_id} .lead-meta p`);
            if (timeEl) timeEl.textContent = now;
        }

        if (msg.type === 'new_lead') {
            const lead = msg.lead;
            console.log("🟢 Yeni lead geldi:", lead);

            const li = document.createElement("li");
            li.className = "chat-item pe-1";
            li.dataset.id = lead.id;
            li.id = "lead-" + lead.id;

            const unreadCount = lead.last_unread_count || 0;
            const previewText = lead.message_preview?.length > 50
                ? lead.message_preview.slice(0, 47) + "..."
                : (lead.message_preview || '');

            li.innerHTML = `
              <a href="javascript:;" class="d-flex align-items-center">
                <figure class="mb-0 me-2">
                  <img src="https://www.bariatricistanbul.com.tr/images/user-default.jpg" class="img-xs rounded-circle" alt="user">
                  <div class="status online"></div>
                </figure>
                <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                  <div>
                    <p class="text-body fw-bolder">${lead.name}</p>
                    <p class="text-secondary fs-13px lead-message">${previewText}</p>
                  </div>
                  <div class="d-flex flex-column align-items-end lead-meta">
                    <p class="text-secondary fs-13px mb-1">${lead.time || ''}</p>
                    <span class="unread-badge">${unreadCount > 0 ? `${unreadCount}` : '0'}</span>
                  </div>
                </div>
              </a>
            `;

            const list = document.querySelector('#leadList');
            list.prepend(li);
            playNotificationSound();
            return;
        }
    } catch (err) {
        console.warn("❌ WebSocket mesaj hatası:", err);
    }
};