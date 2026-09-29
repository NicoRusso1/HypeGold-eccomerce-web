import { CurrencyPipe } from '@angular/common';
import {
  Component,
  ElementRef,
  HostListener,
  Input,
  inject,
  signal,
} from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { Subject, catchError, debounceTime, distinctUntilChanged, of, switchMap } from 'rxjs';
import { Category } from '../../core/models/category.model';
import { Product } from '../../core/models/product.model';
import { ProductService } from '../../core/services/product.service';

const LARGO_MINIMO = 2;

@Component({
  imports: [CurrencyPipe, RouterLink],
  selector: 'app-search-box',
  styleUrl: './search-box.css',
  templateUrl: './search-box.html',
})
export class SearchBox {
  @Input() categories: Category[] = [];

  private readonly productService = inject(ProductService);
  private readonly router = inject(Router);
  private readonly elementRef = inject(ElementRef);

  private readonly queryChanges = new Subject<string>();

  protected readonly query = signal('');
  protected readonly suggestions = signal<Product[]>([]);
  protected readonly loading = signal(false);
  protected readonly searched = signal(false);
  protected readonly open = signal(false);

  constructor() {
    this.queryChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged(),
        switchMap((q) => {
          if (q.trim().length < LARGO_MINIMO) {
            this.loading.set(false);
            this.searched.set(false);
            return of<Product[]>([]);
          }

          this.loading.set(true);

          return this.productService.searchSuggestions(q.trim()).pipe(
            catchError(() => of<Product[]>([])),
          );
        }),
      )
      .subscribe((productos) => {
        this.loading.set(false);
        this.searched.set(this.query().trim().length >= LARGO_MINIMO);
        this.suggestions.set(productos);
      });
  }

  @HostListener('document:click', ['$event.target'])
  protected clickFuera(target: EventTarget | null): void {
    if (target && !this.elementRef.nativeElement.contains(target)) {
      this.open.set(false);
    }
  }

  protected onInput(value: string): void {
    this.query.set(value);
    this.open.set(true);
    this.queryChanges.next(value);
  }

  protected onFocus(): void {
    if (this.query().trim().length >= LARGO_MINIMO) {
      this.open.set(true);
    }
  }

  /** Cierra y resetea el buscador; la navegación la hace routerLink en el template. */
  protected cerrarAlNavegar(): void {
    this.cerrar();
  }

  protected verTodosLosResultados(): void {
    const q = this.query().trim();

    if (!q) {
      return;
    }

    this.cerrar();
    this.router.navigate(['/catalogo'], { queryParams: { search: q } });
  }

  protected onSubmit(): void {
    this.verTodosLosResultados();
  }

  private cerrar(): void {
    this.open.set(false);
    this.query.set('');
    this.suggestions.set([]);
    this.searched.set(false);
  }
}
