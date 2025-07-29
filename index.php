<?php
ob_start();
session_start();
require_once 'api/vendor/autoload.php';
require_once 'api/controller/database.php';
require_once 'api/controller/setting.php';
require_once 'api/controller/leads.php';
require_once 'api/controller/agents.php';

$setting    = new setting();
$variable   = $setting->getAgentVariables();
if (!isset($_SESSION['biAgent_admin']) && !isset($_SESSION['biAgent_session'])) {header('location: '.$variable->site.'/login');}
$agent = agents::getAgent($_SESSION['biAgent_admin']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Bariatric Istanbul CRM">
    <meta name="author" content="Bariatric Istanbul">
    <title>Bariatric Istanbul Whatsapp</title>
    <link rel="stylesheet" href="https://use.typekit.net/lyu3lrq.css">
    <link rel="stylesheet" href="<?=$variable->site;?>/css/core/core.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="<?=$variable->site;?>/css/style.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/css/general.css">
    <link rel="stylesheet" href="<?=$variable->site;?>/style.css">
    <link rel="stylesheet" href="https://cdn.bariatricistanbul.com/assets/webfont/fontawesome/css/all.min.css">
    <link rel="shortcut icon" href="https://www.bariatricistanbul.com.tr/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-mart@3.0.1/css/emoji-mart.css">


    <style>
        #leadListDiv{min-height: 500px;max-height: 520px;overflow-y: scroll;overflow-x: hidden;}
        .chat-body{min-height: 500px;max-height: 520px;overflow: hidden;}
        .chat-wrapper .chat-content .chat-body .messages{padding: 15px 10px;list-style-type: none;min-height: 445px;max-height: 445px;overflow: scroll!important;}
    </style>
</head>
<body>
<audio id="notifySound" src="https://www.bariatricistanbul.com/crm/whatsapp/sounds/bip2.mp3" preload="auto"></audio>

<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content container-xxl">

            <div class="row chat-wrapper">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row position-relative">
                                <div class="col-lg-4 chat-aside border-end-lg">
                                    <div class="aside-content">
                                        <div class="aside-header">
                                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <figure class="me-2 mb-0">
                                                        <img src="https://www.bariatricistanbul.com.tr/images/logo-social.jpg" class="img-sm rounded-circle" alt="profile">
                                                        <div class="status online"></div>
                                                    </figure>
                                                    <div>
                                                        <h6><?=$agent->name;?></h6>
                                                        <p class="text-secondary fs-13px"><?=$setting->getAgentTitle($agent->role);?></p>
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <a type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="settings" class="lucide lucide-settings icon-lg text-secondary pb-3px"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </a>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        <a class="dropdown-item d-flex align-items-center" href="javascript:;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="eye" class="lucide lucide-eye icon-sm me-2"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg> <span class="">View Profile</span></a>
                                                        <a class="dropdown-item d-flex align-items-center" href="javascript:;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="edit-2" class="lucide lucide-edit-2 icon-sm me-2"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path></svg> <span class="">Edit Profile</span></a>
                                                        <a class="dropdown-item d-flex align-items-center" href="javascript:;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="aperture" class="lucide lucide-aperture icon-sm me-2"><circle cx="12" cy="12" r="10"></circle><path d="m14.31 8 5.74 9.94"></path><path d="M9.69 8h11.48"></path><path d="m7.38 12 5.74-9.94"></path><path d="M9.69 16 3.95 6.06"></path><path d="M14.31 16H2.83"></path><path d="m16.62 12-5.74 9.94"></path></svg> <span class="">Add status</span></a>
                                                        <a class="dropdown-item d-flex align-items-center" href="javascript:;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="settings" class="lucide lucide-settings icon-sm me-2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg> <span class="">Settings</span></a>
                                                        <a class="dropdown-item d-flex align-items-center" href="<?=$variable->site;?>/logout"><i data-lucide="log-out" class="icon-sm me-2"></i> <span class="">Logout</span></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <form class="search-form">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="searchForm" placeholder="Search here...">
                                                    <span class="input-group-text bg-transparent">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" class="lucide lucide-search cursor-pointer"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
                                                    </span>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="aside-body">
                                            <div class="tab-content mt-3">
                                                <p class="text-secondary mb-1">Recent chats</p>
                                                <div class="tab-pane fade show active ps ps--active-x ps--active-y" id="chats" role="tabpanel" aria-labelledby="chats-tab">
                                                    <div id="leadListDiv">
                                                        <ul class="list-unstyled chat-list px-1" id="leadList"></ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8 chat-content" id="chat-container" style="display: none;">
                                    <div class="chat-header border-bottom pb-2">
                                        <div class="d-flex justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="corner-up-left" id="backToChatList" class="lucide lucide-corner-up-left icon-lg me-2 ms-n2 text-secondary d-lg-none"><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path><path d="M9 14 4 9l5-5"></path></svg>
                                                <figure class="mb-0 me-2">
                                                    <img src="https://www.bariatricistanbul.com.tr/images/user-default.jpg" class="img-sm rounded-circle" alt="image">
                                                    <div class="status online"></div>
                                                    <div class="status online"></div>
                                                </figure>
                                                <div>
                                                    <p></p>
                                                    <p class="text-secondary fs-13px"></p>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center me-n1">
                                                <a class="me-3" type="button" data-bs-toggle="tooltip" data-bs-title="Start video call">
                                                    <i data-lucide="video" class="icon-lg text-secondary"></i>
                                                </a>
                                                <a class="me-0 me-sm-3" data-bs-toggle="tooltip" data-bs-title="Start voice call" type="button">
                                                    <i data-lucide="phone-call" class="icon-lg text-secondary"></i>
                                                </a>
                                                <a type="button" class="d-none d-sm-block" data-bs-toggle="tooltip" data-bs-title="Add to contacts">
                                                    <i data-lucide="user" class="icon-lg text-secondary"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chat-body ps ps--active-y">
                                        <ul class="messages"></ul>

                                        <div class="chat-footer d-flex">
                                            <div id="emoji-picker"></div>

                                        <div>
                                            <button type="button" class="btn border btn-icon rounded-circle me-2">
                                                <i data-lucide="smile" id="emojiTrigger" class="lucide lucide-smile text-secondary"></i>
                                            </button>
                                        </div>
                                        <div class="d-none d-md-block">
                                            <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiles" aria-controls="offcanvasFiles">
                                                <i data-lucide="paperclip" class="lucide lucide-paperclip text-secondary"></i>
                                            </button>
                                        </div>
                                        <div class="d-none d-md-block">
                                            <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Record you voice">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="mic" class="lucide lucide-mic text-secondary"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" x2="12" y1="19" y2="22"></line></svg>
                                            </button>
                                        </div>
                                        <form class="search-form flex-grow-1 me-2" method="post" enctype="multipart/form-data" id="chatFormWrapper">
                                            <input type="hidden" name="lead_id" id="leadIdInput" value="">
                                            <input type="hidden" name="agentId" id="agentId" value="<?=$_SESSION['biAgent_admin'];?>">
                                            <input type="hidden" id="mediaFileUrl" name="media_url" value="">
                                            <input type="hidden" id="mediaFileType" name="media_type" value="">
                                            <div class="input-group">
                                                <input type="text" class="form-control rounded-pill" id="chatForm" placeholder="Type a message">
                                            </div>
                                            <div id="mediaPreview" class="mt-2"></div>
                                        </form>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-icon rounded-circle" id="sendMessageBtn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="send" class="lucide lucide-send"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasFiles" aria-labelledby="offcanvasFilesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFilesLabel">Offcanvas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h6 class="mb-3">📎 Gönderilecek Dosyayı Seçin:</h6>
            <div class="row g-3">
                <!-- Örnek PDF -->
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary media-item" data-url="https://www.bariatricistanbul.com.tr/files/faq.pdf" data-type="pdf">
                        📄 Bilgilendirme PDF
                    </button>
                </div>

                <!-- Örnek Görsel -->
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary media-item" data-url="https://www.bariatricistanbul.com.tr/files/welcome.jpg" data-type="image">
                        🖼️ Welcome Image
                    </button>
                </div>

                <!-- Başka dosyalar -->
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary media-item" data-url="https://cdn.bariatricistanbul.com.tr/files/instructions.pdf" data-type="pdf">
                        📘 Hazırlık Talimatları
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="<?=$variable->site;?>/css/core/core.js"></script>
<script src="<?=$variable->cdn;?>/vendors/jquery/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.4.11/jquery.autocomplete.min.js" integrity="sha512-uxCwHf1pRwBJvURAMD/Gg0Kz2F2BymQyXDlTqnayuRyBFE7cisFCh2dSb1HIumZCRHuZikgeqXm8ruUoaxk5tA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?=$variable->cdn;?>/vendors/flatpickr/flatpickr.min.js"></script>
<script src="<?=$variable->cdn;?>/vendors/feather-icons/feather.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
    <script src="<?=$variable->cdn;?>/js/app.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js" integrity="sha512-zMfrMAZYAlNClPKjN+JMuslK/B6sPM09BGvrWlW+cymmPmsUT1xJF3P4kxI3lOh9zypakSgWaTpY6vDJY/3Dig==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>

    <script>
    const CURRENT_AGENT_ID = <?= json_encode($_SESSION['biAgent_admin']); ?>;
    lucide.createIcons();
    </script>
    <script src="<?=$variable->site;?>/js/frontend.js"></script>
</body>
</html>
