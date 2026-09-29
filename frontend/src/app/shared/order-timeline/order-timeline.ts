import { Component, Input, computed, signal } from '@angular/core';
import { ORDER_STATUS_LABELS, ORDER_STATUS_STEPS } from '../../core/models/order-status';

@Component({
  selector: 'app-order-timeline',
  styleUrl: './order-timeline.css',
  templateUrl: './order-timeline.html',
})
export class OrderTimeline {
  protected readonly steps = ORDER_STATUS_STEPS;
  protected readonly labels = ORDER_STATUS_LABELS;

  private readonly statusSignal = signal('pendiente');

  @Input({ required: true })
  set status(value: string) {
    this.statusSignal.set(value);
  }

  protected readonly isCancelado = computed(() => this.statusSignal() === 'cancelado');

  protected readonly currentIndex = computed(() => {
    const index = this.steps.indexOf(this.statusSignal() as (typeof this.steps)[number]);
    return index === -1 ? 0 : index;
  });
}
