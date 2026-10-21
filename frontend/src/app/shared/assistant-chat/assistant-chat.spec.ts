import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AssistantChat } from './assistant-chat';

describe('AssistantChat', () => {
  let component: AssistantChat;
  let fixture: ComponentFixture<AssistantChat>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AssistantChat],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AssistantChat);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('empieza cerrado y se abre al tocar el botón', () => {
    expect(component['abierto']()).toBe(false);

    component['toggle']();

    expect(component['abierto']()).toBe(true);
  });

  it('muestra las sugerencias iniciales cuando no hay mensajes', () => {
    component['toggle']();
    fixture.detectChanges();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Regalo para mi novia');
  });

  it('envía el mensaje y muestra la respuesta con productos', () => {
    component['toggle']();
    component['inputValue'].set('Busco una cadena');
    component['onSubmit']();

    expect(component['mensajes']()).toHaveLength(1);
    expect(component['cargando']()).toBe(true);

    const req = httpMock.expectOne((r) => r.url.endsWith('/assistant/chat'));
    expect(req.request.body.message).toBe('Busco una cadena');

    req.flush({
      reply: '¡Te recomiendo esta!',
      products: [{ id: 1, name: 'Cadena cubana', slug: 'cadena-cubana', base_price: 45000, image: null, variant_id: 5 }],
    });

    expect(component['cargando']()).toBe(false);
    expect(component['mensajes']()).toHaveLength(2);
    expect(component['mensajes']()[1].products?.[0].name).toBe('Cadena cubana');
  });

  it('muestra un error y permite reintentar si falla la respuesta', () => {
    component['toggle']();
    component['inputValue'].set('Hola');
    component['onSubmit']();

    httpMock.expectOne((r) => r.url.endsWith('/assistant/chat')).flush('error', { status: 500, statusText: 'Error' });

    expect(component['error']()).toBe(true);

    component['reintentar']();

    const req = httpMock.expectOne((r) => r.url.endsWith('/assistant/chat'));
    req.flush({ reply: 'Ahora sí', products: [] });

    expect(component['error']()).toBe(false);
    expect(component['mensajes']()).toHaveLength(2);
  });
});
