const chatList = document.getElementById('chatList');
const leadList = document.getElementById('leadList');
let CURRENT_LEAD_ID = 2;

const socket = new WebSocket("wss://wss.bariatricistanbul.com.tr");

socket.onmessage = function (event) {
  const msg = JSON.parse(event.data);
  // Durum güncellemesi kontrolü
  if (msg.type === 'status_update' && msg.message_sid && msg.status) {
    const messageElement = document.querySelector(`[data-sid=\"${msg.message_sid}\"]`);
    if (messageElement) {
      const statusEl = messageElement.querySelector('.status-icon');
      if (statusEl) statusEl.innerHTML = getStatusIcon(msg.status);
    }
    return;
  }
  // ✅ Eğer mesaj aktif lead'e ait değilse DOM'a basma
  if (msg.patient_id && msg.patient_id != CURRENT_LEAD_ID) {
    // Mevcut unread badge’i bul
    const badge = document.querySelector(`#lead-${msg.patient_id} .unread-badge`);
    if (badge) {
      badge.textContent = parseInt(badge.textContent) + 1;
    } else {
      const leadMeta = document.querySelector(`#lead-${msg.patient_id} .lead-meta`);
      if (leadMeta) {
        const newBadge = document.createElement('span');
        newBadge.classList.add('unread-badge');
        newBadge.textContent = '1';
        leadMeta.appendChild(newBadge);
      }
    }
    if (msg.way == 1) {triggerNotification();}
    if (document.hidden) triggerNotification();
    return;
  }
  appendMessage(msg);
};
function triggerNotification() {
  const audio = document.getElementById('notifySound');
  if (audio) audio.play().catch(() => {});
  if (document.hidden) {
    titleInterval = setInterval(() => {
      document.title = document.title === "🔴 Yeni Mesaj!" ? originalTitle : "🔴 Yeni Mesaj!";
    }, 1000);
  }
}


function appendMessage(msg) {
    if (!msg.id) {
        msg.id = 'temp-' + Math.random().toString(36).substring(2, 10);
    }
    if (document.getElementById('msg-' + msg.id)) return;

    const isRight = msg.way == 0 || msg.way === "0";
    const div = document.createElement('div');
    div.classList.add('message', isRight ? 'right' : 'left');
    div.id = 'msg-' + msg.id;

    // ✅ SID eklendi
    if (msg.message_sid) {
        div.setAttribute('data-sid', msg.message_sid);
    }

    let mediaHtml = '';
    if (msg.media_url) {
        if (msg.media_url.endsWith('.pdf')) {
            mediaHtml = `<div style="margin-top:8px;"><a href="${msg.media_url}" target="_blank">📄 PDF Görüntüle</a><div class="clearfix"></div></div>`;
        } else {
            mediaHtml = `<div style="margin-top:8px;"><img src="${msg.media_url}" style="max-width: 200px; border-radius: 8px;" /><div class="clearfix"></div></div>`;
        }
    }

    // ✅ Status ikonu artık <span class="status-icon"> içinde
    let statusIcon = '';
    if (isRight) {
        if (msg.status === 'read') {
            statusIcon = '<span class="status-icon"><i class="fas fa-check-double" style="color:#25d366;"></i></span>';
        } else if (msg.status === 'delivered') {
            statusIcon = '<span class="status-icon"><i class="fas fa-check-double" style="color:#999;"></i></span>';
        } else {
            statusIcon = '<span class="status-icon"><i class="fas fa-check" style="color:#999;"></i></span>';
        }
    }

    let reactionHtml = '';
    if (msg.reaction) {
        reactionHtml = `<span class="reaction">${msg.reaction}</span>`;
    }

    div.innerHTML = `
        <div class="bubble">
            ${formatMessageText(msg.content || msg.message || '')}
            ${mediaHtml}
            ${reactionHtml}
        </div>
        <div class="info">
            ${msg.sender_name || (isRight ? 'Siz' : 'Hasta')} • ${formatDateTime(msg.created_at)} ${statusIcon}
        </div>`;

    chatList.appendChild(div);

    // 📸 Görsel varsa scroll'u beklet
    const img = div.querySelector('img');
    if (img) {
        img.onload = () => {
            chatList.scrollTop = chatList.scrollHeight;
        };
    } else {
        chatList.scrollTop = chatList.scrollHeight;
    }

    // 🔔 Uyarı sesi (sadece gelen mesajlarda)
    if (!isRight) {
        const audio = document.getElementById('notifySound');
        if (audio) audio.play().catch(e => console.warn("🔇 Ses engellendi:", e));
    }
}






function loadAllMessages() {
    fetch('https://api.bariatricistanbul.com.tr/work/check_whatsapp_messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ lead_id: CURRENT_LEAD_ID })
    })
        .then(res => res.json())
        .then(data => {
            chatList.innerHTML = '';
            if (data.status === 'ok') {
                data.messages.forEach(appendMessage);
            }
        });
        /*fetch('https://www.bariatricistanbul.com/crm/whatsapp/work/mark_messages_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ lead_id: CURRENT_LEAD_ID })
        });
    
        document.querySelector(`#lead-${CURRENT_LEAD_ID} .unread-badge`)?.remove();
         */
}

function clearUnread(leadId) {
    fetch('https://api.bariatricistanbul.com.tr/work/clear_unread', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ lead_id: leadId })
    });
    document.querySelector(`#lead-${CURRENT_LEAD_ID} .unread-badge`)?.remove();
}

function loadLeads() {
  fetch('https://api.bariatricistanbul.com.tr/work/list_leads')
    .then(res => res.json())
    .then(data => {
      const container = document.querySelector('.lead-list');
      container.innerHTML = '';

      data.leads.forEach(lead => {
        const div = document.createElement('div');
        div.classList.add('lead-item');
        div.id = `lead-${lead.id}`;

          let badge = '';
          if (lead.last_unread_count && lead.last_unread_count > 0) {
              badge = `<span class="unread-badge">${lead.last_unread_count}</span>`;
          }

        div.innerHTML = `<div class="lead-name">${lead.name}</div><div class="lead-meta">${badge}</div>`;
        div.addEventListener('click', () => {
          CURRENT_LEAD_ID = lead.id;
          loadAllMessages();
        });
        container.appendChild(div);
      });
    });
}


document.getElementById('sendMessage').addEventListener('submit', function(e) {
    e.preventDefault();
    const template_id       = document.getElementById('template_id');
    const content_variables = document.getElementById('content_variables');
    const input             = document.getElementById('message');
    const fileInput         = document.getElementById('mediaFile');
    const text              = input.value.trim();
    const templateid        = template_id.value.trim();
    const contentvariables  = content_variables.value.trim();
    const file = fileInput.files[0];

    if (!text && !file) return;

    const formData = new FormData();
    formData.append('lead_id', CURRENT_LEAD_ID);
    formData.append('message', text);
    if (templateid) formData.append('template_id', templateid);
    if (contentvariables) formData.append('content_variables', contentvariables);
    if (file) formData.append('media', file);

    fetch('https://api.bariatricistanbul.com.tr/work/send_message', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'ok') {
                appendMessage({ ...res.message, way: 0 });
                input.value = '';
                fileInput.value = '';
            }
        });
});


document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.lead-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? 'block' : 'none';
    });
});

document.addEventListener("readystatechange", function () {
    if (document.readyState === "interactive" || document.readyState === "complete") {
        loadAllMessages();
        loadLeads();
    }
});

document.getElementById('mediaFile').addEventListener('change', function () {
    const file = this.files[0];
    if (file && file.size > 2 * 1024 * 1024) {
        alert('⚠️ Dosya boyutu 2MB\'ı geçemez!');
        this.value = ''; // dosyayı sıfırla
    }
});



document.getElementById('showTemplates').addEventListener('click', function () {
    const container = document.getElementById('templateContainer');


    if (container.style.display === 'none') {
        const LEAD_LANGUAGE = 1; // Örnek: 1 İngilizce
        fetch('https://api.bariatricistanbul.com.tr/work/get_templates', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ language: LEAD_LANGUAGE })
        }).then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    const list = data.templates.map(tpl => `
            <div>
              <button type="button" class="select-template" data-id="${tpl.id}">
                ${tpl.label}
              </button>
            </div>
          `).join('');
                    container.innerHTML = list;
                    container.style.display = 'block';
                }
            });
    } else {
        container.style.display = 'none';
    }
});
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('select-template')) {
        const templateId = e.target.getAttribute('data-id');

        fetch('https://api.bariatricistanbul.com.tr/work/get_template_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: templateId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    const tpl = data.template;
                    const variables = JSON.parse(tpl.variables || '{}');

                    let html = `<form id="templateFillForm" class="templateFillForm" role="form">`;

                    Object.entries(variables).forEach(([key, label]) => {
                        html += `
                        <div class="form-group">
                          <label>${label}</label>
                          <input type="text" dirname="form-control" name="${key}" required />
                        </div>`;
                    });

                    html += `<div class="form-group right"><button type="submit">Mesajı Oluştur</button></form></div>`;

                    document.getElementById('templateContainer').innerHTML = html;

                    // Oluştur butonu tıklanınca mesajı oluştur
                    document.getElementById('templateFillForm').addEventListener('submit', function (e) {
                        e.preventDefault();
                        const values = Object.fromEntries(new FormData(this).entries());
                        let content = tpl.content;

                        Object.entries(values).forEach(([key, val]) => {
                            const regex = new RegExp(`{{\\s*${key}\\s*}}`, 'g');
                            content = content.replace(regex, val);
                        });

                        document.getElementById('message').value = content;
                        document.getElementById('template_id').value = tpl.template_id;
                        document.getElementById('content_variables').value = JSON.stringify(values);
                    });

                }
            });
    }
});


function getStatusIcon(status) {
    if (status === "read") {
        return '<i class="fas fa-check-double" style="color:blue;"></i>';
    } else if (status === "delivered") {
        return '<i class="fas fa-check-double" style="color:gray;"></i>';
    } else if (status === "sent") {
        return '<i class="fas fa-check" style="color:gray;"></i>';
    } else {
        return '';
    }
}



function formatMessageText(text) {
    if (!text) return '';

    return text
        .replace(/(?:\r\n|\r|\n)/g, '<br>')                                  // Satır sonları
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')                    // **kalın**
        .replace(/__(.*?)__/g, '<em>$1</em>')                                // __italik__
        .replace(/~~(.*?)~~/g, '<del>$1</del>')                              // ~~üstü çizili~~
        .replace(
            /((https?:\/\/|www\.)[^\s<]+)/g,
            url => {
                const href = url.startsWith('http') ? url : `https://${url}`;
                return `<a href="${href}" target="_blank">${url}</a>`;
            }
        );                                                                   // Linkler
}
function formatDateTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = date.getFullYear();
    const h = String(date.getHours()).padStart(2, '0');
    const i = String(date.getMinutes()).padStart(2, '0');
    return `${dd}-${mm}-${yyyy} ${h}:${i}`;
}

document.querySelector('.emoji-toggle').addEventListener('click', function () {
    const container = document.getElementById('emojiContainer');

    // Eğer gizliyse gösterelim (örn. display: none olabilir)
    if (container.style.display === 'none' || getComputedStyle(container).display === 'none') {
        container.style.display = 'block';
    }else{
        container.style.display = 'none';
    }

    // Toggle aktif/pasif durumu
    if (container.classList.contains('emoji-active')) {
        container.innerHTML = '';
        container.classList.remove('emoji-active');
        return;
    }

    const emojiList = ['😀','😎','🔥','💯','👍','🙏','❤️','🥳','🚀','🤖'];
    let html = '<div class="emoji-picker">';
    emojiList.forEach(emoji => {
        html += `<span class="emoji-item">${emoji}</span>`;
    });
    html += '</div>';

    container.innerHTML = html;
    container.classList.add('emoji-active');

    document.querySelectorAll('.emoji-item').forEach(el => {
        el.addEventListener('click', () => {
            const input = document.getElementById('message');
            input.value += el.textContent;
            input.focus();
        });
    });
});


document.addEventListener('click', function (e) {
    const item = e.target.closest('.lead-item');
    if (!item) return;

    // 🔄 Diğer tüm active class'larını kaldır
    document.querySelectorAll('.lead-item.active').forEach(el => el.classList.remove('active'));

    // ✅ Bu elemana active class'ı ekle
    item.classList.add('active');

    const textID    = item.id;
    const numberID  = textID.replace('lead-', '');

    $('.user-profile').attr('data-patientid', numberID);

    clearUnread(numberID);

    // 🔼 En üste taşı (eğer zaten üstte değilse)
   /* const parent = item.parentNode;
    if (parent.firstChild !== item) {
        parent.insertBefore(item, parent.firstChild);
    }
    */
});


$('.user-profile').on('click', function (){
    $(this).toggleClass('active');
    $('.user-profile-detail').toggleClass('active');
    let patientId = CURRENT_LEAD_ID;
    $.ajax({
        type        : "POST",
        url         : 'https://api.bariatricistanbul.com.tr/work/get_profile',
        data        : { id: patientId },
        success     : function (e) {
            $('.user-profile-detail .nameValue').html(e.leads.name);
            $('.user-profile .name-title').html(e.leads.name);
            $('.user-profile-detail .interactionValue').html(e.leads.last_interaction_at);
            $('.user-profile-detail a.whatsapp').prop('href', 'https://wa.me/'+e.leads.full_phone+'?text=Olá, *'+e.leads.name+'*');
        },
        dataType    : 'json'
    });
});

$('body').on('click', '.lead-item', function (){
    let numberID    = $(this).attr('id');
    numberID        = CURRENT_LEAD_ID;
    $('.user-profile-detail').removeClass('active');
    $.ajax({
        type        : "POST",
        url         : 'https://api.bariatricistanbul.com.tr/work/work/get_profile',
        data        : { id: numberID },
        success     : function (e) {
            $('.user-profile .name-title').html(e.leads.name);
            $('.user-profile-detail .nameValue').html(e.leads.name);
            $('.user-profile-detail .interactionValue').html(e.leads.last_interaction_at);
            $('.user-profile-detail a.whatsapp').prop('href', 'https://wa.me/'+e.leads.full_phone+'?text='+urlencode('Olá, *'+e.leads.name+'*'));
        },
        dataType    : 'json'
    });
});


