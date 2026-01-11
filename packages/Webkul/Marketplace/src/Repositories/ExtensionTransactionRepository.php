<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\ExtensionTransaction;

class ExtensionTransactionRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return ExtensionTransaction::class;
    }

    /**
     * Get transactions for a specific seller.
     *
     * @param  int  $sellerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forSeller(int $sellerId)
    {
        return $this->model->where('seller_id', $sellerId);
    }

    /**
     * Get transactions for a specific buyer.
     *
     * @param  int  $buyerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forBuyer(int $buyerId)
    {
        return $this->model->where('buyer_id', $buyerId);
    }

    /**
     * Get transactions for a specific extension.
     *
     * @param  int  $extensionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forExtension(int $extensionId)
    {
        return $this->model->where('extension_id', $extensionId);
    }

    /**
     * Get completed transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function completed()
    {
        return $this->model->where('status', 'completed');
    }

    /**
     * Get pending transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function pending()
    {
        return $this->model->where('status', 'pending');
    }

    /**
     * Get refunded transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function refunded()
    {
        return $this->model->where('status', 'refunded');
    }

    /**
     * Get failed transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function failed()
    {
        return $this->model->where('status', 'failed');
    }

    /**
     * Get transactions by payment method.
     *
     * @param  string  $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function byPaymentMethod(string $method)
    {
        return $this->model->where('payment_method', $method);
    }

    /**
     * Get revenue statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_transactions' => $this->model->count(),
            'completed_transactions' => $this->model->where('status', 'completed')->count(),
            'pending_transactions' => $this->model->where('status', 'pending')->count(),
            'failed_transactions' => $this->model->where('status', 'failed')->count(),
            'refunded_transactions' => $this->model->where('status', 'refunded')->count(),
            'total_amount' => $this->model->where('status', 'completed')->sum('amount'),
            'total_platform_fees' => $this->model->where('status', 'completed')->sum('platform_fee'),
            'total_seller_revenue' => $this->model->where('status', 'completed')->sum('seller_revenue'),
        ];
    }

    /**
     * Get recent transactions.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent(int $limit = 10)
    {
        return $this->scopeQuery(function ($query) use ($limit) {
            return $query->with(['extension', 'buyer', 'seller'])
                ->orderBy('created_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get transactions within a date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function betweenDates(string $startDate, string $endDate)
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate]);
    }
}
