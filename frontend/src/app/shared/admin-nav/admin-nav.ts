import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  imports: [RouterLink, RouterLinkActive],
  selector: 'app-admin-nav',
  styleUrl: './admin-nav.css',
  templateUrl: './admin-nav.html',
})
export class AdminNav {}
