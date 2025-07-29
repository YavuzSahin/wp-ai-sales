function loadLeads() {
    fetch("https://api.bariatricistanbul.com.tr/work/list_leads")
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById("leadList");
            list.innerHTML = ""; // temizle

            if (!data || !Array.isArray(data.leads)) {
                console.error("Geçersiz veri yapısı:", data);
                return;
            }

            data.leads.forEach(lead => {
                const li = document.createElement("li");
                li.className = "chat-item pe-1";
                li.dataset.id = lead.id;
                li.id = "lead-" + lead.id;

                const unreadCount = lead.last_unread_count || 0;

                // ✅ Mesaj önizleme: boşsa "No message yet", uzunsa kısalt
                let msgPreview = lead.last_message ? lead.last_message.trim() : "";
                if (!msgPreview) {
                    msgPreview = "No message yet";
                } else if (msgPreview.length > 50) {
                    msgPreview = msgPreview.slice(0, 47) + "...";
                }

                li.innerHTML = `
                    <a href="javascript:;" class="d-flex align-items-center">
                        <figure class="mb-0 me-2">
                            <img src="https://www.bariatricistanbul.com.tr/images/user-default.jpg" class="img-xs rounded-circle" alt="user">
                            <div class="status online"></div>
                        </figure>
                        <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                            <div>
                                <p class="text-body fw-bolder">${lead.name}</p>
                                <p class="text-secondary fs-13px lead-message">${msgPreview}</p>
                            </div>
                            <div class="d-flex flex-column align-items-end lead-meta">
                                <p class="text-secondary fs-13px mb-1">${lead.last_interaction_at || ''}</p>
                                <span class="unread-badge">${unreadCount > 0 ? `${unreadCount}` : '0'}</span>
                            </div>
                        </div>
                    </a>
                `;

                list.appendChild(li);
            });
        })
        .catch(err => {
            console.error("❌ Lead listesi alınamadı:", err);
        });
}



let currentLoadedLead = null;
let lastMessageId = 0;
let pickerVisible = false;

const trigger = document.getElementById('emojiTrigger');
const pickerContainer = document.getElementById('emoji-picker');
const input = document.getElementById('chatForm');

const picker = new EmojiMart.Picker({
    onEmojiSelect: (emoji) => {
        input.value += emoji.native;
    },
    style: { position: 'absolute', zIndex: 100 },
    theme:'light'
});


function loadMessages(leadId) {
    if (currentLoadedLead === leadId) return;

    currentLoadedLead = leadId;
    fetch(`https://api.bariatricistanbul.com.tr/work/load_messages?lead_id=${leadId}`)
        .then(response => response.json())
        .then(messages => {
            const container = document.querySelector(".chat-body ul.messages");
            container.innerHTML = "";

            messages.forEach(msg => {
                const sender = msg.way === 1 ? 'me' : 'friend';
                const avatar = sender === 'me' ? 'logo-social' : 'user-default';
                const agentName = msg.agent_name || 'System';

                let bubbleContent = '';

                if (msg.media_url) {
                    if (msg.media_type === 'image') {
                        bubbleContent += `<img src="${msg.media_url}" class="img-fluid rounded mb-2" style="max-width: 200px;" alt="image">`;
                    } else if (msg.media_type === 'pdf') {
                        bubbleContent += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-file-pdf me-1"></i> PDF Görüntüle
                        </a><br>`;
                    }
                }

                if (msg.message) {
                    bubbleContent += `<p>${msg.message}</p>`;
                }

                const li = document.createElement("li");
                li.className = `message-item ${sender}`;
                li.setAttribute('data-id', msg.id);
                li.innerHTML = `
                    <img src="https://www.bariatricistanbul.com.tr/images/${avatar}.jpg" class="img-xs rounded-circle" alt="avatar">
                    <div class="content">
                        <div class="message">
                            <div class="bubble">${bubbleContent}</div>
                            <span><small class="text-muted">${sender === 'me' ? `${agentName} | ` : ''}${msg.time}</small></span>
                        </div>
                    </div>
                `;

                container.appendChild(li);
            });

            lastMessageId = messages[messages.length - 1]?.id || 0;
            scrollToBottom();
        })
        .catch(err => console.error("Mesajlar yüklenemedi:", err));
}



/*
function checkNewMessages() {
    if (!activeLeadId) {
        console.warn("Aktif lead yok. Kontrol yapılmadı.");
        return;
    }

    $.post('https://api.bariatricistanbul.com.tr/work/check_new_messages', {
        lead_id: activeLeadId,
        last_id: lastMessageId
    }, function(response) {
        if (response.status === 'ok' && response.messages.length > 0) {
            response.messages.forEach(msg => {
                // Sadece yeni mesajları ekle
                if (parseInt(msg.id) > lastMessageId) {
                    appendMessage(msg);
                    lastMessageId = msg.id; // 🧠 En son mesaj ID’yi güncelle
                }
            });

            scrollToBottom();
            playNotificationSound();
        }
    });
}
*/
function checkNewMessages() {
    if (!activeLeadId) {
        console.log("Aktif lead yok. Kontrol yapılmadı.");
        return;
    }

    const lastId = $(".messages li").last().data("id") || 0;
    $.post('https://api.bariatricistanbul.com.tr/work/check_new_messages', {
        lead_id: activeLeadId,
        last_id: lastMessageId
    }, function(response) {
        if (response.status === 'ok' && response.messages && response.messages.length > 0) {
            response.messages.forEach(msg => {
                if (parseInt(msg.patient_id) === parseInt(activeLeadId)) {
                    appendMessage(msg);
                    lastMessageId = msg.id; // En son mesaj ID'yi güncelle
                }
            });
            playNotificationSound();
        }
    }).fail(function(err) {
        console.error("Yeni mesaj kontrolü hatası:", err);
    });
}


let audioReady = false;
let notificationAudio = null;

// Kullanıcı etkileşimini bir kez dinle
document.addEventListener('click', () => {
    if (!notificationAudio) {
        notificationAudio = new Audio('https://www.bariatricistanbul.com/crm/whatsapp/sounds/bip2.mp3');
    }

    notificationAudio.play().then(() => {
        notificationAudio.pause();
        notificationAudio.currentTime = 0;
        audioReady = true;
        console.log("🔊 Ses çalmaya hazır.");
    }).catch((err) => {
        console.warn("🔇 Ses ilk tetiklemede çalınamadı:", err.message);
    });
}, { once: true }); // sadece ilk tıklamada çalışsın

// Bildirim sesi fonksiyonu
function playNotificationSound() {
    if (!audioReady) {
        console.log("⏳ Ses henüz izinli değil.");
        return;
    }

    if (!notificationAudio) {
        notificationAudio = new Audio('https://www.bariatricistanbul.com/crm/whatsapp/sounds/bip2.mp3');
    }

    notificationAudio.play().catch(err => {
        console.warn("Bildirim sesi çalınamadı:", err.message);
    });
}


const unreadCounts = new Map();

function increaseBadge(patientId) {
    const leadEl = document.querySelector(`#lead-${patientId}`);
    if (!leadEl) return;

    const badgeEl = leadEl.querySelector(".unread-badge");
    let count = unreadCounts.get(patientId) || 0;
    count += 1;
    unreadCounts.set(patientId, count);

    if (badgeEl) {
        badgeEl.textContent = count;
        badgeEl.classList.remove("d-none");
    } else {
        const target = leadEl.querySelector(".d-flex.flex-column.align-items-end");
        if (target) {
            const badge = document.createElement("div");
            badge.className = "badge rounded-pill bg-primary ms-auto unread-badge";
            badge.textContent = count;
            target.appendChild(badge);
        }
    }
}

function resetBadge(leadId) {
    const badgeEl = document.querySelector(`#lead-${leadId} .unread-badge`);
    if (badgeEl) {
        badgeEl.textContent = '0';
        badgeEl.classList.add('d-none');
    }
}

function appendMessage(msg) {
    // DOM'da bu ID'de mesaj varsa tekrar eklemeyelim
    const existing = document.querySelector(`.messages li[data-id="${msg.id}"]`);
    if (existing) {
        console.warn("🔁 Zaten mevcut mesaj:", msg.id);
        return;
    }

    const sender = msg.way === 1 ? 'me' : 'friend';
    const time = msg.time || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const agentName = sender === 'me' ? (msg.agent_name || '') : '';
    const avatar = sender === 'me' ? 'logo-social' : 'user-default';

    let messageContent = '';

    // Medya varsa
    if (msg.media_url && msg.media_type) {
        if (msg.media_type === 'image') {
            messageContent += `<img src="${msg.media_url}" class="img-fluid rounded mb-2" style="max-width:200px;" alt="image">`;
        } else if (msg.media_type === 'pdf') {
            messageContent += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-danger btn-sm mb-2"><i class="fas fa-file-pdf me-1"></i>PDF</a><br>`;
        } else {
            messageContent += `<a href="${msg.media_url}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">Dosya</a><br>`;
        }
    }

    // Metin varsa ekle
    if (msg.message && msg.message.trim() !== '') {
        messageContent += `<p>${msg.message}</p>`;
    }

    // Eğer hem medya hem mesaj yoksa, gösterme
    if (messageContent.trim() === '') {
        console.warn('⛔ Boş mesaj içerik atlandı:', msg.id);
        return;
    }

    const messageHtml = `
        <li class="message-item ${sender}" data-id="${msg.id}">
            <img src="https://www.bariatricistanbul.com.tr/images/${avatar}.jpg" class="img-xs rounded-circle" alt="avatar">
            <div class="content">
                <div class="message">
                    <div class="bubble">
                        ${messageContent}
                    </div>
                    <span><small class="text-muted">${agentName ? `${agentName} | ` : ''}${time}</small></span>
                </div>
            </div>
        </li>`;

    document.querySelector('ul.messages').insertAdjacentHTML('beforeend', messageHtml);
    lastMessageId = Math.max(lastMessageId, msg.id);
    scrollToBottom();
}



function scrollToBottom() {
    const container = document.querySelector(".chat-body .messages");

    // Tüm görselleri kontrol et
    const images = container.querySelectorAll("img");
    let loadedCount = 0;

    if (images.length === 0) {
        container.scrollTop = container.scrollHeight;
        return;
    }

    images.forEach(img => {
        // Görsel zaten yüklüyse say
        if (img.complete) {
            loadedCount++;
            if (loadedCount === images.length) {
                container.scrollTop = container.scrollHeight;
            }
        } else {
            // Yüklenince say
            img.onload = () => {
                loadedCount++;
                if (loadedCount === images.length) {
                    container.scrollTop = container.scrollHeight;
                }
            };
        }
    });
}

function checkUnreadBadges() {
    fetch('https://api.bariatricistanbul.com.tr/work/check_unreads')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                data.updated_leads.forEach(lead => {
                    const badge = document.querySelector(`#lead-${lead.id} .unread-badge`);
                    if (badge) {
                        badge.classList.remove("d-none");
                        badge.textContent = lead.unread > 0 ? lead.unread : '';
                    }
                });
            }
        })
        .catch(err => console.error("❌ Okunmamış mesaj kontrol hatası:", err));
}

function bringLeadToTop(leadId) {
    console.log('bringLeadToTop called');
    const $allLeads = $('#leadList .chat-item');
    let maxOrder = 0;

    $allLeads.each(function () {
        const order = parseInt($(this).css('order')) || 0;
        if (order > maxOrder) maxOrder = order;
    });

    const $target = $('#lead-' + leadId);
    if ($target.length) {
        $target.css({
            order: maxOrder - 1,
            opacity: 0.5,
            transform: 'scale(0.96)',
            transition: 'all 0.3s ease'
        });

        setTimeout(() => {
            $target.css({
                opacity: 1,
                transform: 'scale(1)'
            });
        }, 300);
    }
}




$('#chatForm').on('keypress', function (e) {
    if (e.which === 13 && !e.shiftKey) {
        e.preventDefault(); // Sayfanın yenilenmesini engelle
        $('#sendMessageBtn').click(); // Gönder butonunu tetikle
    }
});
/*
$('#sendMessageBtn').on('click', function () {
    const text          = $('#chatForm').val().trim();
    const leadId        = $('#leadIdInput').val();
    const agentId       = $('#agentId').val();
    const mediaFileUrl  = $('#mediaFileUrl').val();
    const mediaFileType = $('#mediaFileType').val();

    if (text === '') return;

    // Aktif lead ID'si global tanımlı olduğunu varsayıyoruz
    console.log(leadId);
    if (!leadId) {
        alert('Lütfen bir lead seçin.');
        return;
    }

    $.ajax({
        url: 'https://api.bariatricistanbul.com.tr/work/send_message',
        method: 'POST',
        data: {
            lead_id: leadId,
            text: text,
            agent_id: agentId,
            media_url: mediaFileUrl,
            media_type: mediaFileType
        },
        success: function (response) {
            if (response.status === 'ok') {
                // Mesajı ekle
                let message_id = response.message_id;
                const now = new Date();
                const agentName = response.agentName || '';
                const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const newMessage = `
                    <li class="message-item me" data-id="${message_id}">
                        <img src="https://www.bariatricistanbul.com.tr/images/logo-social.jpg" class="img-xs rounded-circle" alt="avatar">
                        <div class="content">
                            <div class="message">
                                <div class="bubble">
                                    <p>${text}</p>
                                </div>
                                <span><small class="text-muted">${agentName} | ${time}</small></span>
                            </div>
                        </div>
                    </li>`;

                $('.messages').append(newMessage);
                $('#chatForm').val('');
                lastMessageId = response.message_id;
                closeEmojiPicker();

                document.getElementById('chatForm').value = '';
                document.getElementById('mediaFileUrl').value = '';
                document.getElementById('mediaFileType').value = '';

                // Scroll to last message
                scrollToBottom();
            } else {
                alert('Mesaj gönderilemedi.');
            }
        },
        error: function () {
            alert('Sunucuya bağlanılamadı.');
        }
    });
});
*/
$('#sendMessageBtn').on('click', function () {
    const text          = $('#chatForm').val().trim();
    const leadId        = $('#leadIdInput').val();
    const agentId       = $('#agentId').val();
    const mediaFileUrl  = $('#mediaFileUrl').val().trim();
    const mediaFileType = $('#mediaFileType').val().trim();

    if (!leadId) {
        alert('Lütfen bir lead seçin.');
        return;
    }

    // Eğer hem mesaj hem medya yoksa gönderme
    if (text === '' && mediaFileUrl === '') {
        alert('Mesaj veya medya eklemelisiniz.');
        return;
    }

    $.ajax({
        url: 'https://api.bariatricistanbul.com.tr/work/send_message',
        method: 'POST',
        data: {
            lead_id: leadId,
            text: text,
            agent_id: agentId,
            media_url: mediaFileUrl,
            media_type: mediaFileType
        },
        success: function (response) {
            if (response.status === 'ok') {
                const message_id = response.message_id;
                const now = new Date();
                const agentName = response.agentName || '';
                const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                // Mesaj HTML'i oluştur
                let messageContent = '';

                // Eğer medya varsa HTML'e medya önizleme olarak ekle
                if (mediaFileUrl !== '') {
                    if (mediaFileType === 'image') {
                        messageContent += `<img src="${mediaFileUrl}" class="img-fluid rounded mb-2" style="max-width: 200px;" alt="image">`;
                    } else if (mediaFileType === 'pdf') {
                        messageContent += `<a href="${mediaFileUrl}" target="_blank" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-file-pdf me-1"></i> PDF Görüntüle</a><br>`;
                    }
                }

                // Metin varsa ekle
                if (text !== '') {
                    messageContent += `<p>${text}</p>`;
                }

                const newMessage = `
                    <li class="message-item me" data-id="${message_id}">
                        <img src="https://www.bariatricistanbul.com.tr/images/logo-social.jpg" class="img-xs rounded-circle" alt="avatar">
                        <div class="content">
                            <div class="message">
                                <div class="bubble">
                                    ${messageContent}
                                </div>
                                <span><small class="text-muted">${agentName} | ${time}</small></span>
                            </div>
                        </div>
                    </li>`;

                $('.messages').append(newMessage);

                // Formu temizle
                $('#chatForm').val('');
                $('#mediaFileUrl').val('');
                $('#mediaFileType').val('');

                // Emoji picker'ı kapat
                if (typeof closeEmojiPicker === 'function') closeEmojiPicker();

                // Scroll
                scrollToBottom();

                // Seçilen medya önizleme alanı varsa, onu da temizleyebilirsin
                $('#mediaPreview').html('');
                $('#mediaPreview').hide();
            } else {
                alert('❌ Mesaj gönderilemedi: ' + (response.message || 'Sunucu hatası.'));
            }
        },
        error: function () {
            alert('⚠️ Sunucuya bağlanılamadı.');
        }
    });
});

function closeEmojiPicker() {
    const pickerContainer = document.getElementById('emoji-picker');
    pickerContainer.innerHTML = '';  // DOM'dan kaldır
    pickerVisible = false;           // Durumu güncelle
}
$(document).on('click', '.chat-item', function () {
    activeLeadId = $(this).data('id'); // ✅ Lead ID'yi alıyoruz
    //lastMessageId = 0;
    // HTML'e hidden input olarak da eklemek istiyorsan:
    $("#leadIdInput").val(activeLeadId); // varsa

    loadMessages(activeLeadId); // mesajları yükle

    // 1. İsim bilgisi
    const name = $(this).find("p.fw-bolder").text().trim();
    $('.chat-header figure img').attr("src", "https://www.bariatricistanbul.com.tr/images/user-default.jpg");
    $('.chat-header figure .status').removeClass("offline").addClass("online");
    $('.chat-header div > p').first().text(name);

    // 2. Durum veya son etkileşim bilgisi
    const statusText = $(this).find(".lead-meta p").text().trim() || "Son görülme yok";
    $('.chat-header div > p.text-secondary').text(statusText);

    console.log("Seçilen lead ID:", activeLeadId);
    console.log('last message id:'+lastMessageId);
    document.querySelector(`#lead-${activeLeadId}`).classList.add("active");
    resetBadge(activeLeadId);

    $.post('https://api.bariatricistanbul.com.tr/work/read_messages', {
        lead_id: activeLeadId
    }, function (res) {
        console.log("📩 Okundu bilgisi güncellendi:", res);
    });

    $('#chat-container').show();
});




document.addEventListener("DOMContentLoaded", function () {
    loadLeads();
    setInterval(checkNewMessages, 3000);
    setInterval(checkUnreadBadges, 10000);


    trigger.addEventListener('click', () => {
        if (!pickerVisible) {
            pickerContainer.innerHTML = '';
            pickerContainer.appendChild(picker);
            pickerVisible = true;
        } else {
            pickerContainer.innerHTML = '';
            pickerVisible = false;
        }
    });


});





$('.media-item').on('click', function() {
    const url = $(this).data('url');
    const type = $(this).data('type'); // 'image' | 'pdf' | 'video'

    $('#mediaFileUrl').val(url);
    $('#mediaFileType').val(type);

    // İsteğe bağlı: Kullanıcıya görsel onay gösterebilirsin
    //$('#chatForm').val("📎 Dosya gönderiliyor..."); // örnek preview
});

document.querySelectorAll('.media-item').forEach(btn => {
    btn.addEventListener('click', function () {
        const url = this.dataset.url;
        const type = this.dataset.type;

        document.getElementById('mediaFileUrl').value = url;
        document.getElementById('mediaFileType').value = type;

        let previewHTML = '';
        if (type === 'image') {
            previewHTML = `<div class="media-preview-content">
                <img src="${url}" class="img-fluid rounded" style="max-height: 200px;">
                <button class="btn btn-sm btn-danger mt-1" onclick="clearMedia()">Kaldır</button>
            </div>`;
        } else if (type === 'pdf') {
            previewHTML = `<div class="media-preview-content">
                <a href="${url}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-file-pdf me-1"></i> PDF Dosyasını Aç</a>
                <button class="btn btn-sm btn-danger mt-1" onclick="clearMedia()">Kaldır</button>
            </div>`;
        }

        document.getElementById('mediaPreview').innerHTML = previewHTML;
        $('#mediaPreview').show();

        // Offcanvas'ı kapat
        const offcanvasEl = document.getElementById('offcanvasFiles');
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
        bsOffcanvas.hide();
    });
});
function clearMedia() {
    document.getElementById('mediaFileUrl').value = '';
    document.getElementById('mediaFileType').value = '';
    document.getElementById('mediaPreview').innerHTML = '';
    $('#mediaPreview').hide();
}


$(function() {
    $("#leadListDiv").niceScroll();
});