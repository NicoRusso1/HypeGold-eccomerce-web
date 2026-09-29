<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_pendiente_puede_pasar_a_pagado_o_cancelado(): void
    {
        $this->assertTrue(OrderStatus::Pendiente->puedeTransicionarA(OrderStatus::Pagado));
        $this->assertTrue(OrderStatus::Pendiente->puedeTransicionarA(OrderStatus::Cancelado));
        $this->assertFalse(OrderStatus::Pendiente->puedeTransicionarA(OrderStatus::Enviado));
        $this->assertFalse(OrderStatus::Pendiente->puedeTransicionarA(OrderStatus::Entregado));
    }

    public function test_pagado_puede_pasar_a_enviado_o_cancelado(): void
    {
        $this->assertTrue(OrderStatus::Pagado->puedeTransicionarA(OrderStatus::Enviado));
        $this->assertTrue(OrderStatus::Pagado->puedeTransicionarA(OrderStatus::Cancelado));
        $this->assertFalse(OrderStatus::Pagado->puedeTransicionarA(OrderStatus::Entregado));
    }

    public function test_enviado_solo_puede_pasar_a_entregado(): void
    {
        $this->assertTrue(OrderStatus::Enviado->puedeTransicionarA(OrderStatus::Entregado));
        $this->assertFalse(OrderStatus::Enviado->puedeTransicionarA(OrderStatus::Cancelado));
        $this->assertFalse(OrderStatus::Enviado->puedeTransicionarA(OrderStatus::Pagado));
    }

    public function test_entregado_y_cancelado_son_estados_finales(): void
    {
        $this->assertSame([], OrderStatus::Entregado->transicionesValidas());
        $this->assertSame([], OrderStatus::Cancelado->transicionesValidas());
    }

    public function test_ningun_estado_puede_transicionar_a_si_mismo(): void
    {
        foreach (OrderStatus::cases() as $estado) {
            $this->assertFalse($estado->puedeTransicionarA($estado));
        }
    }
}
