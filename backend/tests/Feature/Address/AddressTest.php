<?php

namespace Tests\Feature\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private function datosDireccion(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Casa',
            'street' => 'Av. Siempre Viva 742',
            'city' => 'Concordia',
            'province' => 'Entre Ríos',
            'postal_code' => 'E3200',
            'phone' => '345 4040162',
        ], $overrides);
    }

    public function test_un_visitante_no_autenticado_no_puede_ver_sus_direcciones(): void
    {
        $this->getJson('/api/addresses')->assertUnauthorized();
    }

    public function test_un_cliente_puede_crear_una_direccion(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/addresses', $this->datosDireccion());

        $response->assertCreated()
            ->assertJsonPath('data.street', 'Av. Siempre Viva 742')
            ->assertJsonPath('data.city', 'Concordia');
        $this->assertDatabaseCount('addresses', 1);
    }

    public function test_no_se_puede_crear_una_direccion_sin_los_campos_requeridos(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/addresses', ['label' => 'Casa']);

        $response->assertStatus(422)->assertJsonValidationErrors(['street', 'city', 'province', 'postal_code', 'phone']);
    }

    public function test_un_cliente_ve_solo_sus_propias_direcciones(): void
    {
        $user = User::factory()->create();
        Address::factory()->for($user)->create();
        Address::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/addresses');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_marcar_una_direccion_como_predeterminada_desmarca_las_demas(): void
    {
        $user = User::factory()->create();
        $primera = Address::factory()->for($user)->create(['is_default' => true]);

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/addresses', $this->datosDireccion(['is_default' => true]));

        $response->assertCreated()->assertJsonPath('data.is_default', true);
        $this->assertFalse($primera->fresh()->is_default);
    }

    public function test_un_cliente_puede_actualizar_su_direccion(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();

        Sanctum::actingAs($user);
        $response = $this->putJson("/api/addresses/{$address->id}", $this->datosDireccion(['city' => 'Colón']));

        $response->assertOk()->assertJsonPath('data.city', 'Colón');
    }

    public function test_un_cliente_no_puede_editar_la_direccion_de_otro_usuario(): void
    {
        $address = Address::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/addresses/{$address->id}", $this->datosDireccion())->assertNotFound();
    }

    public function test_un_cliente_puede_eliminar_su_direccion(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();

        Sanctum::actingAs($user);
        $response = $this->deleteJson("/api/addresses/{$address->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_un_cliente_no_puede_eliminar_la_direccion_de_otro_usuario(): void
    {
        $address = Address::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/addresses/{$address->id}")->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }
}
