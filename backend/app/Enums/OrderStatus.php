<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pendiente = 'pendiente';
    case Pagado = 'pagado';
    case Enviado = 'enviado';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';

    /**
     * Transiciones válidas desde este estado. El flujo normal es
     * pendiente → pagado → enviado → entregado; cancelado solo se puede
     * aplicar antes de que el pedido se haya enviado. Entregado y
     * cancelado son estados finales.
     *
     * @return list<self>
     */
    public function transicionesValidas(): array
    {
        return match ($this) {
            self::Pendiente => [self::Pagado, self::Cancelado],
            self::Pagado => [self::Enviado, self::Cancelado],
            self::Enviado => [self::Entregado],
            self::Entregado, self::Cancelado => [],
        };
    }

    public function puedeTransicionarA(self $nuevo): bool
    {
        return in_array($nuevo, $this->transicionesValidas(), true);
    }
}
