import { CurrencyPipe } from '@angular/common';
import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

interface Categoria {
  slug: string;
  nombre: string;
  icono: string;
}

interface ProductoDestacado {
  slug: string;
  nombre: string;
  material: string;
  precio: number;
  emoji: string;
}

@Component({
  imports: [RouterLink, CurrencyPipe],
  selector: 'app-home',
  styleUrl: './home.css',
  templateUrl: './home.html',
})
export class Home {
  // Datos de ejemplo: se reemplazan por la API en HG-21.
  protected readonly categorias: Categoria[] = [
    { slug: 'cadenas', nombre: 'Cadenas', icono: '⛓️' },
    { slug: 'pulseras', nombre: 'Pulseras', icono: '✨' },
  ];

  protected readonly destacados: ProductoDestacado[] = [
    { slug: 'cadena-cubana', nombre: 'Cadena cubana', material: 'Oro laminado', precio: 45000, emoji: '⛓️' },
    { slug: 'pulsera-forcet', nombre: 'Pulsera forcet', material: 'Oro 18k', precio: 24000, emoji: '✨' },
    { slug: 'cadena-con-dije-corona', nombre: 'Cadena con dije corona', material: 'Oro laminado', precio: 35000, emoji: '👑' },
  ];
}
