<?php

namespace losthost\YandexAI;

/**
 * Description of OperationError
 *
 * @author drweb
 */
class OperationError {

    protected stdClass $data;

    public function __construct(array|string|stdClass $input) {
        if (is_string($input)) {
            $decoded = json_decode($input);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException("Invalid JSON string");
            }
            $this->data = $decoded;
        } elseif (is_array($input)) {
            $this->data = json_decode(json_encode($input));
        } elseif ($input instanceof stdClass) {
            $this->data = $input;
        } else {
            throw new \InvalidArgumentException("Unsupported input type");
        }
    }

    public function getCode(): string {
        return $this->data->code ?? 0;
    }

    public function getMessage(): string {
        return $this->data->code ?? '';
    }

    public function getDetails(): array {
        return $this->data->code ?? [];
    }

}
