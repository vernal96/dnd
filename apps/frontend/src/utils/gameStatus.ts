import type { GameStatus } from '@/types/game';

/**
 * Возвращает русское название статуса игры.
 */
export function formatGameStatus(status: string): string {
  const labels: Record<GameStatus, string> = {
    draft: 'Черновик',
    active: 'Активна',
    paused: 'На паузе',
    completed: 'Завершена',
  };

  return labels[status as GameStatus] ?? status;
}
