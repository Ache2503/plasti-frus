<?php
namespace App\Http\Requests;

use App\Exceptions\ValidationException;

abstract class FormRequest
{
    protected array $data;
    protected array $errors = [];

    public function __construct(array $data = [])
    {
        $this->data = !empty($data) ? $data : array_merge($_GET, $_POST);
    }

    abstract public function rules(): array;

    abstract public function messages(): array;

    public function validate(): array
    {
        $this->errors = \validate($this->data, $this->rules());

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->sanitized();
    }

    public function validated(): array
    {
        $rules = $this->rules();
        $keys = array_keys($rules);
        return array_intersect_key($this->data, array_flip($keys));
    }

    public function sanitized(): array
    {
        $data = $this->validated();
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function fails(): bool
    {
        return !empty(\validate($this->data, $this->rules()));
    }

    public function errors(): array
    {
        return \validate($this->data, $this->rules());
    }
}
