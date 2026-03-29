<?php
/**
 * 藍新 MPG 2.0 — TradeInfo（AES-256-CBC hex）+ TradeSha（SHA256 大寫）
 */
require_once __DIR__ . '/../config/payment_minimal.php';

class PaymentMinimal
{
    public static function stripPadding($string)
    {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        if ($slast < 1 || $slast > 32) {
            return $string;
        }
        $pcheck = substr($string, -$slast);
        if (preg_match('/' . preg_quote($slastc, '/') . '{' . $slast . '}/', $string)) {
            return substr($string, 0, strlen($string) - $slast);
        }
        return $string;
    }

    /**
     * @param array $params 內含 MerchantID、MerchantOrderNo、Amt、…
     */
    public static function encryptTradeInfo(array $params)
    {
        $data = http_build_query($params);
        $raw = openssl_encrypt($data, 'AES-256-CBC', MPG_HASH_KEY, OPENSSL_RAW_DATA, MPG_HASH_IV);
        if ($raw === false) {
            return false;
        }
        return bin2hex($raw);
    }

    public static function buildTradeSha($tradeInfoHex)
    {
        $s = 'HashKey=' . MPG_HASH_KEY . '&' . $tradeInfoHex . '&HashIV=' . MPG_HASH_IV;
        return strtoupper(hash('sha256', $s));
    }

    public static function decryptTradeInfoHex($hexCipher)
    {
        $bin = @hex2bin($hexCipher);
        if ($bin === false) {
            return false;
        }
        $dec = openssl_decrypt($bin, 'AES-256-CBC', MPG_HASH_KEY, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, MPG_HASH_IV);
        if ($dec === false) {
            return false;
        }
        return self::stripPadding($dec);
    }

    /**
     * @return array|null
     */
    public static function decryptTradeInfoToJson($hexCipher)
    {
        $plain = self::decryptTradeInfoHex($hexCipher);
        if ($plain === false) {
            return null;
        }
        $j = json_decode($plain, true);
        return is_array($j) ? $j : null;
    }

    public static function verifyTradeSha($tradeInfoHex, $tradeShaReceived)
    {
        $expected = self::buildTradeSha($tradeInfoHex);
        return $tradeShaReceived !== '' && hash_equals($expected, (string)$tradeShaReceived);
    }
}
