<?php
namespace Modules\OrganizationsModule\Casts;
use Illuminate\Database\Eloquent\Model;
use Modules\OrganizationsModule\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
        private string $defaultCurrency;

    public function __construct(string $defaultCurrency = 'USD')
    {
        $this->defaultCurrency = strtoupper($defaultCurrency);
    }

    public function get($model, string $key, $value, array $attributes): Money
    {
        // يمكن إضافة عمود currency إذا موجود في الجدول
        $currency = $attributes['currency'] ?? $this->defaultCurrency;

        return new Money($value, $currency);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
   public function set($model, string $key, $value, array $attributes): array|string|int|float
    {
        if ($value instanceof Money) {
            return $value->amount(); // نحتفظ بالـ decimal في DB
        }

        // يمكن تمرير عدد فقط (float/string)
        return $value;
    }
}
