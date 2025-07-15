<?php

namespace Tests\Unit;
use App\Models\Product;
use App\Services\SpreadsheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Mockery;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SpreadsheetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->service = new SpreadsheetService;
    }
    
}