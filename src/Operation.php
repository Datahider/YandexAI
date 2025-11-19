<?php

namespace losthost\YandexAI;

class Operation {

    protected \stdClass $data;
    protected ?OperationError $error = null;

    public function __construct(array|string|stdClass $input) {
        if (is_string($input)) {
            $decoded = json_decode($input);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Invalid JSON string");
            }
            $this->data = $decoded;
        } elseif (is_array($input)) {
            $this->data = json_decode(json_encode($input));
        } elseif ($input instanceof \stdClass) {
            $this->data = $input;
        } else {
            throw new InvalidArgumentException("Unsupported input type");
        }

        // Автоматическое создание вложенного объекта error
        if (isset($this->data->error)) {
            $this->error = new OperationError($this->data->error);
        }
    }

    public static function fromResponse(array|string|stdClass $input): self {
        return new self($input);
    }

    public function __get(string $name) {
        return $this->data->$name ?? null;
    }
    
    public function getId() : string {
        return $this->data->id;
    }
    
    public function getError() : OperationError {
        return $this->error;
    }
    
    public function isDone() : bool {
        return $this->data->done;
    }

}
