<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LiveRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_live_revision_requires_authentication(): void
    {
        $this->getJson(route('live.revision'))->assertUnauthorized();
    }

    public function test_live_revision_returns_a_fingerprint(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('live.revision'))
            ->assertOk()
            ->assertJsonStructure(['revision']);
    }

    public function test_live_revision_changes_when_tracked_data_changes(): void
    {
        $user = User::factory()->create();

        $before = $this->actingAs($user)->getJson(route('live.revision'))->json('revision');

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        Courier::create([
            'user_id' => $courierUser->id,
            'vehicle_type' => 'Mobil Box',
            'vehicle_number' => 'DA 8123 AB',
            'is_available' => true,
        ]);

        $after = $this->actingAs($user)->getJson(route('live.revision'))->json('revision');

        $this->assertNotSame($before, $after);
    }
}
