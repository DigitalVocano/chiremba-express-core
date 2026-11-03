<?php

namespace App\Helpers;

class Google2FA
{
    private static $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random Base32 secret key.
     *
     * @param int $secretLength
     * @return string
     */
    public static function generateSecretKey($secretLength = 16)
    {
        $secret = '';
        $validChars = self::$base32chars;
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify 6-digit TOTP code.
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy
     * @return bool
     */
    public static function verifyCode($secret, $code, $discrepancy = 1)
    {
        if (strlen($code) !== 6 || !is_numeric($code)) {
            return false;
        }

        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals((string)$calculatedCode, (string)$code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate 6-digit TOTP code for given time slice.
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get OTPAuth URL for QR code generation.
     *
     * @param string $label
     * @param string $holder
     * @param string $secret
     * @param string $issuer
     * @return string
     */
    public static function getOtpAuthUrl($label, $holder, $secret, $issuer = 'ChirembaExpress')
    {
        $account = rawurlencode($holder);
        $issuerName = rawurlencode($issuer);
        return "otpauth://totp/{$issuerName}:{$account}?secret={$secret}&issuer={$issuerName}";
    }

    /**
     * Generate Google Charts QR code image URL.
     *
     * @param string $otpAuthUrl
     * @param int $size
     * @return string
     */
    public static function getQrCodeUrl($otpAuthUrl, $size = 200)
    {
        $encodedUrl = urlencode($otpAuthUrl);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedUrl}";
    }

    /**
     * Decode Base32 string.
     *
     * @param string $secret
     * @return string
     */
    private static function base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars = self::$base32chars;
        $base32charsFlipped = array_flip(str_split($base32chars));

        $secret = strtoupper($secret);
        $secret = str_replace('=', '', $secret);
        $secretLength = strlen($secret);
        $binaryString = '';

        for ($i = 0; $i < $secretLength; $i = $i + 8) {
            $x = '';
            if (!isset($base32charsFlipped[$secret[$i]])) {
                return '';
            }
            for ($j = 0; $j < 8; $j++) {
                if (isset($secret[$i + $j])) {
                    $x .= str_pad(base_convert($base32charsFlipped[$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                }
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }
}
