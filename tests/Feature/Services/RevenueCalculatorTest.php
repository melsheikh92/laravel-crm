<?php

use Webkul\Marketplace\Services\RevenueCalculator;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->transactionRepository = Mockery::mock(ExtensionTransactionRepository::class);
    $this->extensionRepository = Mockery::mock(ExtensionRepository::class);
    $this->service = new RevenueCalculator($this->transactionRepository, $this->extensionRepository);
});

afterEach(function () {
    Mockery::close();
});

describe('calculateRevenueSplit', function () {
    it('calculates revenue split correctly with default platform fee', function () {
        $result = $this->service->calculateRevenueSplit(100.00);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['amount'])->toBe(100.00)
            ->and($result['platform_fee'])->toBe(30.00)
            ->and($result['platform_fee_percentage'])->toBe(30.0)
            ->and($result['seller_revenue'])->toBe(70.00)
            ->and($result['seller_revenue_percentage'])->toBe(70.0);
    });

    it('calculates revenue split with custom platform fee', function () {
        $result = $this->service->calculateRevenueSplit(100.00, 20.0);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['platform_fee'])->toBe(20.00)
            ->and($result['seller_revenue'])->toBe(80.00);
    });

    it('returns error for negative amount', function () {
        $result = $this->service->calculateRevenueSplit(-50.00);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Amount cannot be negative');
    });

    it('returns error for invalid platform fee percentage', function () {
        $result = $this->service->calculateRevenueSplit(100.00, 150.0);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Platform fee percentage must be between 0 and 100');
    });

    it('returns error for negative platform fee percentage', function () {
        $result = $this->service->calculateRevenueSplit(100.00, -10.0);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Platform fee percentage must be between 0 and 100');
    });

    it('handles zero amount', function () {
        $result = $this->service->calculateRevenueSplit(0.00);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['platform_fee'])->toBe(0.00)
            ->and($result['seller_revenue'])->toBe(0.00);
    });

    it('rounds amounts to two decimal places', function () {
        $result = $this->service->calculateRevenueSplit(33.33, 30.0);

        expect($result['success'])->toBeTrue()
            ->and($result['platform_fee'])->toBe(10.00)
            ->and($result['seller_revenue'])->toBe(23.33);
    });
});

describe('processRefund', function () {
    it('processes refund successfully', function () {
        $mockTransaction = Mockery::mock();
        $mockTransaction->id = 1;
        $mockTransaction->transaction_id = 'TXN-123';
        $mockTransaction->amount = 100.00;
        $mockTransaction->platform_fee = 30.00;
        $mockTransaction->seller_revenue = 70.00;
        $mockTransaction->payment_method = 'stripe';
        $mockTransaction->extension_id = 1;
        $mockTransaction->buyer_id = 1;
        $mockTransaction->seller_id = 2;

        $mockTransaction->shouldReceive('isRefunded')->andReturn(false);
        $mockTransaction->shouldReceive('isCompleted')->andReturn(true);
        $mockTransaction->shouldReceive('markAsRefunded')->once();

        $mockRefundTransaction = Mockery::mock();
        $mockRefundTransaction->amount = -100.00;

        $this->transactionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockTransaction);

        $this->transactionRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockRefundTransaction);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $result = $this->service->processRefund(1, 'Customer request');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result)->toHaveKeys(['original_transaction', 'refund_transaction', 'refund_amount']);
    });

    it('returns error for already refunded transaction', function () {
        $mockTransaction = Mockery::mock();
        $mockTransaction->shouldReceive('isRefunded')->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockTransaction);

        $result = $this->service->processRefund(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Transaction is already refunded');
    });

    it('returns error for non-completed transaction', function () {
        $mockTransaction = Mockery::mock();
        $mockTransaction->shouldReceive('isRefunded')->andReturn(false);
        $mockTransaction->shouldReceive('isCompleted')->andReturn(false);

        $this->transactionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockTransaction);

        $result = $this->service->processRefund(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Only completed transactions can be refunded');
    });

    it('rolls back on error', function () {
        $mockTransaction = Mockery::mock();
        $mockTransaction->shouldReceive('isRefunded')->andReturn(false);
        $mockTransaction->shouldReceive('isCompleted')->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockTransaction);

        $this->transactionRepository
            ->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('error')->once();

        $result = $this->service->processRefund(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to process refund');
    });
});

describe('generateSellerReport', function () {
    it('generates seller revenue report', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn(collect());

        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->with(1)
            ->andReturnSelf();

        $this->transactionRepository
            ->shouldReceive('completed')
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->andReturn($mockQuery);

        $result = $this->service->generateSellerReport(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result)->toHaveKeys(['seller_id', 'period', 'summary', 'by_extension', 'by_month'])
            ->and($result['summary'])->toHaveKeys([
                'total_transactions',
                'total_gross_sales',
                'total_platform_fees',
                'total_revenue',
                'total_refunds',
                'refund_count',
                'net_revenue',
                'average_transaction'
            ]);
    });

    it('handles date range filtering', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')->times(2)->andReturnSelf();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn(collect());

        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->with(1)
            ->andReturnSelf();

        $this->transactionRepository
            ->shouldReceive('completed')
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->andReturn($mockQuery);

        $result = $this->service->generateSellerReport(1, '2024-01-01', '2024-12-31');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['period']['start_date'])->toBe('2024-01-01')
            ->and($result['period']['end_date'])->toBe('2024-12-31');
    });

    it('handles errors gracefully', function () {
        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->andThrow(new \Exception('Database error'));

        Log::shouldReceive('error')->once();

        $result = $this->service->generateSellerReport(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to generate report');
    });
});

describe('generatePlatformReport', function () {
    it('generates platform revenue report', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn(collect());

        $this->transactionRepository
            ->shouldReceive('completed')
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->andReturn($mockQuery);

        $result = $this->service->generatePlatformReport();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result)->toHaveKeys([
                'period',
                'summary',
                'by_payment_method',
                'top_sellers',
                'top_extensions',
                'by_month'
            ])
            ->and($result['summary'])->toHaveKeys([
                'total_transactions',
                'total_gross_sales',
                'total_platform_fees',
                'total_seller_revenue',
                'total_refunds',
                'refund_count',
                'net_platform_fees',
                'average_platform_fee',
                'average_transaction'
            ]);
    });
});

describe('generateExtensionReport', function () {
    it('generates extension revenue report', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;
        $mockExtension->name = 'Test Extension';

        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn(collect());

        $this->extensionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockExtension);

        $this->transactionRepository
            ->shouldReceive('forExtension')
            ->with(1)
            ->andReturnSelf();

        $this->transactionRepository
            ->shouldReceive('completed')
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->andReturn($mockQuery);

        $result = $this->service->generateExtensionReport(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['extension_id'])->toBe(1)
            ->and($result['extension_name'])->toBe('Test Extension')
            ->and($result)->toHaveKeys(['period', 'summary', 'by_month']);
    });
});

describe('calculateSellerRevenue', function () {
    it('calculates total seller revenue', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('sum')->with('seller_revenue')->andReturn(500.00);
        $mockQuery->shouldReceive('count')->andReturn(5);

        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->with(1)
            ->andReturnSelf();

        $this->transactionRepository
            ->shouldReceive('completed')
            ->andReturn($mockQuery);

        $result = $this->service->calculateSellerRevenue(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['seller_id'])->toBe(1)
            ->and($result['total_revenue'])->toBe(500.00)
            ->and($result['total_transactions'])->toBe(5);
    });

    it('handles errors when calculating revenue', function () {
        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->andThrow(new \Exception('Database error'));

        $result = $this->service->calculateSellerRevenue(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to calculate seller revenue');
    });
});

describe('calculatePendingPayouts', function () {
    it('calculates pending payouts for seller', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('sum')->with('seller_revenue')->andReturn(1000.00);
        $mockQuery->shouldReceive('count')->andReturn(10);

        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->with(1)
            ->andReturnSelf();

        $this->transactionRepository
            ->shouldReceive('completed')
            ->times(2)
            ->andReturn($mockQuery);

        $result = $this->service->calculatePendingPayouts(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['seller_id'])->toBe(1)
            ->and($result['pending_revenue'])->toBe(1000.00)
            ->and($result['transaction_count'])->toBe(10);
    });

    it('handles errors when calculating pending payouts', function () {
        $this->transactionRepository
            ->shouldReceive('forSeller')
            ->andThrow(new \Exception('Database error'));

        $result = $this->service->calculatePendingPayouts(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to calculate pending payouts');
    });
});

describe('setPlatformFeePercentage', function () {
    it('sets valid platform fee percentage', function () {
        $result = $this->service->setPlatformFeePercentage(25.0);

        expect($result)->toBeTrue();
    });

    it('rejects invalid platform fee percentage', function () {
        $result = $this->service->setPlatformFeePercentage(150.0);

        expect($result)->toBeFalse();
    });

    it('rejects negative platform fee percentage', function () {
        $result = $this->service->setPlatformFeePercentage(-10.0);

        expect($result)->toBeFalse();
    });

    it('accepts zero platform fee percentage', function () {
        $result = $this->service->setPlatformFeePercentage(0.0);

        expect($result)->toBeTrue();
    });

    it('accepts 100 percent platform fee', function () {
        $result = $this->service->setPlatformFeePercentage(100.0);

        expect($result)->toBeTrue();
    });
});

describe('getRevenueStatistics', function () {
    it('returns revenue statistics', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('count')->andReturn(10);
        $mockQuery->shouldReceive('sum')->andReturn(1000.00);

        $this->transactionRepository
            ->shouldReceive('completed')
            ->times(4)
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->once()
            ->andReturn($mockQuery);

        $result = $this->service->getRevenueStatistics();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['statistics'])->toHaveKeys([
                'total_transactions',
                'total_gross_revenue',
                'total_platform_fees',
                'total_seller_revenue',
                'total_refunds',
                'net_revenue',
                'average_transaction'
            ]);
    });

    it('handles zero transactions', function () {
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('count')->andReturn(0);
        $mockQuery->shouldReceive('sum')->andReturn(0);

        $this->transactionRepository
            ->shouldReceive('completed')
            ->times(4)
            ->andReturn($mockQuery);

        $this->transactionRepository
            ->shouldReceive('refunded')
            ->once()
            ->andReturn($mockQuery);

        $result = $this->service->getRevenueStatistics();

        expect($result['success'])->toBeTrue()
            ->and($result['statistics']['average_transaction'])->toBe(0);
    });

    it('handles errors when getting statistics', function () {
        $this->transactionRepository
            ->shouldReceive('completed')
            ->andThrow(new \Exception('Database error'));

        $result = $this->service->getRevenueStatistics();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to get revenue statistics');
    });
});
