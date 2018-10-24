<?php
namespace dollarphp;
/**
 * @desc：php rsa加密解密类
 * @author [Lee] <[<complet@163.com>]>
 */
class rsa{
    private static $private_key = <<<HH
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDjcuqtaq1rq6XVPqAS9oYh16CAi/woORCyq8FKlpthj/9azU+v
cXmra7VIlSjwckRoxpK/j0FjeVNf1BAj5XXctqnh5X8BZYiZxYQdfr6U20zu8bjK
zHLPF4ZbzpAhXd9pYrFSawt5U3RtIw8nq5MDJIlOLupxTx9gWLRCC9gHYwIDAQAB
AoGAJVi0Mf9nNFu94hLjY9m40ou+Xf0eTVh5Zm0PUvkB0HY9fqJhqDQgv0XzQVTE
oR6SHwYkCHI0UWoVh5GhiNNfk6vIqlNwwI7gxi5kIhMQbF7x2ghvwGPvfxzUxY4x
3zq7QnTChpmrFudJrCliosY+FTdoAWxtRDSTm8//74k6eSkCQQD/JZjc6/r42H0j
JUsy9YFVwnOrZXPYUCRDo34ottFrowUdNdmIjdf7kHkNW6bd05XiyX74MYdnFUVz
Xfju2uEPAkEA5DWcRVTEiUBYy1Qg/MSwSVL7HPv9AziBSIFwKzogO9ROw+B/hHUx
845S7r0WKGO7D56IJNcELIKpW3t0vU6MbQJAMeAcmJr8jWZsV9FzeLurE6OWTtvf
IFrSK/Kqt7S9DUhpuIMNSfdIUCG2uBjBbr1soE95JXUxHcJ3uAyXm8FnmwJBANFl
N5yOKT/e4RrAePw15aOCFpQDy6Z25HmI+0lOrmD3b8ZfaeI6PrlCMGqK6Zfp2qx8
RGO5P0UwJwGgB//j4QkCQQDe8Ogu3JJ256Z4NDy/GC3VSZqKxnApTJICScYFt3hQ
M5/d8kDpkvcugI0ADjjBYs0FpnSiYsyq85GCAFQZsym4
-----END RSA PRIVATE KEY-----
HH;
    private static $public_key = <<<HH
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDjcuqtaq1rq6XVPqAS9oYh16CA
i/woORCyq8FKlpthj/9azU+vcXmra7VIlSjwckRoxpK/j0FjeVNf1BAj5XXctqnh
5X8BZYiZxYQdfr6U20zu8bjKzHLPF4ZbzpAhXd9pYrFSawt5U3RtIw8nq5MDJIlO
LupxTx9gWLRCC9gHYwIDAQAB
-----END PUBLIC KEY-----
HH;
    /**
     * 获取私钥
     * @return bool|resource
     */
    private static function getPrivateKey(){
        $privKey = self::$private_key; 
        return openssl_pkey_get_private($privKey);    }
    /**
     * 获取公钥
     * @return bool|resource
     */
    private static function getPublicKey(){
        $publicKey = self::$public_key;
        return openssl_pkey_get_public($publicKey);
    }
    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privEncrypt($data = ''){
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey()) ? base64_encode($encrypted) : null;
    }
    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public static function publicEncrypt($data = ''){
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data,$encrypted,self::getPublicKey()) ? base64_encode($encrypted) : null;
    }
    /**     
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public static function privDecrypt($encrypted = ''){
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? $decrypted : null;
    }
    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt($encrypted = ''){
        if (!is_string($encrypted)) {
            return null;
        }
    return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? $decrypted : null;
    }
}
$rsa = new rsa();
$str = 'hello world';
// 私钥加密
$privEncrypt = $rsa->privEncrypt($str);
echo $privEncrypt.PHP_EOL.PHP_EOL;
// 公钥解密
$publicDecrypt = $rsa->publicDecrypt($privEncrypt);
echo $publicDecrypt.PHP_EOL.PHP_EOL;
// 公钥加密
$publicEncrypt = $rsa->publicEncrypt($str);
echo $publicEncrypt.PHP_EOL.PHP_EOL;
// 私钥解密
$privDecrypt = $rsa->privDecrypt($publicEncrypt);
echo $privDecrypt.PHP_EOL.PHP_EOL;