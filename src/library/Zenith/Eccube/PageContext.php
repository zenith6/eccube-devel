<?php

class Zenith_Eccube_PageContext extends ArrayObject {
    protected $secret_key;

    /**
     * @param array $input
     * @param string $secret_key
     */
    public function __construct($input = array(), $secret_key = null) {
        parent::__construct($input);

        if ($secret_key === null) {
            $this->secret_key = AUTH_MAGIC;
        }
    }

    /**
     * @param string $serialized
     * @param array $defaults
     * @return Zenith_Eccube_PageContext
     */
    public static function restore($serialized = '', $defaults = array()) {
        $context = new self($defaults);

        if ($serialized == '') {
            return $context;
        }
        
        $data = $context->decode($serialized);
        foreach ($data as $key => $value) {
            $context[$key] = $value;
        }

        return $context;
    }

    /**
     * @param string $data
     * @param string $secret_key
     * @return string
     */
    protected function digest($data, $secret_key) {
        return hash_hmac('sha1', $data, $this->secret_key);
    }

    /**
     * @return string
     */
    public function encode() {
        $data = iterator_to_array($this);
        $serialized = serialize($data);
        $digest = $this->digest($serialized, $this->secret_key);
        return base64_encode($serialized) . '--' . base64_encode($digest);
    }

    /**
     * @param string $encoded
     * @throws RuntimeException
     * @return array
     */
    public function decode($encoded) {
        @list($serialized, $digest) = (array)explode('--', $encoded, 2);
        $serialized = base64_decode($serialized);
        $digest = base64_decode($digest);

        $current_digest = $this->digest($serialized, $this->secret_key);
        if ($digest !== $current_digest) {
            throw new RuntimeException('page context was invalid.');
        }

        $data = unserialize($serialized);
        if (!is_array($data)) {
            throw new RuntimeException('page context was broken.');
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->encode();
    }
}
