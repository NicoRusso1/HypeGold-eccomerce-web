export interface LegalSection {
  heading: string;
  paragraphs: string[];
  list?: string[];
}

export interface LegalPageContent {
  title: string;
  updatedAt: string;
  intro?: string;
  sections: LegalSection[];
}
