<?php
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\ContactMessage;
use Netflie\WhatsAppCloudApi\Message\LocationMessage;
use Netflie\WhatsAppCloudApi\Message\Template\TemplateMessage;

class WhatsAppService
{
    private $whatsapp;

    public function __construct()
    {
        $setting = new \setting();
        $config = $setting->getAgentVariables();

        $this->whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => $config->WPPhone,
            'access_token' => $config->WPAccessT,
            'api_version' => $config->WPApiVer
        ]);
    }

    public function sendText(string $phoneNumber, string $text, bool $preview_url = true): ?string
    {
        try {
            $response = $this->whatsapp->sendTextMessage($phoneNumber, $text, $preview_url);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log("Text Send Error: " . $e->getMessage());
            return null;
        }
    }

    public function sendImage(string $phoneNumber, string $imageUrl, string $caption = ''): ?string
    {
        try {
            $link_id = new LinkID($imageUrl);
            $response = $this->whatsapp->sendImage($phoneNumber, $link_id, $caption);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Image Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendVideo(string $phoneNumber, string $videoUrl, string $caption = ''): ?string
    {
        try {
            $link_id = new LinkID($videoUrl);
            $response = $this->whatsapp->sendVideo($phoneNumber, $link_id, $caption);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Video Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendDocument(string $phoneNumber, string $docUrl, string $filename = ''): ?string
    {
        try {
            $link_id = new LinkID($docUrl);
            $response = $this->whatsapp->sendDocument($phoneNumber, $link_id, $filename);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Document Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendAudio(string $phoneNumber, string $audioUrl): ?string
    {
        try {
            $link_id = new LinkID($audioUrl);
            $response = $this->whatsapp->sendAudio($phoneNumber, $link_id);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Audio Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendSticker(string $phoneNumber, string $stickerUrl): ?string
    {
        try {
            $link_id = new LinkID($stickerUrl);
            $response = $this->whatsapp->sendSticker($phoneNumber, $link_id);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Sticker Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendContact(string $phoneNumber, ContactMessage $contact): ?string
    {
        try {
            $response = $this->whatsapp->sendContact($phoneNumber, $contact);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Contact Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendLocation(string $phoneNumber, LocationMessage $location): ?string
    {
        try {
            $response = $this->whatsapp->sendLocation($phoneNumber, $location);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Location Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendTemplate(string $phoneNumber, TemplateMessage $template): ?string
    {
        try {
            $response = $this->whatsapp->sendTemplateMessage($phoneNumber, $template);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Template Send Error: ' . $e->getMessage());
            return null;
        }
    }

    public function markAsRead($msgId){
        try {
            $response = $this->whatsapp->markMessageAsRead($msgId);
            $decoded = $response->decodedBody();
            return $decoded['messages'][0]['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Message Read Error: ' . $e->getMessage());
            return null;
        }
    }
}
