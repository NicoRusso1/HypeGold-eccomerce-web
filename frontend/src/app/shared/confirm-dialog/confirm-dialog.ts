import { Component, HostListener, inject } from '@angular/core';
import { ConfirmDialogService } from '../../core/services/confirm-dialog.service';

@Component({
  selector: 'app-confirm-dialog',
  styleUrl: './confirm-dialog.css',
  templateUrl: './confirm-dialog.html',
})
export class ConfirmDialog {
  protected readonly dialogService = inject(ConfirmDialogService);

  @HostListener('document:keydown.escape')
  protected onEscape(): void {
    if (this.dialogService.state()) {
      this.cancelar();
    }
  }

  protected confirmar(): void {
    this.dialogService.resolve(true);
  }

  protected cancelar(): void {
    this.dialogService.resolve(false);
  }
}
