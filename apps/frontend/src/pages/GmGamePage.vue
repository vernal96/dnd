<script setup lang="ts">
import { ChevronLeft, Map, Pencil, Plus, ScrollText, Shield, Trash2, Users } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import CabinetShell from '@/components/cabinet/CabinetShell.vue';
import GmAddMemberForm from '@/components/gm/GmAddMemberForm.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { usePlayerInvitations } from '@/composables/usePlayerInvitations';
import { connectRealtime, subscribeRealtime } from '@/composables/useRealtimeSocket';
import { useToastCenter } from '@/composables/useToastCenter';
import { createGameScene, deleteGameScene } from '@/services/sceneApi';
import { fetchGame, inviteGameMember, removeGameMember, updateGameStatus } from '@/services/gameApi';
import type { GameDetail, GameStatus } from '@/types/game';
import type { RealtimeEventMessage } from '@/types/realtime';
import { formatGameStatus } from '@/utils/gameStatus';

const route = useRoute();
const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending, logoutUser } = useAuthSession();
const { loadInvitations, pendingInvitationsCount } = usePlayerInvitations();
const { pushToast } = useToastCenter();

const game = ref<GameDetail | null>(null);
const gameError = ref('');
const isGameLoading = ref(false);
const isStatusUpdating = ref(false);
const isMemberUpdating = ref(false);
const isSceneUpdating = ref(false);

const gameId = computed<number | null>(() => {
  const rawValue = route.params.id;
  const parsedValue = Number.parseInt(Array.isArray(rawValue) ? rawValue[0] : String(rawValue), 10);

  return Number.isNaN(parsedValue) ? null : parsedValue;
});

/**
 * Завершает сессию и возвращает пользователя на экран входа.
 */
async function handleLogout(): Promise<void> {
  await logoutUser();
  await router.push('/');
}

/**
 * Загружает одну игру текущего мастера.
 */
async function loadGame(): Promise<void> {
  if (gameId.value === null) {
    gameError.value = 'Игра не найдена.';

    return;
  }

  isGameLoading.value = true;
  gameError.value = '';

  try {
    game.value = await fetchGame(gameId.value);
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isGameLoading.value = false;
  }
}

const statusOptions: Array<{ label: string; value: GameStatus }> = [
  { label: 'Черновик', value: 'draft' },
  { label: 'Активна', value: 'active' },
  { label: 'Пауза', value: 'paused' },
  { label: 'Завершена', value: 'completed' },
];

/**
 * Обновляет статус текущей игры.
 */
async function handleStatusUpdate(status: GameStatus): Promise<void> {
  if (game.value === null || game.value.status === status) {
    return;
  }

  isStatusUpdating.value = true;
  gameError.value = '';

  try {
    game.value = await updateGameStatus(game.value.id, status);
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isStatusUpdating.value = false;
  }
}

/**
 * Отправляет приглашение новому участнику в текущую игру.
 */
async function handleAddMember(login: string): Promise<void> {
  if (game.value === null) {
    return;
  }

  isMemberUpdating.value = true;
  gameError.value = '';

  try {
    game.value = await inviteGameMember(game.value.id, { login });
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isMemberUpdating.value = false;
  }
}

/**
 * Удаляет участника из текущей игры.
 */
async function handleRemoveMember(memberId: number): Promise<void> {
  if (game.value === null) {
    return;
  }

  isMemberUpdating.value = true;
  gameError.value = '';

  try {
    game.value = await removeGameMember(game.value.id, memberId);
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isMemberUpdating.value = false;
  }
}

/**
 * Создает новую authored-сцену с размерами по умолчанию.
 */
async function handleCreateScene(): Promise<void> {
  if (game.value === null) {
    return;
  }

  isSceneUpdating.value = true;
  gameError.value = '';

  try {
    await createGameScene(game.value.id, {
      name: `Сцена ${game.value.scene_states.length + 1}`,
      width: 6,
      height: 6,
    });
    await loadGame();
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isSceneUpdating.value = false;
  }
}

/**
 * Удаляет authored-сцену из текущей игры.
 */
async function handleDeleteScene(sceneId: number): Promise<void> {
  if (game.value === null) {
    return;
  }

  isSceneUpdating.value = true;
  gameError.value = '';

  try {
    await deleteGameScene(game.value.id, sceneId);
    await loadGame();
  } catch (error) {
    gameError.value = (error as Error).message;
  } finally {
    isSceneUpdating.value = false;
  }
}

/**
 * Возвращает русское название роли участника.
 */
function formatMemberRole(role: string): string {
  if (role === 'gm') {
    return 'Мастер';
  }

  if (role === 'player') {
    return 'Игрок';
  }

  return role;
}

/**
 * Обрабатывает realtime-обновления, относящиеся к текущей игре.
 */
async function handleRealtimeEvent(message: RealtimeEventMessage): Promise<void> {
  if (gameId.value === null || message.payload.gameId !== gameId.value) {
    return;
  }

  if (
    message.event === 'game-invitation.created'
    || message.event === 'game-invitation.accepted'
    || message.event === 'game-invitation.declined'
  ) {
    await loadGame();
    await loadInvitations(true);

    if (message.event === 'game-invitation.created' && message.payload.invitedUserId === currentUser.value?.id) {
      pushToast('Новое приглашение', 'Тебе пришло новое приглашение как игроку в другой игровой стол.', 'info');
    }

    if (message.event === 'game-invitation.accepted') {
      pushToast('Игрок присоединился', 'Приглашённый игрок принял приглашение и добавлен в стол.', 'success');
    }

    if (message.event === 'game-invitation.declined') {
      pushToast('Приглашение отклонено', 'Игрок отклонил приглашение в этот стол.', 'info');
    }
  }
}

onMounted(async () => {
  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');
    return;
  }

  if (!currentUser.value?.canAccessGm) {
    await router.replace('/cabinet/player');
    return;
  }

  await loadGame();
  await loadInvitations();
  connectRealtime();
});

const unsubscribeRealtime = subscribeRealtime((message) => {
  void handleRealtimeEvent(message);
});

onUnmounted(() => {
  unsubscribeRealtime();
});
</script>

<template>
  <CabinetShell
    v-if="currentUser"
    current-section="gm"
    :pending="isPending"
    :player-invitation-badge="pendingInvitationsCount"
    :user="currentUser"
    @logout="handleLogout"
  >
    <div class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2">
          <RouterLink
            class="inline-flex items-center gap-2 text-sm text-amber-200/80 transition hover:text-amber-100"
            to="/cabinet/gm"
          >
            <ChevronLeft class="h-4 w-4" />
            Назад к списку игр
          </RouterLink>

          <p class="text-xs uppercase text-amber-200/50">
            Карточка игры
          </p>
        </div>

        <span
          v-if="game"
          class="rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-xs uppercase text-amber-100"
        >
          {{ formatGameStatus(game.status) }}
        </span>
      </div>

      <div
        v-if="gameError"
        class="rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
      >
        {{ gameError }}
      </div>

      <div
        v-else-if="isGameLoading"
        class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 px-5 py-8 text-sm text-slate-300"
      >
        Загружаем данные игры...
      </div>

      <template v-else-if="game">
        <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <p class="text-xs uppercase text-amber-200/50">
            Игра #{{ game.id }}
          </p>
          <h1 class="mt-3 font-display text-4xl text-amber-50">
            {{ game.title }}
          </h1>
          <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
            {{ game.description || 'Описание для этой игры пока не заполнено.' }}
          </p>

          <div class="mt-5 flex flex-wrap gap-2">
            <button
              v-for="option in statusOptions"
              :key="option.value"
              :class="
                game.status === option.value
                  ? 'border-amber-300/25 bg-amber-300/10 text-amber-50'
                  : 'border-amber-100/10 bg-white/5 text-slate-300'
              "
              class="rounded-full border px-4 py-2 text-sm transition hover:border-amber-200/20 hover:text-amber-50"
              :disabled="isStatusUpdating"
              type="button"
              @click="handleStatusUpdate(option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </section>

        <div class="grid gap-4 md:grid-cols-3">
          <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5">
            <Shield class="h-5 w-5 text-amber-200" />
            <p class="mt-4 text-sm uppercase text-amber-200/50">
              Мастер
            </p>
            <p class="mt-2 font-display text-2xl text-amber-50">
              {{ game.gm.name }}
            </p>
            <p class="mt-2 text-sm text-slate-300">
              {{ game.gm.email }}
            </p>
          </div>

          <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5">
            <Users class="h-5 w-5 text-amber-200" />
            <p class="mt-4 text-sm uppercase text-amber-200/50">
              Участники
            </p>
            <p class="mt-2 font-display text-2xl text-amber-50">
              {{ game.members_count }}
            </p>
            <p class="mt-2 text-sm text-slate-300">
              Только игроки, которые уже приняли приглашение.
            </p>
          </div>

          <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5">
            <ScrollText class="h-5 w-5 text-amber-200" />
            <p class="mt-4 text-sm uppercase text-amber-200/50">
              Состояние сцены
            </p>
            <p class="mt-2 font-display text-2xl text-amber-50">
              {{ game.active_scene_state_id === null ? 'Не выбрано' : `#${game.active_scene_state_id}` }}
            </p>
            <p class="mt-2 text-sm text-slate-300">
              Активная сцена пока {{ game.active_scene_state_id === null ? 'не назначена.' : 'подключена к игре.' }}
            </p>
          </div>
        </div>

        <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <p class="text-xs uppercase text-amber-200/50">
            Состав игры
          </p>

          <GmAddMemberForm
            class="mt-4"
            :pending="isMemberUpdating"
            @submit="handleAddMember"
          />

          <div class="mt-4 grid gap-3 lg:grid-cols-2">
            <div
              v-for="member in game.members"
              :key="member.id"
              class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4"
            >
              <div class="flex items-center justify-between gap-3">
                <p class="font-medium text-amber-50">
                  {{ member.user.name }}
                </p>
                <div class="flex items-center gap-2">
                  <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1 text-xs uppercase text-amber-100/80">
                    {{ formatMemberRole(member.role) }}
                  </span>
                  <button
                    v-if="member.role !== 'gm'"
                    class="rounded-full border border-rose-300/15 bg-rose-500/10 p-2 text-rose-200 transition hover:bg-rose-500/20"
                    :disabled="isMemberUpdating"
                    type="button"
                    @click="handleRemoveMember(member.id)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
              <p class="mt-2 text-sm text-slate-300">
                {{ member.user.email }}
              </p>
              <p class="mt-2 text-xs uppercase text-slate-400">
                Статус: {{ member.status === 'active' ? 'Активен' : member.status }}
              </p>
            </div>
          </div>
        </section>

        <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-xs uppercase text-amber-200/50">
                Сцены
              </p>
              <p class="mt-2 text-sm text-slate-300">
                Авторские сцены этой игры. Отсюда можно создать, удалить и открыть редактор.
              </p>
            </div>

            <button
              class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15"
              :disabled="isSceneUpdating"
              type="button"
              @click="handleCreateScene"
            >
              <Plus class="h-4 w-4" />
              Создать сцену
            </button>
          </div>

          <div
            v-if="game.scene_states.length === 0"
            class="mt-4 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
          >
            У этой игры пока нет сцен.
          </div>

          <div
            v-else
            class="mt-4 grid gap-3 lg:grid-cols-2"
          >
            <div
              v-for="scene in game.scene_states"
              :key="scene.id"
              class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="font-medium text-amber-50">
                    {{ scene.scene_template.name }}
                  </p>
                  <p class="mt-2 text-sm text-slate-300">
                    {{ scene.scene_template.description || 'Описание сцены пока не заполнено.' }}
                  </p>
                </div>

                <span
                  v-if="game.active_scene_state_id === scene.id"
                  class="shrink-0 rounded-full border border-emerald-300/20 bg-emerald-500/10 px-3 py-1 text-xs uppercase text-emerald-100"
                >
                  Активна
                </span>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-2 text-xs uppercase text-slate-400">
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1">
                  <Map class="mr-1 inline h-3.5 w-3.5" />
                  {{ scene.scene_template.width }}x{{ scene.scene_template.height }}
                </span>
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1">
                  Версия {{ scene.version }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <RouterLink
                  class="inline-flex items-center gap-2 rounded-full border border-sky-300/20 bg-sky-500/10 px-4 py-2 text-sm text-sky-100 transition hover:bg-sky-500/20"
                  :to="`/cabinet/gm/games/${game.id}/scenes/${scene.id}`"
                >
                  <Pencil class="h-4 w-4" />
                  Редактировать
                </RouterLink>

                <button
                  class="inline-flex items-center gap-2 rounded-full border border-rose-300/15 bg-rose-500/10 px-4 py-2 text-sm text-rose-200 transition hover:bg-rose-500/20"
                  :disabled="isSceneUpdating"
                  type="button"
                  @click="handleDeleteScene(scene.id)"
                >
                  <Trash2 class="h-4 w-4" />
                  Удалить
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <p class="text-xs uppercase text-amber-200/50">
            Ожидают ответа
          </p>
          <div
            v-if="game.invitations.length === 0"
            class="mt-4 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
          >
            Пока нет активных приглашений.
          </div>

          <div
            v-else
            class="mt-4 grid gap-3 lg:grid-cols-2"
          >
            <div
              v-for="invitation in game.invitations"
              :key="invitation.id"
              class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4"
            >
              <div class="flex items-center justify-between gap-3">
                <p class="font-medium text-amber-50">
                  {{ invitation.invited_user.name }}
                </p>
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1 text-xs uppercase text-amber-100/80">
                  Ожидает
                </span>
              </div>
              <p class="mt-2 text-sm text-slate-300">
                {{ invitation.invited_user.email }}
              </p>
              <p class="mt-2 text-xs text-slate-400">
                Приглашение появится в личном кабинете игрока.
              </p>
            </div>
          </div>
        </section>
      </template>
    </div>
  </CabinetShell>
</template>
