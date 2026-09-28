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
  // Datos de ejemplo: se reemplazan por la API en HG-18/HG-19/HG-21.
  protected readonly categorias: Categoria[] = [
    { slug: 'anillos', nombre: 'Anillos', icono: '💍' },
    { slug: 'cadenas', nombre: 'Cadenas', icono: '⛓️' },
    { slug: 'aros', nombre: 'Aros', icono: '💎' },
    { slug: 'pulseras', nombre: 'Pulseras', icono: '✨' },
  ];

  protected readonly destacados: ProductoDestacado[] = [
    { slug: 'anillo-clasico-oro', nombre: 'Anillo clásico', material: 'Oro 18k', precio: 85000, emoji: '💍' },
    { slug: 'cadena-cubana', nombre: 'Cadena cubana', material: 'Oro laminado', precio: 45000, emoji: '⛓️' },
    { slug: 'aros-gota', nombre: 'Aros gota', material: 'Plata 925', precio: 22000, emoji: '💎' },
  ];
}
