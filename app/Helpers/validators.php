<?php
function validate_required(string $value): bool
{
    return isset($value) && trim($value) !== '';
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_numeric($value): bool
{
    return is_numeric($value);
}

function validate_min_length(string $value, int $min): bool
{
    return strlen(trim($value)) >= $min;
}

function validate_max_length(string $value, int $max): bool
{
    return strlen(trim($value)) <= $max;
}

function validate_range($value, $min, $max): bool
{
    return $value >= $min && $value <= $max;
}

function validate_phone(string $phone): bool
{
    return preg_match('/^[0-9\+\-\(\)\s]{7,20}$/', $phone) === 1;
}

function validate_rfc(string $rfc): bool
{
    return preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', strtoupper($rfc)) === 1;
}

function validate_url(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function validate_date(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validate_alpha(string $value): bool
{
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $value) === 1;
}

function validate_alphanumeric(string $value): bool
{
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_]+$/', $value) === 1;
}

function validate_positive($value): bool
{
    return is_numeric($value) && $value > 0;
}

function validate_decimal($value, int $precision = 2): bool
{
    if (!is_numeric($value)) return false;
    $parts = explode('.', (string) $value);
    if (count($parts) === 2 && strlen($parts[1]) > $precision) return false;
    return true;
}

function validate_length(string $value, int $exact): bool
{
    return strlen($value) === $exact;
}

function validate_unique(string $table, string $column, $value, $excludeId = null): bool
{
    $db = \App\Core\Database::getInstance();
    $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$column} = :value";
    $params = ['value' => $value];
    if ($excludeId !== null) {
        $sql .= " AND id != :exclude_id";
        $params['exclude_id'] = $excludeId;
    }
    $result = $db->fetchOne($sql, $params);
    return (int) ($result['total'] ?? 0) === 0;
}

function validate_estatus(string $value): bool
{
    $valid = ['activo', 'inactivo', 'pendiente', 'completado', 'cancelado', 'en_proceso'];
    return in_array(strtolower($value), $valid);
}

function validate(array $data, array $rules): array
{
    $errors = [];
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? '';
        $rulesList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
        foreach ($rulesList as $rule) {
            $params = [];
            if (strpos($rule, ':') !== false) {
                [$rule, $paramStr] = explode(':', $rule, 2);
                $params = explode(',', $paramStr);
            }
            switch ($rule) {
                case 'required':
                    if (!validate_required($value)) {
                        $errors[$field][] = "El campo {$field} es obligatorio";
                    }
                    break;
                case 'email':
                    if (!empty($value) && !validate_email($value)) {
                        $errors[$field][] = "El campo {$field} debe ser un email válido";
                    }
                    break;
                case 'numeric':
                    if (!empty($value) && !validate_numeric($value)) {
                        $errors[$field][] = "El campo {$field} debe ser numérico";
                    }
                    break;
                case 'min':
                    $min = $params[0] ?? 0;
                    if (!empty($value) && !validate_min_length($value, (int) $min)) {
                        $errors[$field][] = "El campo {$field} debe tener al menos {$min} caracteres";
                    }
                    break;
                case 'max':
                    $max = $params[0] ?? 255;
                    if (!empty($value) && !validate_max_length($value, (int) $max)) {
                        $errors[$field][] = "El campo {$field} debe tener máximo {$max} caracteres";
                    }
                    break;
                case 'phone':
                    if (!empty($value) && !validate_phone($value)) {
                        $errors[$field][] = "El campo {$field} debe ser un teléfono válido";
                    }
                    break;
                case 'rfc':
                    if (!empty($value) && !validate_rfc($value)) {
                        $errors[$field][] = "El campo {$field} debe ser un RFC válido";
                    }
                    break;
                case 'alpha':
                    if (!empty($value) && !validate_alpha($value)) {
                        $errors[$field][] = "El campo {$field} solo acepta letras";
                    }
                    break;
                case 'alphanumeric':
                    if (!empty($value) && !validate_alphanumeric($value)) {
                        $errors[$field][] = "El campo {$field} solo acepta letras y números";
                    }
                    break;
                case 'positive':
                    if (!empty($value) && !validate_positive($value)) {
                        $errors[$field][] = "El campo {$field} debe ser un valor positivo";
                    }
                    break;
            }
        }
    }
    return $errors;
}
