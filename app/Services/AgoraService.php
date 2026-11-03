<?php

namespace App\Services;

use App\Models\Setting;
use Exception;

class AgoraService
{
    /**
     * Generate an Agora RTC Token.
     *
     * @param string $channelName
     * @param int|string $uid
     * @param int $role (1 for Publisher, 2 for Subscriber)
     * @param int $expireTimeInSeconds
     * @return string
     */
    public function generateToken($channelName, $uid, $role = 1, $expireTimeInSeconds = 3600)
    {
        $setting = Setting::first();
        $appID = $setting->agora_app_id;
        $appCertificate = $setting->agora_app_certificate;

        if (empty($appID) || empty($appCertificate)) {
            throw new Exception("Agora App ID or Certificate not configured.");
        }

        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return $this->buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
    }

    private function buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        // Assign appropriate role
        // Role_Publisher = 1: A host can both send and receive streams.
        // Role_Subscriber = 2: A subscriber can only receive streams.
        $role = ($role == 1) ? 1 : 2;
        
        $token = $this->generate($appID, $appCertificate, $channelName, $uid, $privilegeExpiredTs, $role);
        return $token;
    }

    private function generate($appID, $appCertificate, $channelName, $uid, $privilegeExpiredTs, $role)
    {
        $salt = rand(1, 99999999);
        $ts = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp() + 24 * 3600;
        $expiredTs = 0; 

        $m = $this->packContent($salt, $ts, $expiredTs, $channelName, $uid, $role, $privilegeExpiredTs);
        $val = hash_hmac('sha256', $m, $appCertificate, true);
        $signature = base64_encode($val);
        
        $content = array_merge(unpack("C*", "006" . $appID .  pack("V", $ts) . pack("V", $salt)), unpack("C*", $channelName), unpack("C*", pack("V", $uid)), unpack("C*", pack("V", $expiredTs)), unpack("C*", $signature));
        
        // Manual pack for the final token string
        $bytes = '';
        foreach($content as $byte){
            $bytes .= chr($byte);
        }
        
        return base64_encode($bytes);
    }

    private function packContent($salt, $ts, $expiredTs, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        // pack("V") is for 32-bit unsigned integer, little endian
        // pack("v") is for 16-bit unsigned integer, little endian
        
        $buffer = "";
        $buffer .= $this->packString($channelName);
        $buffer .= pack("V", $uid);
        $buffer .= pack("V", $role);
        $buffer .= pack("V", $privilegeExpiredTs);
        $buffer .= pack("V", $salt);
        $buffer .= pack("V", $ts);
        
        // Messages (privileges)
        // kJoinChannel = 1
        // kPublishAudioStream = 2
        // kPublishVideoStream = 3
        // kPublishDataStream = 4
        
        // We add privileges based on role. 
        // If Publisher (1), add all. If Subscriber (2), only JoinChannel.
        // However, standard buildTokenWithUid usually adds JoinChannel + PublishStream if publisher.
        
        $messages = [];
        $messages[1] = $privilegeExpiredTs; // kJoinChannel
        
        if ($role == 1) {
            $messages[2] = $privilegeExpiredTs; // kPublishAudioStream
            $messages[3] = $privilegeExpiredTs; // kPublishVideoStream
            $messages[4] = $privilegeExpiredTs; // kPublishDataStream
        }

        $buffer .= pack("v", count($messages));
        foreach ($messages as $key => $val) {
            $buffer .= pack("v", $key);
            $buffer .= pack("V", $val);
        }
        
        return $buffer;
    }

    private function packString($value)
    {
        return pack("v", strlen($value)) . $value;
    }
}
