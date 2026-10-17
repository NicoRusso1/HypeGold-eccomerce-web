import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute } from '@angular/router';
import { LegalPage } from './legal-page';
import { LegalPageContent } from './legal-content.model';

describe('LegalPage', () => {
  let component: LegalPage;
  let fixture: ComponentFixture<LegalPage>;

  const content: LegalPageContent = {
    title: 'Página de prueba',
    updatedAt: 'octubre de 2026',
    intro: 'Intro de prueba.',
    sections: [
      { heading: 'Sección uno', paragraphs: ['Párrafo uno.'] },
      { heading: 'Sección dos', paragraphs: ['Párrafo dos.'], list: ['Item A', 'Item B'] },
    ],
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LegalPage],
      providers: [{ provide: ActivatedRoute, useValue: { snapshot: { data: { content } } } }],
    }).compileComponents();

    fixture = TestBed.createComponent(LegalPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create and show the page content', () => {
    expect(component).toBeTruthy();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Página de prueba');
    expect(text).toContain('Sección uno');
    expect(text).toContain('Item A');
  });
});
