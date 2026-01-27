<?php
namespace Modules\OrganizationsModule\ValueObjects;
use InvalidArgumentException;

final class Money{

    private string $amount; // string لتجنب float
    private string $currency;

    /**
     * @param string $amount
     * @param string $currency
     */
    public function __construct(string $amount, string $currency)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be numeric');
        }

        if (bccomp((string)$amount, '0', 2) === -1) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }

        $this->amount = bcadd((string)$amount, '0', 2);
        $this->currency = strtoupper($currency);
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency;
    }

    public function add(Money $money): Money
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self(
            bcadd($this->amount, $money->amount(), 2),
            $this->currency
        );
    }

    public function subtract(Money $money): Money
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies');
        }

        if (bccomp($this->amount, $money->amount(), 2) === -1) {
            throw new InvalidArgumentException('Result cannot be negative');
        }

        return new self(
            bcsub($this->amount, $money->amount(), 2),
            $this->currency
        );
    }

    public function equals(Money $money): bool
    {
        return $this->currency === $money->currency
            && bccomp($this->amount, $money->amount(), 2) === 0;
    }

}
