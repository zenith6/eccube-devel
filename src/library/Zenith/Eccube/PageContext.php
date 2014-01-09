<?php

class Zenith_Eccube_PageContext extends ArrayObject {
    protected $secret_key;

    /**
     * @param array $input
     * @param string $secret_key
     */
    public function __construct($input = array(), $secret_key = null) {
        parent::__construct($input);

        $this->secret_key = $secret_key;
    }

    /**
     * @param string $encoded
     */
    public function restore($encoded) {
        $data = $this->decode($encoded);
        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * @param string $data
     * @param string $secret_key
     * @return string
     */
    protected function hash($data, $secret_key) {
        return hash_hmac('sha1', $data, $this->secret_key);
    }

    /**
     * @return string
     */
    public function encode() {
        $data = iterator_to_array($this);
        $serialized = serialize($data);
        $hash = $this->hash($serialized, $this->secret_key);
        return base64_encode($serialized) . '--' . base64_encode($hash);
    }

    /**
     * @param string $encoded
     * @throws RuntimeException
     * @return array
     */
    public function decode($encoded) {
        @list($serialized, $hash) = array_map('base64_decode', (array)explode('--', $encoded, 2));

        $current_hash = $this->hash($serialized, $this->secret_key);
        if ($hash !== $current_hash) {
            throw new RuntimeException('page context string was invalid.');
        }

        $data = unserialize($serialized);
        if (!is_array($data)) {
            throw new RuntimeException('page context string was broken.');
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
