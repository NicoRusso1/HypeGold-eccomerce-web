import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ConfirmDialogService } from '../../core/services/confirm-dialog.service';
import { ConfirmDialog } from './confirm-dialog';

describe('ConfirmDialog', () => {
  let fixture: ComponentFixture<ConfirmDialog>;
  let dialogService: ConfirmDialogService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({ imports: [ConfirmDialog] }).compileComponents();
    fixture = TestBed.createComponent(ConfirmDialog);
    dialogService = TestBed.inject(ConfirmDialogService);
    fixture.detectChanges();
  });

  it('no muestra nada cuando no hay ningun dialogo pendiente', () => {
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text.trim()).toBe('');
  });

  it('muestra el titulo y el mensaje cuando se pide confirmacion', () => {
    dialogService.confirm({ title: 'Eliminar categoría', message: '¿Seguro?' });
    fixture.detectChanges();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Eliminar categoría');
    expect(text).toContain('¿Seguro?');
  });

  it('resuelve true al confirmar', async () => {
    const promesa = dialogService.confirm({ title: 'Eliminar', message: '¿Seguro?' });
    fixture.detectChanges();

    fixture.componentInstance['confirmar']();

    expect(await promesa).toBe(true);
  });

  it('resuelve false al cancelar', async () => {
    const promesa = dialogService.confirm({ title: 'Eliminar', message: '¿Seguro?' });
    fixture.detectChanges();

    fixture.componentInstance['cancelar']();

    expect(await promesa).toBe(false);
  });
});
