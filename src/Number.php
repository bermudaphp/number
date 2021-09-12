<?php

namespace Bermuda\Number;

use function Bermuda\is_hex;
use function Bermuda\is_oct;

final class Number
{
    /**
     * @var int|float
     */
    private $value;
    
    /**
     * Numeric constructor.
     * @param int|float|string $int
     * @throws \InvalidArgumentException
     */
    public function __construct($int = 0)
    {
        $this->value = $this->normalize($int);
    }

    /**
     * @return Number
     */
    public function abs(): self
    {
        return new self(abs($this->toNumber()));
    }

    /**
     * @param $numeric
     * @return Number
     */
    public function module($numeric): self 
    {
        return $this->decrement($numeric, true);
    }

    /**
     * @param $numeric
     * @param bool $module
     * @return Number
     */
    public function division($numeric, bool $module = false): self 
    {
        if (($numeric = $this->normalize($numeric)) == 0) {
            throw new \ArithmeticError('Division by zero');
        }

        if ($module) {
            return new self(($this->toNumber() % $numeric));
        }

        return new self(($this->toNumber() / $numeric));
    }

    public function dd() : void 
    {
        dd($this->toNumber());
    }

    /**
     * @param $numeric
     * @return Number
     */
    public function percent($numeric): self
    {
        return new self($this->toNumber() / 100 * $this->normalize($numeric));
    }

    /**
     * @param $numeric
     * @return Number
     */
    public function rp($numeric): self 
    {
        return new self($this->normalize($numeric) / $this->toNumber() * 100);
    }

    /**
     * @param $numeric
     * @return Number
     */
    public function multiplication($numeric): self 
    {
        return new self($this->normalize($numeric) * $this->toNumber());
    }

    /**
     * @param $numeric
     * @param bool $strict
     * @return bool
     */
    public function equals($numeric, bool $strict = false): bool
    {
        return $strict ? $this->normalize($numeric) === $this->toNumber()
            : $this->normalize($numeric) == $this->toNumber();
    }

    /**
     * @param $numeric
     * @return bool
     */
    public function identity($numeric) : bool 
    {
        return $this->equals($numeric, true);
    }

    /**
     * @return Number
     */
    public function round(): self 
    {
        return new self(round($this->toNumber()));
    }

    /**
     * @return string
     */
    public function toString(): string 
    {
        return (string) $this->value ;
    }

    /**
     * @return int|float
     */
    public function toNumber()
    {
        return $this->isInt() ? $this->toInt() : $this->toFloat();
    }

    /**
     * @return bool
     */
    public function isFloat(): bool 
    {
        return $this->value - floor($this->value) != 0;
    }

    /**
     * @return bool
     */
    public function isInt(): bool
    {
        return !$this->isFloat();
    }

    /**
     * @return int
     */
    public function toInt(): int 
    {
        return intval($this->value);
    }

    /**
     * @return float
     */
    public function toFloat(): float 
    {
        return floatval($this->value);
    }

    /**
     * @return string
     */
    public function toHex(): string
    {
        return dechex($this->toNumber());
    }

    /**
     * @param $var
     * @return int|float
     */
    private function normalize($var)
    {
        if($var instanceof static)
        {
            return $var->toNumber();
        }

        if(is_numeric($var))
        {
            return $var + 0 ;
        }

        if(is_hex($var))
        {
            return hexdec($var);
        }

        if(is_oct($var))
        {
            return octdec($var);
        }

        return 0;
    }

    /**
     * @param int|float|string $numeric
     * @return Number
     */
    public function increment($numeric = 1): self
    {
        return new self($this->toNumber() + $this->normalize($numeric));
    }

    /**
     * @param int|float|string $numeric
     * @return Number
     */
    public function decrement($numeric): self 
    {
        return new self($this->toNumber() - $this->normalize($numeric));
    }

    /**
     * @param int $exp
     * @return Number
     */
    public function pow(int $exp): self 
    {
        return new self(pow($this->toNumber(), $exp));
    }

    /**
     * @return Number
     */
    public function sqrt(): self 
    {
        return new self(sqrt($this->toNumber()));
    }
}
