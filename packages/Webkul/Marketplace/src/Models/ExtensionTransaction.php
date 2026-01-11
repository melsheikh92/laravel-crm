<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Marketplace\Contracts\ExtensionTransaction as ExtensionTransactionContract;
use Webkul\User\Models\UserProxy;

class ExtensionTransaction extends Model implements ExtensionTransactionContract
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'amount',
        'platform_fee',
        'seller_revenue',
        'payment_method',
        'status',
        'notes',
        'metadata',
        'extension_id',
        'buyer_id',
        'seller_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'amount'         => 'decimal:2',
        'platform_fee'   => 'decimal:2',
        'seller_revenue' => 'decimal:2',
        'metadata'       => 'array',
    ];

    /**
     * Get the extension that was purchased.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(ExtensionProxy::modelClass());
    }

    /**
     * Get the buyer (user who purchased).
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'buyer_id');
    }

    /**
     * Get the seller (extension author).
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'seller_id');
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include refunded transactions.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope a query to filter by payment method.
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to filter transactions for a specific buyer.
     */
    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Scope a query to filter transactions for a specific seller.
     */
    public function scopeForSeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Scope a query to filter transactions for a specific extension.
     */
    public function scopeForExtension($query, $extensionId)
    {
        return $query->where('extension_id', $extensionId);
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if transaction has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark transaction as completed.
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    /**
     * Mark transaction as refunded.
     */
    public function markAsRefunded(string $reason = null): void
    {
        $this->status = 'refunded';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    /**
     * Mark transaction as cancelled.
     */
    public function markAsCancelled(string $reason = null): void
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted platform fee.
     */
    public function getFormattedPlatformFeeAttribute(): string
    {
        return '$' . number_format($this->platform_fee, 2);
    }

    /**
     * Get formatted seller revenue.
     */
    public function getFormattedSellerRevenueAttribute(): string
    {
        return '$' . number_format($this->seller_revenue, 2);
    }

    /**
     * Get the platform fee percentage.
     */
    public function getPlatformFeePercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->platform_fee / $this->amount) * 100;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ExtensionTransactionFactory::new();
    }
}
