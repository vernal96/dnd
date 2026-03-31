<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import CabinetShell from '@/components/cabinet/CabinetShell.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { usePlayerInvitations } from '@/composables/usePlayerInvitations';
import { connectRealtime, subscribeRealtime } from '@/composables/useRealtimeSocket';
import { useToastCenter } from '@/composables/useToastCenter';
import { useGameMasterGames } from '@/composables/useGameMasterGames';
import GmCreateGameForm from '@/components/gm/GmCreateGameForm.vue';
import GmGamesList from '@/components/gm/GmGamesList.vue';
import type { CreateGamePayload } from '@/types/game';
import type { RealtimeEventMessage } from '@/types/realtime';

const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending, logoutUser } = useAuthSession();
const { loadInvitations, pendingInvitationsCount } = usePlayerInvitations();
const { pushToast } = useToastCenter();
const {
  activeStatusFilter,
  applyStatusFilter,
  createNewGame,
  ensureGamesLoaded,
  errorMessage,
  games,
  isPending: gamesPending,
  loadGames,
} = useGameMasterGames();

/**
 * Завершает сессию и возвращает пользователя на экран входа.
 */
async function handleLogout(): Promise<void> {
  await logoutUser();
  await router.push('/');
}

/**
 * Создает новую игру из формы кабинета мастера.
 */
async function handleCreateGame(payload: CreateGamePayload): Promise<void> {
  await createNewGame(payload);
}

/**
 * Обрабатывает realtime-обновления приглашений в кабинете мастера.
 */
async function handleRealtimeEvent(message: RealtimeEventMessage): Promise<void> {
  if (
    message.event !== 'game-invitation.created'
    && message.event !== 'game-invitation.accepted'
    && message.event !== 'game-invitation.declined'
  ) {
    return;
  }

  await loadGames();
  await loadInvitations(true);

  if (message.event === 'game-invitation.created' && message.payload.invitedUserId === currentUser.value?.id) {
    pushToast('Новое приглашение', 'Пока ты в кабинете ГМа, тебе пришло новое приглашение как игроку.', 'info');
  }

  if (message.event === 'game-invitation.accepted') {
    pushToast('Игрок присоединился', 'Один из приглашённых игроков принял приглашение в игровой стол.', 'success');
  }

  if (message.event === 'game-invitation.declined') {
    pushToast('Приглашение отклонено', 'Игрок отклонил приглашение в игровой стол.', 'info');
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

  await ensureGamesLoaded();
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
      <div class="space-y-3">
        <p class="text-xs uppercase text-amber-200/50">
          Кабинет мастера
        </p>
        <h1 class="font-display text-4xl text-amber-50">
          Создание игр и управление кампаниями
        </h1>
        <p class="max-w-2xl text-sm leading-7 text-slate-300">
          Здесь мастер создает игровые столы и видит все свои кампании в одном месте.
        </p>
      </div>

      <div
        v-if="errorMessage"
        class="rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
      >
        {{ errorMessage }}
      </div>

      <GmGamesList
        :active-status-filter="activeStatusFilter"
        :games="games"
        :loading="gamesPending"
        @update:status-filter="applyStatusFilter"
      />

      <GmCreateGameForm
        :pending="gamesPending"
        @submit="handleCreateGame"
      />
    </div>
  </CabinetShell>
</template>
