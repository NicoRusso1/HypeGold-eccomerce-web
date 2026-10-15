import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Header } from './shared/header/header';
import { Footer } from './shared/footer/footer';
import { ConfirmDialog } from './shared/confirm-dialog/confirm-dialog';

@Component({
  imports: [RouterOutlet, Header, Footer, ConfirmDialog],
  selector: 'app-root',
  styleUrl: './app.css',
  templateUrl: './app.html',
})
export class App {}
