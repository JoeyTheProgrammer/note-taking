<?php

class Encryption {
    private $key;
    private $cipher = 'aes-256-cbc';
    private $response;

    public function __construct() {
        $xmlFile = __DIR__ . '/encryption.xml';

        if(!file_exists($xmlFile)){
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "XML configuration not found";
            return $this->response;
        }

        $xml = simplexml_load_file($xmlFile);

        if(!isset($xml->key) || empty($xml->key)){
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "Encryption key not found in configuration file";
            return $this->response;
        }

        $this->key = hash('sha256', (string)$xml->key, true); // Hash the key for use in encryption
    }

    /**
     * Encrypts plaintext data.
     */
    public function encrypt($plaintext){
        $this->response["response_code"] = 0;
        $this->response["response_message"] = "Clear";

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $ciphertext = openssl_encrypt($plaintext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "Encryption Failed";
            return $this->response;
        }

        $this->response["encrypted_data"] = base64_encode($iv . $ciphertext);
        return $this->response;
    }

    /**
     * Decrypts encrypted data.
     */
    public function decrypt($encryptedData){
        $this->response["response_code"] = 0;
        $this->response["response_message"] = "Clear";

        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);

        $iv = substr($data, 0, $ivLength);
        $ciphertext = substr($data, $ivLength);

        $plaintext = openssl_decrypt($ciphertext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "Decryption Failed";
            return $this->response;
        }

        $this->response["decrypted_data"] = $plaintext;
        return $this->response;
    }
}
?>
