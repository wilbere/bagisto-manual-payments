<?php

namespace Wilbere\BagistoManualPayments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $payment_account_id
 * @property string $payment_method
 * @property string $token
 * @property string|null $reference_number
 * @property string|null $payer_name
 * @property string|null $payer_id
 * @property string|null $payer_phone
 * @property string|null $payer_email
 * @property string|null $payer_bank
 * @property string|null $paid_amount
 * @property string|null $paid_currency
 * @property string|null $exchange_rate
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property string|null $proof_image_path
 * @property string|null $proof_original_name
 * @property string|null $proof_mime
 * @property int|null $proof_size
 * @property string $status
 * @property string|null $admin_notes
 * @property string|null $rejection_reason
 * @property int|null $verified_by
 * @property \Carbon\CarbonImmutable|null $verified_at
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class ManualPaymentReceipt extends Model
{
    public const STATUS_PENDING_SUBMISSION = 'pending_submission';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $table = 'manual_payment_receipts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_amount' => 'decimal:4',
            'exchange_rate' => 'decimal:4',
            'paid_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'proof_size' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        $orderModel = config('manual-payments.models.order', 'Webkul\Sales\Models\Order');

        return $this->belongsTo($orderModel, 'order_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ManualPaymentAccount::class, 'payment_account_id');
    }

    public function verifier(): BelongsTo
    {
        $adminModel = config('manual-payments.models.admin', 'Webkul\User\Models\Admin');

        return $this->belongsTo($adminModel, 'verified_by');
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_VERIFICATION);
    }

    public function scopePendingSubmission(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_SUBMISSION);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_SUBMISSION)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    public function scopeByToken(Builder $query, string $token): Builder
    {
        return $query->where('token', $token);
    }

    public function isPendingSubmission(): bool
    {
        return $this->status === self::STATUS_PENDING_SUBMISSION;
    }

    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->isPendingSubmission() && $this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return false;
    }

    public function canBeSubmitted(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        if ($this->isPendingSubmission()) {
            return true;
        }

        if ($this->isRejected() && config('manual-payments.allow_resubmission', true)) {
            return true;
        }

        return false;
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_SUBMISSION => trans('manual_payments::app.status.pending_submission'),
            self::STATUS_PENDING_VERIFICATION => trans('manual_payments::app.status.pending_verification'),
            self::STATUS_APPROVED => trans('manual_payments::app.status.approved'),
            self::STATUS_REJECTED => trans('manual_payments::app.status.rejected'),
            self::STATUS_EXPIRED => trans('manual_payments::app.status.expired'),
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
