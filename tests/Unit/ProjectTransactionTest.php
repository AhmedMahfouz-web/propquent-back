<?php

namespace Tests\Unit;

use App\Models\ProjectTransaction;
use App\Models\Project;
use App\Models\Developer;
use App\Models\TransactionWhat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class ProjectTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_transaction_belongs_to_project()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        $transactionWhat = TransactionWhat::factory()->create();
        
        $transaction = ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'what_id' => $transactionWhat->id,
        ]);

        $this->assertInstanceOf(Project::class, $transaction->project);
        $this->assertEquals($project->key, $transaction->project->key);
    }

    public function test_project_transaction_belongs_to_transaction_what()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        $transactionWhat = TransactionWhat::factory()->create();
        
        $transaction = ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'what_id' => $transactionWhat->id,
        ]);

        $this->assertInstanceOf(TransactionWhat::class, $transaction->transactionWhat);
        $this->assertEquals($transactionWhat->id, $transaction->transactionWhat->id);
    }

    public function test_project_transaction_validates_positive_amount()
    {
        $this->expectException(ValidationException::class);
        
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        $transactionWhat = TransactionWhat::factory()->create();
        
        ProjectTransaction::create([
            'project_key' => $project->key,
            'what_id' => $transactionWhat->id,
            'type' => 'payment',
            'amount' => -100,
            'transaction_date' => now(),
        ]);
    }

    public function test_project_transaction_casts_attributes_correctly()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        $transactionWhat = TransactionWhat::factory()->create();
        
        $transaction = ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'what_id' => $transactionWhat->id,
            'amount' => '1500.50',
            'due_date' => '2024-01-01',
            'actual_date' => '2024-01-02',
            'transaction_date' => '2024-01-01',
        ]);

        $this->assertEquals('1500.50', $transaction->amount);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->due_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->actual_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->transaction_date);
    }
}
