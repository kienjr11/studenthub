<?php
declare(strict_types=1);

/** Validation phía server. Mỗi trường giữ lại lỗi đầu tiên gặp phải. */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function value(string $field): string
    {
        return trim((string) ($this->data[$field] ?? ''));
    }

    public function add(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->add($field, "$label không được để trống.");
        }
        return $this;
    }

    public function length(string $field, string $label, int $min, int $max): self
    {
        $v = $this->value($field);
        if ($v !== '' && (mb_strlen($v) < $min || mb_strlen($v) > $max)) {
            $this->add($field, "$label phải dài từ $min đến $max ký tự.");
        }
        return $this;
    }

    public function personName(string $field, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && !preg_match("/^[\p{L}\p{M}][\p{L}\p{M}\s.'’-]*$/u", $v)) {
            $this->add($field, "$label chỉ được chứa chữ cái, khoảng trắng và dấu . ' -");
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): self
    {
        $v = $this->value($field);
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->add($field, "$label không đúng định dạng.");
        }
        return $this;
    }

    public function in(string $field, string $label, array $allowed): self
    {
        if (!in_array($this->value($field), $allowed, true)) {
            $this->add($field, "$label không hợp lệ.");
        }
        return $this;
    }

    /** Mật khẩu không trim; bcrypt chỉ dùng 72 byte đầu nên giới hạn tối đa 72 byte. */
    public function password(string $field, int $min): self
    {
        $v = (string) ($this->data[$field] ?? '');
        if ($v === '') {
            $this->add($field, 'Mật khẩu không được để trống.');
        } elseif (mb_strlen($v) < $min) {
            $this->add($field, "Mật khẩu phải có ít nhất $min ký tự.");
        } elseif (strlen($v) > 72) {
            $this->add($field, 'Mật khẩu tối đa 72 byte (khoảng 72 ký tự không dấu).');
        } elseif (!preg_match('/\p{L}/u', $v) || !preg_match('/\d/', $v)) {
            $this->add($field, 'Mật khẩu phải chứa cả chữ cái và chữ số.');
        }
        return $this;
    }

    public function matches(string $field, string $other, string $message): self
    {
        if ((string) ($this->data[$field] ?? '') !== (string) ($this->data[$other] ?? '')) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
