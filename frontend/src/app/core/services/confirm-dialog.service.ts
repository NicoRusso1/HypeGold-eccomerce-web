import { Injectable, signal } from '@angular/core';

export interface ConfirmOptions {
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
}

interface ConfirmState extends ConfirmOptions {
  confirmText: string;
  cancelText: string;
}

@Injectable({ providedIn: 'root' })
export class ConfirmDialogService {
  readonly state = signal<ConfirmState | null>(null);
  private resolver: ((value: boolean) => void) | null = null;

  confirm(options: ConfirmOptions): Promise<boolean> {
    this.state.set({
      title: options.title,
      message: options.message,
      confirmText: options.confirmText ?? 'Eliminar',
      cancelText: options.cancelText ?? 'Cancelar',
    });

    return new Promise<boolean>((resolve) => {
      this.resolver = resolve;
    });
  }

  resolve(value: boolean): void {
    this.state.set(null);
    this.resolver?.(value);
    this.resolver = null;
  }
}
