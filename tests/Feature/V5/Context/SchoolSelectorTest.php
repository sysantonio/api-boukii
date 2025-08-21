<?php

namespace Tests\Feature\V5\Context;

use App\Models\PersonalAccessToken;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SchoolSelectorTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private User $user;
    private School $schoolOwned;
    private School $otherSchool;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'schools.view']);
        Permission::create(['name' => 'schools.switch']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['schools.view', 'schools.switch']);

        $this->schoolOwned = School::factory()->create(['active' => true]);
        $this->otherSchool = School::factory()->create(['active' => true]);

        $this->user->schools()->attach($this->schoolOwned->id);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function lista_solo_schools_del_usuario()
    {
        $response = $this->getJson('/api/v5/me/schools', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->schoolOwned->id, $data[0]['id']);
    }

    /** @test */
    public function retorna_403_si_intenta_cambiar_a_una_school_sin_pertenencia()
    {
        $response = $this->postJson('/api/v5/context/school', [
            'school_id' => $this->otherSchool->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['status' => 403]);
    }

    /** @test */
    public function cambio_valido_actualiza_el_contexto()
    {
        $response = $this->postJson('/api/v5/context/school', [
            'school_id' => $this->schoolOwned->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'school_id' => $this->schoolOwned->id,
            'season_id' => null,
        ]);

        $tokenId = explode('|', $this->token)[0];
        $tokenModel = PersonalAccessToken::find($tokenId);
        $this->assertEquals($this->schoolOwned->id, $tokenModel->context_data['school_id']);
        $this->assertNull($tokenModel->context_data['season_id']);
    }

    /** @test */
    public function get_context_devuelve_estado_actual()
    {
        // Set context first
        $this->postJson('/api/v5/context/school', [
            'school_id' => $this->schoolOwned->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response = $this->getJson('/api/v5/context', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'school_id' => $this->schoolOwned->id,
            'season_id' => null,
        ]);
    }
}
