<?php

class ZSchema {
    private array $validations = [];
    private array $transforms = [];
    private array $messages = [];

    function __construct(
        private string $type,
        private mixed $message = null,
        private mixed $array = null,
    ) {}

    public static function int($message = null): self {
        return new self("int", $message);
    }

    public static function float($message = null): self {
        return new self("float", $message);
    }

    public static function string($message = null): self {
        return new self("string", $message);
    }

    public static function bool($message = null): self {
        return new self("bool", $message);
    }

    public static function array($array = null, $message = null): mixed {
        // if (!is_array($array)) throw new Exception("the argument must be an array");

        // if (count($array) == 0) throw new Exception("array cannot be empty");

        if ($array != null && is_array($array) && count($array) > 0) {
            foreach($array as $key => $value) {
                if (!($value instanceof Schema)) throw new Exception("$key is not a Schema");
            }
        }

        return new self("array", $message, $array);
    }

    public static function null(): self {
        return new self("null");
    }

    public function parse($value, $key = null) {
        switch ($this->type) {
            case "int":
                $is_valid = $this->validate_int($value, $key);
                if ($is_valid['success'] == true) return $value;
                throw new Exception($is_valid['message']);

            case "float":
                $is_valid = $this->validate_float($value, $key);
                if ($is_valid['success'] == true) return $value;
                throw new Exception($is_valid['message']);

            case "string":
                $is_valid = $this->validate_string($value, $key);
                if ($is_valid['success'] == true) return $value;
                throw new Exception($is_valid['message']);

            case "bool":
                if (!is_bool($value)) throw new Exception($this->message ?? "$value is not a boolean");
                return $value;

            case "array":
                if (!is_array($value)) throw new Exception($this->message ?? "$value is not an array");

                foreach ($this->array as $tKey => $tValue) {
                    if (!($tValue instanceof Schema)) throw new Exception("$tKey is not a Schema");

                    if (isset($value[$tKey])) $tValue->parse($value[$tKey], $tKey);
                    else if (isset($tValue->get_validations()['required'])) {
                        throw new Exception($tValue->get_messages()["required"] ?? "$tKey is required");
                    }
                }

                return $value;

            case "null":
                if (!is_null($value)) throw new Exception("$value is not null");
                return null;
        }
    }

    public function safe_parse($value): array {
        try {
            return [
                "success" => true,
                "value" => $this->parse($value)
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage(),
                "value" => $value
            ];
        }
    }

    private function validate_string($value, $key = null): array {
        if ($key == null) $key = $value;

        if (!is_string($value)) return [
            "success" => false,
            "message" => $this->message ?? "$key is not a valid string"
        ];

        foreach ($this->validations as $validation_name => $validation_value) {
            switch ($validation_name) {
                case "not_empty":
                    if (empty($value)) return [
                        "success" => false,
                        "message" => $this->messages["not_empty"] ?? "$key cannot be empty"
                    ];
                    break;

                case "max_length":
                    if (strlen($value) > $validation_value) return [
                        "success" => false,
                        "message" => $this->messages["max_length"] ?? "$key must not be greater than $validation_value characters"
                    ];
                    break;

                case "min_length":
                    if (strlen($value) < $validation_value) return [
                        "success" => false,
                        "message" => $this->messages["min_length"] ?? "$key must not be less than $validation_value characters"
                    ];
                    break;

                case "length":
                    if (strlen($value) != $validation_value) return [
                        "success" => false,
                        "message" => $this->messages["length"] ?? "$key must be $validation_value characters"
                    ];
                    break;

                case "email":
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) return [
                        "success" => false,
                        "message" => $this->messages["email"] ?? "$key is not a valid email"
                    ];
                    break;

                case "url":
                    if (!filter_var($value, FILTER_VALIDATE_URL)) return [
                        "success" => false,
                        "message" => $this->messages["url"] ?? "$key is not a valid url"
                    ];
                    break;

                case "uuid":
                    if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $value)) return [
                        "success" => false,
                        "message" => $this->messages["uuid"] ?? "$key is not a valid uuid"
                    ];
                    break;

                case "ipv4":
                    if (!filter_var($value, FILTER_VALIDATE_IP)) return [
                        "success" => false,
                        "message" => $this->messages["ipv4"] ?? "$key is not a valid ipv4"
                    ];
                    break;

                case "ipv6":
                    if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return [
                        "success" => false,
                        "message" => $this->messages["ipv6"] ?? "$key is not a valid ipv6"
                    ];
                    break;

                case "regex":
                    if (!preg_match($validation_value, $value)) return [
                        "success" => false,
                        "message" => $this->messages["regex"] ?? "$key does not match $validation_value"
                    ];
                    break;

                case "includes":
                    if (!str_contains($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["includes"] ?? "$key does not contain $validation_value"
                    ];
                    break;

                case "not_includes":
                    if (str_contains($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["not_includes"] ?? "$key contains $validation_value"
                    ];
                    break;

                case "starts_with":
                    if (!str_starts_with($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["starts_with"] ?? "$key should start with $validation_value"
                    ];
                    break;

                case "not_starts_with":
                    if (str_starts_with($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["not_starts_with"] ?? "$key should not start with $validation_value"
                    ];
                    break;

                case "ends_with":
                    if (!str_ends_with($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["ends_with"] ?? "$key should end with $validation_value"
                    ];
                    break;

                case "not_ends_with":
                    if (str_ends_with($value, $validation_value)) return [
                        "success" => false,
                        "message" => $this->messages["not_ends_with"] ?? "$key should not end with $validation_value"
                    ];
                    break;

                case "date":
                    if (!strtotime($value)) return [
                        "success" => false,
                        "message" => $this->messages["date"] ?? "$key is not a valid date"
                    ];
                    break;

                case "time":
                    if (!strtotime($value)) return [
                        "success" => false,
                        "message" => $this->messages["time"] ?? "$key is not a valid time"
                    ];
                    break;

                case "datetime":
                    if (!strtotime($value)) return [
                        "success" => false,
                        "message" => $this->messages["datetime"] ?? "$key is not a valid datetime"
                    ];
                    break;
            }
        }

        return [
            "success" => true
        ];
    }

    private function transform_string(string $value): string {
        foreach ($this->transforms as $transform_name => $transform_value) {
            switch ($transform_name) {
                case "trim":
                    $value = trim($value);
                    break;

                case "to_lower_case":
                    $value = strtolower($value);
                    break;

                case "to_upper_case":
                    $value = strtoupper($value);
                    break;
            }
        }

        return $value;
    }

    public function validate_number($value, $key = null): array {
        if ($key == null) $key = $value;

        foreach ($this->validations as $validation_name => $validation_value) {
            switch ($validation_name) {
                case "not_empty":
                    if (empty($value)) return [
                        "success" => false,
                        "message" => $this->messages["not_empty"] ?? "$key cannot be empty"
                    ];
                    break;

                case "max":
                    if ($value > $validation_value) return [
                        "success" => false,
                        "message" => $this->messages["max"] ?? "$key must be less than $validation_value"
                    ];
                    break;

                case "min":
                    if ($value < $validation_value) return [
                        "success" => false,
                        "message" => $this->messages["min"] ?? "$key must be greater than $validation_value"
                    ];
                    break;

                case "positive":
                    if (!($value > 0)) return [
                        "success" => false,
                        "message" => $this->messages["positive"] ?? "$key must be greater than 0"
                    ];
                    break;

                case "nonpositive":
                    if ($value > 0) return [
                        "success" => false,
                        "message" => $this->messages["nonpositive"] ?? "$key must be less than 0"
                    ];
                    break;

                case "negative":
                    if (!($value < 0)) return [
                        "success" => false,
                        "message" => $this->messages["negative"] ?? "$key must be less than 0"
                    ];
                    break;

                case "nonnegative":
                    if ($value < 0) return [
                        "success" => false,
                        "message" => $this->messages["nonnegative"] ?? "$key must be greater than 0"
                    ];
                    break;
            }
        }

        return [
            "success" => true
        ];
    }

    public function validate_int($value, $key = null): array {
        if ($key == null) $key = $value;

        if (!is_int($value)) return [
            "success" => false,
            "message" => $this->message ?? "$key is not a valid int"
        ];

        return $this->validate_number($value, $key);
    }

    public function validate_float($value, $key = null): array {
        if ($key == null) $key = $value;

        if (!is_int($value)) return [
            "success" => false,
            "message" => $this->message ?? "$key is not a valid float"
        ];

        return $this->validate_number($value, $key);
    }

    public function transform_int(int $value): int {
        foreach ($this->transforms as $transform_name => $transform_value) {
            switch ($transform_name) {
                case "to_max":
                    $value = max($value, $transform_value);
                    break;
                case "to_min":
                    $value = min($value, $transform_value);
                    break;
            }
        }

        return $value;
    }

    public function max($value, $message = null): self {
        $this->validations['max'] = $value;
        if ($message != null) $this->messages['max'] = $message;
        return $this;
    }

    public function min($value, $message = null): self {
        $this->validations['min'] = $value;
        if ($message != null) $this->messages['min'] = $message;
        return $this;
    }

    public function not_empty($message = null): self {
        $this->validations['not_empty'] = true;
        if ($message != null) $this->messages['not_empty'] = $message;
        return $this;
    }

    public function required($message = null): self {
        $this->validations['required'] = true;
        if ($message != null) $this->messages['required'] = $message;
        return $this;
    }

    public function max_length($value, $message = null): self {
        $this->validations['max_length'] = $value;
        if ($message != null) $this->messages['max_length'] = $message;
        return $this;
    }

    public function min_length($value, $message = null): self {
        $this->validations['min_length'] = $value;
        if ($message != null) $this->messages['min_length'] = $message;
        return $this;
    }

    public function length($value, $message = null): self {
        $this->validations['length'] = $value;
        if ($message != null) $this->messages['length'] = $message;
        return $this;
    }

    public function email($message = null): self {
        $this->validations['email'] = true;
        if ($message != null) $this->messages['email'] = $message;
        return $this;
    }

    public function url($message = null): self {
        $this->validations['url'] = true;
        if ($message != null) $this->messages['url'] = $message;
        return $this;
    }

    // public function emoji(): self {
    //     $this->validations['emoji'] = true;
    //     return $this;
    // }

    public function uuid($message): self {
        $this->validations['uuid'] = true;
        if ($message != null) $this->messages['uuid'] = $message;
        return $this;
    }

    public function ipv4($message = null): self {
        $this->validations['ipv4'] = true;
        if ($message != null) $this->messages['ipv4'] = $message;
        return $this;
    }

    public function ipv6($message = null): self {
        $this->validations['ipv4'] = true;
        if ($message != null) $this->messages['ipv6'] = $message;
        return $this;
    }

    public function regex($regex, $message = null): self {
        $this->validations['regex'] = $regex;
        if ($message != null) $this->messages['regex'] = $message;
        return $this;
    }

    public function includes($value, $message = null): self {
        $this->validations['includes'] = $value;
        if ($message != null) $this->messages['includes'] = $message;
        return $this;
    }

    public function not_includes($value, $message = null): self {
        $this->validations['not_includes'] = $value;
        if ($message != null) $this->messages['not_includes'] = $message;
        return $this;
    }

    public function starts_with($value, $message = null): self {
        $this->validations['starts_with'] = $value;
        if ($message != null) $this->messages['starts_with'] = $message;
        return $this;
    }

    public function not_starts_with($value, $message = null): self {
        $this->validations['not_starts_with'] = $value;
        if ($message != null) $this->messages['not_starts_with'] = $message;
        return $this;
    }

    public function ends_with($value, $message = null): self {
        $this->validations['ends_with'] = $value;
        if ($message != null) $this->messages['ends_with'] = $message;
        return $this;
    }

    public function not_ends_with($value, $message = null): self {
        $this->validations['not_ends_with'] = $value;
        if ($message != null) $this->messages['not_ends_with'] = $message;
        return $this;
    }

    public function date($message = null): self {
        $this->validations['date'] = true;
        if ($message != null) $this->messages['date'] = $message;
        return $this;
    }

    public function time($message = null): self {
        $this->validations['time'] = true;
        if ($message != null) $this->messages['time'] = $message;
        return $this;
    }

    public function datetime($message = null): self {
        $this->validations['datetime'] = true;
        if ($message != null) $this->messages['datetime'] = $message;
        return $this;
    }

    // public function base64($message = null) {
    //     $this->validations['base64'] = true;
    //     if ($message != null) $this->messages['base64'] = $message;
    //     return $this;
    // }

    public function positive($message = null): self {
        $this->validations['positive'] = true;
        if ($message != null) $this->messages['positive'] = $message;
        return $this;
    }

    public function nonpositive($message = null): self {
        $this->validations['nonpositive'] = true;
        if ($message != null) $this->messages['nonpositive'] = $message;
        return $this;
    }

    public function negative($message = null): self {
        $this->validations['negative'] = true;
        if ($message != null) $this->messages['negative'] = $message;
        return $this;
    }

    public function nonnegative($message = null): self {
        $this->validations['nonnegative'] = true;
        if ($message != null) $this->messages['nonnegative'] = $message;
        return $this;
    }

    public function trim(): self {
        $this->transforms['trim'] = true;
        return $this;
    }

    public function to_lower_case(): self {
        $this->transforms['to_lower_case'] = true;
        return $this;
    }

    public function to_upper_case(): self {
        $this->transforms['to_upper_case'] = true;
        return $this;
    }

    public function to_max($value): self {
        $this->transforms['to_max'] = $value;
        return $this;
    }

    public function to_min($value): self {
        $this->transforms['to_min'] = $value;
        return $this;
    }

    public function get_validations() {
        return $this->validations;
    }

    public function get_messages() {
        return $this->messages;
    }
}