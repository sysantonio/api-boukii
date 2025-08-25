<?php

namespace Tests\Feature\V5\Context;

use App\Models\School;
use App\Models\User;
use App\Services\ContextService;
use App\Support\Pivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        DB::table(Pivot::USER_SCHOOLS)->insert([
            'user_id' => $this->user->id,
            'school_id' => $this->schoolOwned->id,
        ]);

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
    public function lista_todas_las_schools_sin_paginacion_cuando_all_true()
    {
        $response = $this->getJson('/api/v5/me/schools?all=true', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertArrayNotHasKey('meta', $response->json());
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
        $response->assertJson([
            'type' => 'about:blank',
            'title' => 'Forbidden',
            'status' => 403,
        ]);
    }

    /** @test */
    public function retorna_404_si_school_no_existe()
    {
        $nonExistingId = School::max('id') + 1;

        $response = $this->postJson('/api/v5/context/school', [
            'school_id' => $nonExistingId,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'type' => 'about:blank',
            'title' => 'Not Found',
            'status' => 404,
        ]);
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

        $contextService = app(ContextService::class);
        $context = $contextService->get($this->user);

        $this->assertEquals($this->schoolOwned->id, $context['school_id']);
        $this->assertNull($context['season_id']);
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
