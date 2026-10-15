<?php

namespace Tests;

use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MercadoPago\Resources\Preference;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Por defecto, ningún test le pega a la API real de Mercado Pago.
        // Los tests que sí necesitan probar la integración (creación de
        // preferencia, webhook) redefinen este mock con sus propias
        // expectativas.
        $this->mock(MercadoPagoService::class, function ($mock) {
            $preferencia = new Preference();
            $preferencia->id = 'pref-test';
            $preferencia->init_point = 'https://mp.example/pago/pref-test';

            $mock->shouldReceive('crearPreferencia')->andReturn($preferencia)->byDefault();
        });
    }
}
