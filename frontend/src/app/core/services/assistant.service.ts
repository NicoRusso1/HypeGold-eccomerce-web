import { HttpClient } from '@angular/common/http';
import { Injectable, inject, signal } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AssistantChatResponse, AssistantTurn } from '../models/assistant.model';

const SESSION_KEY = 'hypegold_assistant_session';
const MAX_HISTORIAL = 10;

@Injectable({ providedIn: 'root' })
export class AssistantService {
  private readonly http = inject(HttpClient);

  private readonly sessionId = this.getOrCreateSessionId();

  readonly history = signal<AssistantTurn[]>([]);

  enviarMensaje(message: string): Observable<AssistantChatResponse> {
    const historyPrevio = this.history().slice(-MAX_HISTORIAL);

    return this.http
      .post<AssistantChatResponse>(`${environment.apiUrl}/assistant/chat`, {
        session_id: this.sessionId,
        message,
        history: historyPrevio,
      })
      .pipe(
        tap((respuesta) => {
          this.history.update((turnos) => [
            ...turnos,
            { role: 'user', content: message },
            { role: 'assistant', content: respuesta.reply },
          ]);
        }),
      );
  }

  private getOrCreateSessionId(): string {
    try {
      const existente = localStorage.getItem(SESSION_KEY);

      if (existente) {
        return existente;
      }

      const nuevo = crypto.randomUUID();
      localStorage.setItem(SESSION_KEY, nuevo);

      return nuevo;
    } catch {
      return crypto.randomUUID();
    }
  }
}
