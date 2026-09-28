import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Product } from '../../core/models/product.model';
import { ProductCard } from './product-card';

describe('ProductCard', () => {
  let component: ProductCard;
  let fixture: ComponentFixture<ProductCard>;

  const product: Product = {
    id: 1,
    name: 'Cadena cubana',
    slug: 'cadena-cubana',
    description: null,
    material: 'Oro laminado',
    base_price: 45000,
    total_stock: 12,
    image: 'https://ejemplo.com/foto.jpg',
    category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({ imports: [ProductCard] }).compileComponents();

    fixture = TestBed.createComponent(ProductCard);
    component = fixture.componentInstance;
    component.product = product;
    fixture.detectChanges();
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('muestra el nombre y el precio del producto', () => {
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Cadena cubana');
  });

  it('muestra el aviso de sin stock cuando total_stock es 0', async () => {
    const sinStockFixture = TestBed.createComponent(ProductCard);
    sinStockFixture.componentInstance.product = { ...product, total_stock: 0 };
    sinStockFixture.detectChanges();
    await sinStockFixture.whenStable();

    const text = (sinStockFixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Sin stock');
  });
});
