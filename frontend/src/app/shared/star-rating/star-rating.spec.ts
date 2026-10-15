import { ComponentFixture, TestBed } from '@angular/core/testing';
import { StarRating } from './star-rating';

describe('StarRating', () => {
  let fixture: ComponentFixture<StarRating>;

  async function crear(rating: number): Promise<ComponentFixture<StarRating>> {
    await TestBed.configureTestingModule({ imports: [StarRating] }).compileComponents();
    const f = TestBed.createComponent(StarRating);
    f.componentRef.setInput('rating', rating);
    f.detectChanges();
    return f;
  }

  it('should create', async () => {
    fixture = await crear(4.5);
    expect(fixture.componentInstance).toBeTruthy();
  });

  it('redondea la calificacion a la estrella mas cercana', async () => {
    fixture = await crear(4.5);
    expect(fixture.componentInstance['estrellas']()).toEqual([true, true, true, true, true]);
  });

  it('marca solo las estrellas correspondientes para una calificacion baja', async () => {
    fixture = await crear(2);
    expect(fixture.componentInstance['estrellas']()).toEqual([true, true, false, false, false]);
  });
});
