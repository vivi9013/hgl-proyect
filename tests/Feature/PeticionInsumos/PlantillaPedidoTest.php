<?php

namespace Tests\Feature\PeticionInsumos;

use App\Models\User;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\Insumo;
use App\Models\PeticionInsumos\PlantillaPedido;
use App\Models\PeticionInsumos\DetallePlantillaPedido;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlantillaPedidoTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $area;
    protected $subarea;
    protected $insumo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::create([
            'nombre_usuario' => 'testuser_' . uniqid(),
            'contra' => md5('password'),
            'activo' => 1,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'usuario' => 1,
        ]);

        $this->area = AreaAbastecimiento::create([
            'nombre' => 'Área de Prueba ' . uniqid(),
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);

        $this->subarea = SubareaAbastecimiento::create([
            'nombre' => 'Subárea de Prueba ' . uniqid(),
            'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);

        $this->insumo = Insumo::create([
            'clave' => 'INS-' . rand(1000, 9999),
            'descripcion' => 'Insumo de Prueba ' . uniqid(),
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);
    }

    public function test_se_puede_crear_una_plantilla_de_pedido_con_area_y_subarea_activas()
    {
        $response = $this->actingAs($this->user)
            ->post(route('plantillas_pedido.store'), [
                'nombre' => 'Plantilla Test ' . uniqid(),
                'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
                'id_subarea_abastecimiento' => $this->subarea->id_subarea_abastecimiento,
                'descripcion' => 'Descripción de prueba',
            ]);

        $response->assertRedirect(route('plantillas_pedido.index'));
        $response->assertSessionHas('exitog');

        $this->assertDatabaseHas('plantilla_pedidos', [
            'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $this->subarea->id_subarea_abastecimiento,
        ]);
    }

    public function test_no_permite_nombres_duplicados_de_plantilla_en_la_misma_area()
    {
        $nombreCompartido = 'Plantilla Duplicada ' . uniqid();

        PlantillaPedido::create([
            'nombre' => $nombreCompartido,
            'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('plantillas_pedido.index'))
            ->post(route('plantillas_pedido.store'), [
                'nombre' => $nombreCompartido,
                'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            ]);

        $response->assertSessionHasErrors(['nombre']);
    }

    public function test_se_puede_asignar_y_actualizar_insumo_en_la_plantilla()
    {
        $plantilla = PlantillaPedido::create([
            'nombre' => 'Plantilla Insumo ' . uniqid(),
            'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('plantillas_pedido.insumo.store', $plantilla->id_plantilla_pedido), [
                'id_insumo' => $this->insumo->id_insumo,
                'cantidad' => 15,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('detalle_plantilla_pedidos', [
            'id_plantilla_pedido' => $plantilla->id_plantilla_pedido,
            'id_insumo' => $this->insumo->id_insumo,
            'cantidad' => 15,
        ]);
    }

    public function test_se_puede_eliminar_un_insumo_de_la_plantilla()
    {
        $plantilla = PlantillaPedido::create([
            'nombre' => 'Plantilla a eliminar insumo ' . uniqid(),
            'id_area_abastecimiento' => $this->area->id_area_abastecimiento,
            'activo' => 1,
            'fecha_registro' => now()->toDateString(),
        ]);

        $detalle = DetallePlantillaPedido::create([
            'id_plantilla_pedido' => $plantilla->id_plantilla_pedido,
            'id_insumo' => $this->insumo->id_insumo,
            'cve_insumo' => $this->insumo->clave,
            'cantidad' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('plantillas_pedido.detalle.destroy', $detalle->id_detalle_plantilla_pedido));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('detalle_plantilla_pedidos', [
            'id_detalle_plantilla_pedido' => $detalle->id_detalle_plantilla_pedido,
        ]);
    }
}
