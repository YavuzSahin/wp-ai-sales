
<div class="layout">
    <div class="sidebar">
        <h2>Leads</h2>
        <input type="text" id="searchInput" placeholder="Ara..." class="search-box" />
        <div id="leadList" class="lead-list"></div>
    </div>
    <div class="chat-container">
        <div class="chat-top" id="chatTop">
            <div class="user-profile"><i class="far fa-user-circle"></i> <div class="name-title"></div></div>
            <div class="user-profile-detail">
                <div class="title">Name</div><div class="dot">:</div><div class="field nameValue"></div>
                <div class="title">Last Interaction</div><div class="dot">:</div><div class="field interactionValue"></div>
                <a href="" class="whatsapp" target="_blank"><i class="fab fa-whatsapp-square"></i> send message</a>
            </div>
        </div>
        <div class="chat-list" id="chatList"></div>
        <div id="templateContainer" style="display:none;"></div>
        <div id="emojiContainer" style="display:none;"></div>

        <form class="chat-input" id="sendMessage" enctype="multipart/form-data">
            <input type="hidden" id="template_id" name="template_id">
            <input type="hidden" id="content_variables" name="content_variables">
            <input type="text" id="message" placeholder="Mesaj yaz..." autocomplete="off">
            <button type="button" id="showTemplates" style="margin-left: 10px;"><i class="fal fa-sticky-note"></i> Templates</button>
            <label for="mediaFile" class="file-label"><i class="fal fa-file-pdf"></i> Files</label>
            <input type="file" id="mediaFile" name="media" accept="image/*,.pdf">
            <button type="button" id="emoji-btn" class="emoji-toggle"><i class="fas fa-smile"></i> Emojis</button>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="script.js?v=5"></script>
