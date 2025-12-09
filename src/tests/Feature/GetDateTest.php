<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class GetDateTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2025-12-01 09:00:00');

        $response = $this->get('/attendance');

        $expectedDate = Carbon::now()->isoFormat('YYYY年M月DD日(dd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
