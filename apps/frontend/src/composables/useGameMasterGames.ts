import {computed, ref} from 'vue';
import {createGame, fetchGames} from '@/services/gameApi';
import type {CreateGamePayload, GameStatusFilter, GameSummary} from '@/types/game';

const games = ref<GameSummary[]>([]);
const isPending = ref(false);
const hasLoadedGames = ref(false);
const errorMessage = ref('');
const activeStatusFilter = ref<GameStatusFilter>('all');

/**
 * Управляет списком игр и созданием новых кампаний в кабинете мастера.
 */
export function useGameMasterGames() {
    const totalGames = computed<number>(() => games.value.length);

    /**
     * Загружает список игр текущего мастера.
     */
    async function loadGames(): Promise<void> {
        isPending.value = true;
        errorMessage.value = '';

        try {
            const response = await fetchGames(activeStatusFilter.value);
            games.value = response.data;
            hasLoadedGames.value = true;
        } catch (error) {
            errorMessage.value = (error as Error).message;
        } finally {
            isPending.value = false;
        }
    }

    /**
     * Гарантирует, что список игр был загружен хотя бы один раз.
     */
    async function ensureGamesLoaded(): Promise<void> {
        if (hasLoadedGames.value) {
            return;
        }

        await loadGames();
    }

    /**
     * Создает новую игру и добавляет ее в начало списка.
     */
    async function createNewGame(payload: CreateGamePayload): Promise<void> {
        isPending.value = true;
        errorMessage.value = '';

        try {
            const game = await createGame(payload);
            if (activeStatusFilter.value === 'all' || activeStatusFilter.value === game.status) {
                games.value = [game, ...games.value];
            }
            hasLoadedGames.value = true;
        } catch (error) {
            errorMessage.value = (error as Error).message;
        } finally {
            isPending.value = false;
        }
    }

    /**
     * Обновляет фильтр статуса и перезагружает список игр.
     */
    async function applyStatusFilter(status: GameStatusFilter): Promise<void> {
        activeStatusFilter.value = status;
        await loadGames();
    }

    return {
        activeStatusFilter,
        applyStatusFilter,
        createNewGame,
        ensureGamesLoaded,
        errorMessage,
        games,
        isPending,
        loadGames,
        totalGames,
    };
}
