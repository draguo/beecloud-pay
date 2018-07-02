<?php
/**
 * 银联签名使用
 */
namespace Draguo\Pay\Supports;


class Rsa
{
    public static function getCertId($cert_path, $cert_pwd)
    {
        $cert = file_get_contents($cert_path);
        openssl_pkcs12_read($cert, $certs, $cert_pwd);
        $x509data = $certs ['cert'];
        openssl_x509_read($x509data);
        $certData = openssl_x509_parse($x509data);
        return $certData['serialNumber'];
    }

    public static function getPrivateKey($cert_path, $cert_pwd)
    {
        $data = file_get_contents($cert_path);
        openssl_pkcs12_read($data, $certs, $cert_pwd);
        return $certs['pkey'];
    }
}