<script setup lang="ts">
import { Backpack, Check, ScrollText, Sword, Users, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import CabinetShell from '@/components/cabinet/CabinetShell.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { usePlayerInvitations } from '@/composables/usePlayerInvitations';
import { connectRealtime, subscribeRealtime } from '@/composables/useRealtimeSocket';
import type { RealtimeEventMessage } from '@/types/realtime';
import { useToastCenter } from '@/composables/useToastCenter';

const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending, logoutUser } = useAuthSession();
const { pushToast } = useToastCenter();
const {
  acceptInvitation,
  declineInvitation,
  invitationError,
  invitationPendingAction,
  invitationPendingToken,
  invitations,
  loadInvitations,
  pendingInvitationsCount,
} = usePlayerInvitations();
const invitationSuccess = ref('');
const activeInvitationTab = ref<'accepted' | 'declined' | 'pending'>('pending');

const filteredInvitations = computed<GameInvitationSummary[]>(() =>
  invitations.value.filter((invitation) => invitation.status === activeInvitationTab.value),
);

/**
 * Завершает сессию и возвращает пользователя на экран входа.
 */
async function handleLogout(): Promise<void> {
  await logoutUser();
  await router.push('/');
}

/**
 * Обрабатывает realtime-события приглашений для кабинета игрока.
 */
async function handleRealtimeEvent(message: RealtimeEventMessage): Promise<void> {
  if (
    message.event !== 'game-invitation.created'
    && message.event !== 'game-invitation.accepted'
    && message.event !== 'game-invitation.declined'
  ) {
    return;
  }

  if (message.event === 'game-invitation.created' && currentUser.value?.id === message.payload.invitedUserId) {
    pushToast('Новое приглашение', 'В личный кабинет поступило новое приглашение в игровой стол.', 'info');
  }

  await loadInvitations(true);
}

/**
 * Принимает приглашение в игровой стол.
 */
async function handleAcceptInvitation(token: string): Promise<void> {
  invitationSuccess.value = '';

  try {
    const invitation = await acceptInvitation(token);
    activeInvitationTab.value = 'accepted';
    invitationSuccess.value = `Ты присоединился к игре «${invitation.game.title}».`;
    pushToast('Приглашение принято', `Ты присоединился к игре «${invitation.game.title}».`, 'success');
  } catch (error) {
    invitationSuccess.value = '';
  } finally {
    //
  }
}

/**
 * Отклоняет приглашение в игровой стол.
 */
async function handleDeclineInvitation(token: string): Promise<void> {
  invitationSuccess.value = '';

  try {
    const invitation = await declineInvitation(token);
    activeInvitationTab.value = 'declined';
    invitationSuccess.value = `Приглашение в игру «${invitation.game.title}» отклонено.`;
    pushToast('Приглашение отклонено', `Ты отклонил приглашение в игру «${invitation.game.title}».`, 'info');
  } catch (error) {
    invitationSuccess.value = '';
  } finally {
    //
  }
}

onMounted(async () => {
  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');

    return;
  }

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
    current-section="player"
    :pending="isPending"
    :player-invitation-badge="pendingInvitationsCount"
    :user="currentUser"
    @logout="handleLogout"
  >
    <div class="space-y-6">
      <div
        v-if="invitationSuccess"
        class="rounded-[1.3rem] border border-emerald-300/20 bg-emerald-500/10 px-4 py-3 text-sm leading-6 text-emerald-100"
      >
        {{ invitationSuccess }}
      </div>

      <div
        v-if="invitationError"
        class="rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
      >
        {{ invitationError }}
      </div>

      <div class="space-y-3">
        <p class="text-xs uppercase text-amber-200/50">
          Кабинет игрока
        </p>
        <h1 class="font-display text-4xl text-amber-50">
          Кампании и герои под рукой
        </h1>
        <p class="max-w-2xl text-sm leading-7 text-slate-300">
          Здесь игрок видит свои приключения, сохраненные партии и быстрый доступ к игровым данным.
        </p>
      </div>

      <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
        <p class="text-xs uppercase text-amber-200/50">
          Приглашения
        </p>
        <h2 class="mt-3 font-display text-2xl text-amber-50">
          Игровые столы, ожидающие ответа
        </h2>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
          Здесь появляются приглашения от мастеров. Игрок попадает в стол только после явного подтверждения.
        </p>

        <div class="mt-5 inline-flex rounded-2xl border border-amber-200/10 bg-slate-950/30 p-1">
          <button
            :class="activeInvitationTab === 'pending' ? 'bg-amber-300/15 text-amber-50' : 'text-slate-300'"
            class="rounded-xl px-4 py-2 text-sm transition"
            type="button"
            @click="activeInvitationTab = 'pending'"
          >
            Новые
          </button>
          <button
            :class="activeInvitationTab === 'accepted' ? 'bg-amber-300/15 text-amber-50' : 'text-slate-300'"
            class="rounded-xl px-4 py-2 text-sm transition"
            type="button"
            @click="activeInvitationTab = 'accepted'"
          >
            Принятые
          </button>
          <button
            :class="activeInvitationTab === 'declined' ? 'bg-amber-300/15 text-amber-50' : 'text-slate-300'"
            class="rounded-xl px-4 py-2 text-sm transition"
            type="button"
            @click="activeInvitationTab = 'declined'"
          >
            Отклонённые
          </button>
        </div>

        <div
          v-if="filteredInvitations.length === 0"
          class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
        >
          {{
            activeInvitationTab === 'pending'
              ? 'Пока нет новых приглашений.'
              : activeInvitationTab === 'accepted'
                ? 'Пока нет принятых приглашений.'
                : 'Пока нет отклонённых приглашений.'
          }}
        </div>

        <div
          v-else
          class="mt-5 grid gap-4 lg:grid-cols-2"
        >
          <div
            v-for="invitation in filteredInvitations"
            :key="invitation.id"
            class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4"
          >
            <p class="text-sm text-amber-200/70">
              Мастер: {{ invitation.gm.name }}
            </p>
            <h3 class="mt-2 font-display text-2xl text-amber-50">
              {{ invitation.game.title }}
            </h3>
            <p class="mt-3 text-sm leading-7 text-slate-300">
              {{ invitation.game.description || 'Мастер пока не добавил описание к этому столу.' }}
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3">
              <button
                v-if="invitation.status === 'pending'"
                class="cta-primary"
                :disabled="invitationPendingToken === invitation.token"
                type="button"
                @click="handleAcceptInvitation(invitation.token)"
              >
                <Check class="h-4 w-4" />
                {{
                  invitationPendingToken === invitation.token && invitationPendingAction === 'accept'
                    ? 'Подтверждаем...'
                    : 'Принять приглашение'
                }}
              </button>

              <button
                v-if="invitation.status === 'pending'"
                class="cta-secondary"
                :disabled="invitationPendingToken === invitation.token"
                type="button"
                @click="handleDeclineInvitation(invitation.token)"
              >
                <X class="h-4 w-4" />
                {{
                  invitationPendingToken === invitation.token && invitationPendingAction === 'decline'
                    ? 'Отклоняем...'
                    : 'Отклонить'
                }}
              </button>

              <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2 text-xs uppercase text-amber-100/80">
                {{ invitation.gm.email }}
              </span>

              <span
                v-if="invitation.status !== 'pending'"
                class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2 text-xs uppercase text-amber-100/80"
              >
                {{ invitation.status === 'accepted' ? 'Принято' : 'Отклонено' }}
              </span>
            </div>
          </div>
        </div>
      </section>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Sword class="h-5 w-5 text-amber-200" />
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Активная партия
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            1
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Готова к продолжению с последнего подтвержденного сохранения.
          </p>
        </div>

        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Users class="h-5 w-5 text-amber-200" />
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Союзники
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            4
          </p>
          <p class="mt-2 text-sm text-slate-300">
            В партии собраны проверенные спутники и готовый состав для новой сцены.
          </p>
        </div>

        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Backpack class="h-5 w-5 text-amber-200" />
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Персонажи
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            3
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Персонажи и игровые профили доступны из одного аккаунта.
          </p>
        </div>

        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <ScrollText class="h-5 w-5 text-amber-200" />
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Последняя хроника
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            12
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Ходов и событий записано в журнале после последнего приключения.
          </p>
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <p class="text-xs uppercase text-amber-200/50">
            Следующий шаг
          </p>
          <h2 class="mt-3 font-display text-2xl text-amber-50">
            Продолжить кампанию «Тень Янтарной башни»
          </h2>
          <p class="mt-3 text-sm leading-7 text-slate-300">
            Сцена сохранена в состоянии исследования. Команда готова вернуться к карте, журналу боя и инвентарю без повторной настройки.
          </p>
          <button
            class="cta-primary mt-5"
            type="button"
          >
            Открыть кампанию
          </button>
        </div>

        <div class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <p class="text-xs uppercase text-amber-200/50">
            Быстрый обзор
          </p>
          <ul class="mt-4 space-y-3 text-sm text-slate-300">
            <li class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-3">
              Логин: {{ currentUser.name }}
            </li>
            <li class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-3">
              Последний вход: сегодня
            </li>
            <li class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-3">
              Активный статус: в партии
            </li>
          </ul>
        </div>
      </div>
    </div>
  </CabinetShell>
</template>
