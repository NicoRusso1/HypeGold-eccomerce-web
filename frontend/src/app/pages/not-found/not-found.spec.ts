import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { NotFound } from './not-found';

describe('NotFound', () => {
  let component: NotFound;
  let fixture: ComponentFixture<NotFound>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NotFound],
      providers: [provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(NotFound);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create and show a link back to home', () => {
    expect(component).toBeTruthy();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('404');
    expect(text).toContain('Ir al inicio');
  });
});
