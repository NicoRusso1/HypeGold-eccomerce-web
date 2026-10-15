import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DashboardResumen, VentaPorDia } from '../../../core/models/admin-dashboard.model';
import { ordenStatusLabel } from '../../../core/models/order-status';
import { AdminDashboardService } from '../../../core/services/admin-dashboard.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

interface BarraGrafico {
  fecha: string;
  total: number;
  alturaPorcentaje: number;
}

function hoyISO(offsetDias = 0): string {
  const fecha = new Date();
  fecha.setDate(fecha.getDate() + offsetDias);
  return fecha.toISOString().slice(0, 10);
}

@Component({
  imports: [CurrencyPipe, DatePipe, FormsModule, AdminNav],
  selector: 'app-admin-dashboard',
  styleUrl: './admin-dashboard.css',
  templateUrl: './admin-dashboard.html',
})
export class AdminDashboardPage {
  private readonly dashboardService = inject(AdminDashboardService);

  protected readonly ordenStatusLabel = ordenStatusLabel;
  protected readonly estados = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

  protected readonly desde = signal(hoyISO(-29));
  protected readonly hasta = signal(hoyISO());

  protected readonly resumen = signal<DashboardResumen | null>(null);
  protected readonly loading = signal(true);
  protected readonly error = signal(false);

  protected readonly grafico = computed<BarraGrafico[]>(() => {
    const ventas = this.resumen()?.ventas_por_dia ?? [];
    const max = Math.max(1, ...ventas.map((v: VentaPorDia) => v.total));

    return ventas.map((v) => ({
      fecha: v.fecha,
      total: v.total,
      alturaPorcentaje: Math.round((v.total / max) * 100),
    }));
  });

  constructor() {
    this.cargar();
  }

  protected aplicarFiltro(): void {
    this.cargar();
  }

  protected reintentar(): void {
    this.cargar();
  }

  private cargar(): void {
    this.loading.set(true);
    this.error.set(false);

    this.dashboardService.getResumen(this.desde(), this.hasta()).subscribe({
      next: ({ data }) => {
        this.resumen.set(data);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.error.set(true);
      },
    });
  }
}
