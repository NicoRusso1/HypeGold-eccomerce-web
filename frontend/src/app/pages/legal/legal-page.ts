import { Component, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { LegalPageContent } from './legal-content.model';

@Component({
  selector: 'app-legal-page',
  styleUrl: './legal-page.css',
  templateUrl: './legal-page.html',
})
export class LegalPage {
  private readonly route = inject(ActivatedRoute);

  protected readonly content = this.route.snapshot.data['content'] as LegalPageContent;
}
