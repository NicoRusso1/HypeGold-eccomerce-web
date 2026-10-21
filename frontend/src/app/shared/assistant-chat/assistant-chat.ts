import { CurrencyPipe } from '@angular/common';
import { Component, ElementRef, ViewChild, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink, Router } from '@angular/router';
import { AssistantService } from '../../core/services/assistant.service';
import { AuthService } from '../../core/services/auth.service';
import { CartService } from '../../core/services/cart.service';
import { AssistantProduct } from '../../core/models/assistant.model';

interface ChatItem {
  role: 'user' | 'assistant';
  content: string;
  products?: AssistantProduct[];
}

const SUGERENCIAS_INICIALES = ['Regalo para mi novia', 'Cadenas de oro laminado', 'Algo hasta $30.000'];

@Component({
  imports: [FormsModule, RouterLink, CurrencyPipe],
  selector: 'app-assistant-chat',
  styleUrl: './assistant-chat.css',
  templateUrl: './assistant-chat.html',
})
export class AssistantChat {
  private readonly assistantService = inject(AssistantService);
  private readonly cartService = inject(CartService);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  @ViewChild('scrollContainer') private scrollContainer?: ElementRef<HTMLDivElement>;

  protected readonly sugerencias = SUGERENCIAS_INICIALES;

  protected readonly abierto = signal(false);
  protected readonly mensajes = signal<ChatItem[]>([]);
  protected readonly cargando = signal(false);
  protected readonly error = signal(false);
  protected readonly inputValue = signal('');
  protected readonly agregadoId = signal<number | null>(null);

  private ultimoMensaje = '';

  protected toggle(): void {
    this.abierto.update((valor) => !valor);

    if (this.abierto()) {
      this.scrollAbajo();
    }
  }

  protected enviarDesdeChip(texto: string): void {
    this.enviar(texto);
  }

  protected onSubmit(): void {
    this.enviar(this.inputValue());
  }

  protected reintentar(): void {
    if (!this.ultimoMensaje || this.cargando()) {
      return;
    }

    this.error.set(false);
    this.cargando.set(true);

    this.assistantService.enviarMensaje(this.ultimoMensaje).subscribe({
      next: (respuesta) => this.onRespuestaOk(respuesta),
      error: () => this.onRespuestaError(),
    });
  }

  protected agregarAlCarrito(producto: AssistantProduct): void {
    if (!producto.variant_id) {
      return;
    }

    if (!this.authService.isLoggedIn()) {
      this.router.navigateByUrl('/cuenta/ingresar');
      return;
    }

    this.cartService.addItem(producto.variant_id, 1).subscribe({
      next: () => {
        this.agregadoId.set(producto.id);
        setTimeout(() => this.agregadoId.set(null), 2000);
      },
    });
  }

  private enviar(texto: string): void {
    const mensaje = texto.trim();

    if (!mensaje || this.cargando()) {
      return;
    }

    this.ultimoMensaje = mensaje;
    this.inputValue.set('');
    this.error.set(false);
    this.cargando.set(true);
    this.mensajes.update((items) => [...items, { role: 'user', content: mensaje }]);
    this.scrollAbajo();

    this.assistantService.enviarMensaje(mensaje).subscribe({
      next: (respuesta) => this.onRespuestaOk(respuesta),
      error: () => this.onRespuestaError(),
    });
  }

  private onRespuestaOk(respuesta: { reply: string; products: AssistantProduct[] }): void {
    this.cargando.set(false);
    this.mensajes.update((items) => [
      ...items,
      { role: 'assistant', content: respuesta.reply, products: respuesta.products },
    ]);
    this.scrollAbajo();
  }

  private onRespuestaError(): void {
    this.cargando.set(false);
    this.error.set(true);
    this.scrollAbajo();
  }

  private scrollAbajo(): void {
    setTimeout(() => {
      const el = this.scrollContainer?.nativeElement;
      if (el) {
        el.scrollTop = el.scrollHeight;
      }
    });
  }
}
