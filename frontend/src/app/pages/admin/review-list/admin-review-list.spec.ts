import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { ConfirmDialogService } from '../../../core/services/confirm-dialog.service';
import { AdminReviewListPage } from './admin-review-list';

describe('AdminReviewListPage', () => {
  let component: AdminReviewListPage;
  let fixture: ComponentFixture<AdminReviewListPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminReviewListPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminReviewListPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  function flushInitial(reviews: unknown[] = []): void {
    httpMock.expectOne((r) => r.url.endsWith('/admin/products') && r.method === 'GET').flush({
      data: [{ id: 1, name: 'Cadena cubana', slug: 'cadena-cubana', description: null, material: 'Oro', base_price: 45000, active: true, category: null }],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });
    httpMock.expectOne((r) => r.url.endsWith('/admin/reviews') && r.method === 'GET').flush({ data: reviews });
  }

  it('should create and load products and reviews', () => {
    expect(component).toBeTruthy();

    flushInitial([
      { id: 1, author_name: 'Marcela G.', rating: 5, comment: 'Hermosa', created_at: '2026-10-01T00:00:00Z', product: { id: 1, name: 'Cadena cubana' } },
    ]);

    expect(component['productos']()).toHaveLength(1);
    expect(component['reviews']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('crea una resena y recarga el listado', () => {
    flushInitial([]);

    component['form'].setValue({ product_id: 1, author_name: 'Marcela G.', rating: 5, comment: '' });
    component['guardar']();

    const createReq = httpMock.expectOne((r) => r.url.endsWith('/admin/reviews') && r.method === 'POST');
    expect(createReq.request.body).toEqual({ product_id: 1, author_name: 'Marcela G.', rating: 5, comment: null });
    createReq.flush({
      data: { id: 2, author_name: 'Marcela G.', rating: 5, comment: null, created_at: '2026-10-01T00:00:00Z', product: { id: 1, name: 'Cadena cubana' } },
    });

    httpMock.expectOne((r) => r.url.endsWith('/admin/reviews') && r.method === 'GET').flush({ data: [] });

    expect(component['saving']()).toBe(false);
    expect(component['editingId']()).toBeNull();
  });

  it('filtra las resenas por producto', () => {
    flushInitial([]);

    component['onCambioFiltro']('1');

    const req = httpMock.expectOne((r) => r.url.includes('/admin/reviews') && r.params.get('product_id') === '1');
    req.flush({ data: [] });

    expect(component['filtroProductoId']()).toBe(1);
  });

  it('elimina una resena tras confirmar', async () => {
    const review = { id: 1, author_name: 'Marcela G.', rating: 5, comment: 'Hermosa', created_at: '2026-10-01T00:00:00Z', product: { id: 1, name: 'Cadena cubana' } };
    flushInitial([review]);

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar'](review);
    confirmDialog.resolve(true);
    await promesa;

    httpMock.expectOne((r) => r.url.endsWith('/admin/reviews/1') && r.method === 'DELETE').flush(null);
    httpMock.expectOne((r) => r.url.endsWith('/admin/reviews') && r.method === 'GET').flush({ data: [] });

    expect(component['reviews']()).toHaveLength(0);
  });

  it('no elimina la resena si se cancela la confirmacion', async () => {
    const review = { id: 1, author_name: 'Marcela G.', rating: 5, comment: 'Hermosa', created_at: '2026-10-01T00:00:00Z', product: { id: 1, name: 'Cadena cubana' } };
    flushInitial([review]);

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar'](review);
    confirmDialog.resolve(false);
    await promesa;

    httpMock.expectNone((r) => r.method === 'DELETE');
  });
});
