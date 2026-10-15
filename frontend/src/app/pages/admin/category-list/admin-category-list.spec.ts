import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { ConfirmDialogService } from '../../../core/services/confirm-dialog.service';
import { AdminCategoryListPage } from './admin-category-list';

describe('AdminCategoryListPage', () => {
  let component: AdminCategoryListPage;
  let fixture: ComponentFixture<AdminCategoryListPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminCategoryListPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminCategoryListPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  function flushCategories(categories: unknown[] = []): void {
    httpMock.expectOne((r) => r.url.endsWith('/admin/categories') && r.method === 'GET').flush({ data: categories });
  }

  it('should create and load categories', () => {
    expect(component).toBeTruthy();

    flushCategories([
      { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 2 },
    ]);

    expect(component['categories']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('crea una categoria y recarga el listado', () => {
    flushCategories([]);

    component['form'].setValue({ name: 'Pulseras', description: '' });
    component['guardar']();

    const createReq = httpMock.expectOne((r) => r.url.endsWith('/admin/categories') && r.method === 'POST');
    createReq.flush({ data: { id: 5, name: 'Pulseras', slug: 'pulseras', description: null, products_count: 0 } });

    flushCategories([{ id: 5, name: 'Pulseras', slug: 'pulseras', description: null, products_count: 0 }]);

    expect(component['saving']()).toBe(false);
    expect(component['categories']()).toHaveLength(1);
  });

  it('carga los datos de la categoria al editar', () => {
    flushCategories([{ id: 1, name: 'Cadenas', slug: 'cadenas', description: 'Desc', products_count: 0 }]);

    component['editar']({ id: 1, name: 'Cadenas', slug: 'cadenas', description: 'Desc', products_count: 0 });

    expect(component['editingId']()).toBe(1);
    expect(component['form'].value.name).toBe('Cadenas');
  });

  it('muestra el error cuando no se puede eliminar una categoria con productos', async () => {
    flushCategories([{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 3 }]);

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar']({ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 3 });
    confirmDialog.resolve(true);
    await promesa;

    const deleteReq = httpMock.expectOne((r) => r.url.endsWith('/admin/categories/1') && r.method === 'DELETE');
    deleteReq.flush(
      { errors: { category: ['No se puede eliminar una categoría que todavía tiene productos.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['deleteError']()).toContain('No se puede eliminar');
  });

  it('no elimina la categoria si se cancela la confirmacion', async () => {
    flushCategories([{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 0 }]);

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar']({ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 0 });
    confirmDialog.resolve(false);
    await promesa;

    httpMock.expectNone((r) => r.method === 'DELETE');
  });
});
