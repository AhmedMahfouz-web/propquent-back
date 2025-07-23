<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\UserTransaction;
use App\Repositories\UserTransactionRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new UserTransactionRepository();
    }

    public function testGetUserTransactions()
    {
        // Create a user
        $user = User::factory()->create();

        // Create some transactions
        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'deposit',
            'amount' => 1000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'withdrawal',
            'amount' => 500,
            'transaction_date' => Carbon::now(),
        ]);

        // Test getting all transactions for the user
        $transactions = $this->repository->getUserTransactions($user->id);
        $this->assertCount(2, $transactions);

        // Test filtering by transaction type
        $deposits = $this->repository->getUserTransactions($user->id, ['transaction_type' => 'deposit']);
        $this->assertCount(1, $deposits);
        $this->assertEquals('deposit', $deposits->first()->transaction_type);

        $withdrawals = $this->repository->getUserTransactions($user->id, ['transaction_type' => 'withdrawal']);
        $this->assertCount(1, $withdrawals);
        $this->assertEquals('withdrawal', $withdrawals->first()->transaction_type);
    }

    public function testCalculateMonthlyData()
    {
        // Create a user
        $user = User::factory()->create();

        // Create transactions in different months
        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'deposit',
            'amount' => 1000,
            'transaction_date' => Carbon::create(2025, 6, 15),
        ]);

        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'deposit',
            'amount' => 2000,
            'transaction_date' => Carbon::create(2025, 7, 10),
        ]);

        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'withdrawal',
            'amount' => 500,
            'transaction_date' => Carbon::create(2025, 7, 20),
        ]);

        // Test monthly data calculation
        $monthlyData = $this->repository->calculateMonthlyData($user->id);

        $this->assertCount(2, $monthlyData);

        // Check June data
        $juneData = $monthlyData->firstWhere('year_month', '2025-06');
        $this->assertNotNull($juneData);
        $this->assertEquals(1000, $juneData->total_deposits);
        $this->assertEquals(0, $juneData->total_withdrawals);
        $this->assertEquals(1000, $juneData->net_deposits);

        // Check July data
        $julyData = $monthlyData->firstWhere('year_month', '2025-07');
        $this->assertNotNull($julyData);
        $this->assertEquals(2000, $julyData->total_deposits);
        $this->assertEquals(500, $julyData->total_withdrawals);
        $this->assertEquals(1500, $julyData->net_deposits);
    }

    public function testIsActiveInvestor()
    {
        // Create a user
        $user = User::factory()->create();

        // Create transactions with net positive deposit
        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'deposit',
            'amount' => 1000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => 'withdrawal',
            'amount' => 500,
            'transaction_date' => Carbon::now(),
        ]);

        // Test active investor status
        $isActive = $this->repository->isActiveInvestor($user->id);
        $this->assertTrue($isActive);

        // Create another user with net zero deposit
        $user2 = User::factory()->create();

        UserTransaction::factory()->create([
            'user_id' => $user2->id,
            'transaction_type' => 'deposit',
            'amount' => 1000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        UserTransaction::factory()->create([
            'user_id' => $user2->id,
            'transaction_type' => 'withdrawal',
            'amount' => 1000,
            'transaction_date' => Carbon::now(),
        ]);

        // Test inactive investor status
        $isActive = $this->repository->isActiveInvestor($user2->id);
        $this->assertFalse($isActive);
    }
}
