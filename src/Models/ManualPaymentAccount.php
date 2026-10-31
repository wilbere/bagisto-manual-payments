<?php

namespace Wilbere\BagistoManualPayments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $bank_code
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string $holder_name
 * @property string|null $holder_id
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $binance_id
 * @property string|null $qr_image_path
 * @property string|null $instructions
 * @property bool $status
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class ManualPaymentAccount extends Model
{
    public const TYPE_PAGOMOVIL = 'pagomovil';
    public const TYPE_BANK_TRANSFER = 'bank_transfer';
    public const TYPE_ZELLE = 'zelle';
    public const TYPE_BINANCE = 'binance';
    public const TYPE_WHATSAPP = 'whatsapp';

    protected $table = 'manual_payment_accounts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)->orderBy('sort_order');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ManualPaymentReceipt::class, 'payment_account_id');
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PAGOMOVIL => trans('manual_payments::app.methods.pagomovil'),
            self::TYPE_BANK_TRANSFER => trans('manual_payments::app.methods.bank_transfer'),
            self::TYPE_ZELLE => trans('manual_payments::app.methods.zelle'),
            self::TYPE_BINANCE => trans('manual_payments::app.methods.binance'),
            self::TYPE_WHATSAPP => trans('manual_payments::app.methods.whatsapp'),
            default => ucfirst($this->type),
        };
    }
}
