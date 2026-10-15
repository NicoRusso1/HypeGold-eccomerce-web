import { Component, computed, input } from '@angular/core';

@Component({
  selector: 'app-star-rating',
  styleUrl: './star-rating.css',
  templateUrl: './star-rating.html',
})
export class StarRating {
  readonly rating = input.required<number>();

  protected readonly estrellas = computed(() => {
    const valor = Math.round(this.rating());
    return [1, 2, 3, 4, 5].map((n) => n <= valor);
  });
}
