export interface VentaPorDia {
  fecha: string;
  total: number;
}

export interface ProductoMasVendido {
  product_name: string;
  cantidad: number;
}

export interface DashboardResumen {
  total_ventas: number;
  cantidad_ventas: number;
  pedidos_por_estado: Record<string, number>;
  productos_mas_vendidos: ProductoMasVendido[];
  ventas_por_dia: VentaPorDia[];
}

export interface DashboardMeta {
  from: string;
  to: string;
}
