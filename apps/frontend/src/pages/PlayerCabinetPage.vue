<script lang="ts" setup>
import {Backpack, Check, DoorOpen, ImageUp, Plus, Sparkles, X} from 'lucide-vue-next';
import {onMounted, onUnmounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import CabinetShell from '@/components/cabinet/CabinetShell.vue';
import PlayerCharacterCreateModal from '@/components/player/PlayerCharacterCreateModal.vue';
import PlayerInvitationAcceptModal from '@/components/player/PlayerInvitationAcceptModal.vue';
import {useAuthSession} from '@/composables/useAuthSession';
import {usePlayerInvitations} from '@/composables/usePlayerInvitations';
import {connectRealtime, subscribeRealtime} from '@/composables/useRealtimeSocket';
import {useToastCenter} from '@/composables/useToastCenter';
import {uploadPlayerCharacterImage} from '@/services/playerCharacterImageApi';
import {fetchPlayerCharacters, updatePlayerCharacterImage} from '@/services/playerCharacterApi';
import {fetchPlayerActiveGames} from '@/services/gameApi';
import type {GameInvitationSummary, PlayerActiveGameSummary} from '@/types/game';
import type {PlayerCharacter} from '@/types/playerCharacter';
import type {RealtimeEventMessage} from '@/types/realtime';
import {resolveCharacterClassLabel, resolveRaceLabel} from '@/utils/catalogLabel';

const router = useRouter();
const {currentUser, ensureSessionLoaded, isAuthenticated, isPending, logoutUser} = useAuthSession();
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
const {pushToast} = useToastCenter();

const playerCharacters = ref<PlayerCharacter[]>([]);
const activeGames = ref<PlayerActiveGameSummary[]>([]);
const characterError = ref('');
const isCharactersLoading = ref(false);
const isActiveGamesLoading = ref(false);
const isCreateCharacterOpen = ref(false);
const imageInputCharacterId = ref<number | null>(null);
const isCharacterImageUploading = ref<number | null>(null);
const selectedInvitation = ref<GameInvitationSummary | null>(null);

/**
 * Загружает список героев текущего игрока.
 */
async function loadCharacters(): Promise<void> {
  isCharactersLoading.value = true;
  characterError.value = '';

  try {
    playerCharacters.value = await fetchPlayerCharacters();
  } catch (error) {
    characterError.value = (error as Error).message;
  } finally {
    isCharactersLoading.value = false;
  }
}

/**
 * Загружает список активных игр, где уже участвуют персонажи игрока.
 */
async function loadActiveGames(): Promise<void> {
  isActiveGamesLoading.value = true;
  characterError.value = '';

  try {
    activeGames.value = await fetchPlayerActiveGames();
  } catch (error) {
    characterError.value = (error as Error).message;
  } finally {
    isActiveGamesLoading.value = false;
  }
}

/**
 * Завершает сессию и возвращает пользователя на экран входа.
 */
async function handleLogout(): Promise<void> {
  await logoutUser();
  await router.push('/');
}

/**
 * Открывает выбор нового фото для указанного героя.
 */
function openCharacterImagePicker(characterId: number): void {
  imageInputCharacterId.value = characterId;
}

/**
 * Загружает и привязывает новое фото к существующему персонажу.
 */
async function handleCharacterImageSelected(event: Event, characterId: number): Promise<void> {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) {
    return;
  }

  isCharacterImageUploading.value = characterId;
  characterError.value = '';

  try {
    const uploadedImage = await uploadPlayerCharacterImage(file);
    const updatedCharacter = await updatePlayerCharacterImage(characterId, uploadedImage.storagePath);
    playerCharacters.value = playerCharacters.value.map((character) => (character.id === characterId ? updatedCharacter : character));
  } catch (error) {
    characterError.value = (error as Error).message;
  } finally {
    isCharacterImageUploading.value = null;
    imageInputCharacterId.value = null;
    input.value = '';
  }
}

/**
 * Открывает pop-up принятия приглашения с выбором героя.
 */
function openInvitationAccept(invitation: GameInvitationSummary): void {
  selectedInvitation.value = invitation;
  characterError.value = '';
}

/**
 * Принимает приглашение выбранным персонажем.
 */
async function handleAcceptInvitation(characterId: number): Promise<void> {
  if (selectedInvitation.value === null) {
    return;
  }

  characterError.value = '';

  try {
    await acceptInvitation(selectedInvitation.value.token, characterId);
    selectedInvitation.value = null;
    await Promise.all([
      loadCharacters(),
      loadInvitations(true),
    ]);
  } catch (error) {
    characterError.value = (error as Error).message;
  }
}

/**
 * Отклоняет приглашение игрока.
 */
async function handleDeclineInvitation(token: string): Promise<void> {
  characterError.value = '';

  try {
    await declineInvitation(token);
    await loadInvitations(true);
  } catch (error) {
    characterError.value = (error as Error).message;
  }
}

/**
 * Открывает runtime-экран активной игры игрока.
 */
async function openActiveGame(gameId: number): Promise<void> {
  await router.push(`/cabinet/player/games/${gameId}/runtime`);
}

/**
 * Обрабатывает realtime-события кабинета игрока.
 */
async function handleRealtimeEvent(message: RealtimeEventMessage): Promise<void> {
  if (message.event === 'game-invitation.created' || message.event === 'game-invitation.accepted' || message.event === 'game-invitation.declined') {
    await loadInvitations(true);
  }

  if (message.event !== 'game-scene.activated' || message.payload.gameId === undefined) {
    return;
  }

  await loadActiveGames();
  pushToast('Игра началась', 'Мастер запустил сцену. Открываем активную игру.', 'info');
  await openActiveGame(message.payload.gameId);
}

onMounted(async () => {
  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');

    return;
  }

  await loadCharacters();
  await loadInvitations();
  await loadActiveGames();
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
      :pending="isPending"
      :player-invitation-badge="pendingInvitationsCount"
      :user="currentUser"
      current-section="player"
      @logout="handleLogout"
  >
    <div class="space-y-6">
      <PlayerCharacterCreateModal
          :open="isCreateCharacterOpen"
          @close="isCreateCharacterOpen = false"
          @created="playerCharacters = [$event, ...playerCharacters]; isCreateCharacterOpen = false"
      />
      <PlayerInvitationAcceptModal
          :invitation="selectedInvitation"
          :open="selectedInvitation !== null"
          :pending="invitationPendingAction === 'accept' && invitationPendingToken === selectedInvitation?.token"
          @accept="handleAcceptInvitation"
          @close="selectedInvitation = null"
      />

      <div class="space-y-3">
        <p class="text-xs uppercase text-amber-200/50">
          Кабинет игрока
        </p>
        <h1 class="font-display text-4xl text-amber-50">
          Твои персонажи
        </h1>
        <p class="max-w-2xl text-sm leading-7 text-slate-300">
          Здесь хранится библиотека твоих героев и мастер создания нового персонажа.
        </p>
      </div>

      <div class="grid gap-4 md:grid-cols-1 xl:grid-cols-1">
        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Check class="h-5 w-5 text-amber-200"/>
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Приглашения
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            {{ pendingInvitationsCount }}
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Ожидают твоего решения. При принятии нужно выбрать свободного героя.
          </p>
        </div>

        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Backpack class="h-5 w-5 text-amber-200"/>
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Персонажи
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            {{ playerCharacters.length }}
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Все твои герои доступны из одного аккаунта и готовы к использованию в разных играх.
          </p>
        </div>

        <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
          <Sparkles class="h-5 w-5 text-amber-200"/>
          <p class="mt-4 text-sm uppercase text-amber-200/50">
            Активные игры
          </p>
          <p class="mt-2 font-display text-2xl text-amber-50">
            {{ activeGames.length }}
          </p>
          <p class="mt-2 text-sm text-slate-300">
            Здесь появляются столы, где мастер уже запустил сцену и твой герой может присоединиться к игре.
          </p>
        </div>
      </div>

      <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-xs uppercase text-amber-200/50">
              Активные игры
            </p>
            <h2 class="mt-3 font-display text-2xl text-amber-50">
              Присоединиться к сцене
            </h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
              Если мастер запустил сцену, она появится здесь. Даже если ты был офлайн, в этот раздел можно вернуться позже и зайти вручную.
            </p>
          </div>
        </div>

        <div
            v-if="isActiveGamesLoading"
            class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
        >
          Загружаем активные игры...
        </div>

        <div
            v-else-if="activeGames.length === 0"
            class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
        >
          Сейчас нет активных сцен, в которые можно войти.
        </div>

        <div
            v-else
            class="mt-5 grid gap-4"
        >
          <article
              v-for="activeGame in activeGames"
              :key="activeGame.id"
              class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-5"
          >
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="space-y-3">
                <p class="text-xs uppercase text-amber-200/50">
                  Активная игра #{{ activeGame.id }}
                </p>
                <h3 class="font-display text-3xl text-amber-50">
                  {{ activeGame.title }}
                </h3>
                <p class="text-sm leading-7 text-slate-300">
                  {{ activeGame.description || 'Описание игры пока не заполнено.' }}
                </p>
                <p class="text-sm text-amber-100/80">
                  Мастер: {{ activeGame.gm.name }}
                </p>
                <p class="text-sm text-slate-300">
                  Сцена: {{ activeGame.active_scene_state?.scene_template?.name || 'Без названия' }}
                </p>
              </div>

              <button
                  class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-200/20"
                  type="button"
                  @click="openActiveGame(activeGame.id)"
              >
                <DoorOpen class="h-4 w-4"/>
                Присоединиться
              </button>
            </div>

            <div
                v-for="member in activeGame.members"
                :key="member.id"
                class="mt-5 rounded-[1.4rem] border border-amber-200/10 bg-white/5 p-4"
            >
              <div class="flex items-start gap-4">
                <div class="h-20 w-20 overflow-hidden rounded-[1.1rem] border border-amber-200/10 bg-slate-900/60">
                  <img
                      v-if="member.player_character?.image_url"
                      :alt="member.player_character.name"
                      :src="member.player_character.image_url"
                      class="h-full w-full object-cover object-top"
                  >
                  <div
                      v-else
                      class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,rgba(245,158,11,0.22),transparent_70%)] text-2xl text-amber-100"
                  >
                    {{ member.player_character?.name?.slice(0, 1) || '?' }}
                  </div>
                </div>

                <div class="min-w-0 flex-1">
                  <h4 class="font-display text-2xl text-amber-50">
                    {{ member.player_character?.name || 'Герой не выбран' }}
                  </h4>
                  <p class="mt-2 text-sm text-amber-100/70">
                    {{ resolveRaceLabel(member.player_character?.race) }}
                    <template v-if="member.player_character?.subrace">
                      · {{ member.player_character.subrace }}
                    </template>
                    · {{ resolveCharacterClassLabel(member.player_character?.class) }}
                    · Ур. {{ member.player_character?.level || '—' }}
                    · XP {{ member.player_character?.experience || 0 }}
                  </p>
                  <p class="mt-3 text-sm leading-7 text-slate-300">
                    {{ member.player_character?.description || 'Описание героя пока не заполнено.' }}
                  </p>
                  <p class="mt-3 text-xs uppercase tracking-[0.24em] text-amber-200/50">
                    Игрок: {{ member.user.name }}
                  </p>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-xs uppercase text-amber-200/50">
              Приглашения в игры
            </p>
            <h2 class="mt-3 font-display text-2xl text-amber-50">
              Ответь на приглашения
            </h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
              Чтобы войти в игру, нужно выбрать персонажа, который не участвует в другой незавершенной игре.
            </p>
          </div>
        </div>

        <div
            v-if="invitationError"
            class="mt-5 rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
        >
          {{ invitationError }}
        </div>

        <div
            v-else-if="invitations.filter((invitation) => invitation.status === 'pending').length === 0"
            class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
        >
          Активных приглашений сейчас нет.
        </div>

        <div
            v-else
            class="mt-5 grid gap-4 xl:grid-cols-2"
        >
          <article
              v-for="invitation in invitations.filter((item) => item.status === 'pending')"
              :key="invitation.id"
              class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-5"
          >
            <p class="text-xs uppercase text-amber-200/50">
              Приглашение #{{ invitation.id }}
            </p>
            <h3 class="mt-2 font-display text-3xl text-amber-50">
              {{ invitation.game.title }}
            </h3>
            <p class="mt-3 text-sm leading-7 text-slate-300">
              {{ invitation.game.description || 'Описание игры пока не заполнено.' }}
            </p>

            <div class="mt-4 flex flex-wrap gap-2 text-xs uppercase text-slate-300">
              <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                ГМ · {{ invitation.gm.name }}
              </span>
              <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                {{ invitation.game.status }}
              </span>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
              <button
                  :disabled="invitationPendingToken === invitation.token"
                  class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15 disabled:cursor-not-allowed disabled:opacity-60"
                  type="button"
                  @click="openInvitationAccept(invitation)"
              >
                <Check class="h-4 w-4"/>
                Принять
              </button>

              <button
                  :disabled="invitationPendingToken === invitation.token"
                  class="inline-flex items-center gap-2 rounded-full border border-rose-300/15 bg-rose-500/10 px-4 py-2 text-sm text-rose-200 transition hover:bg-rose-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                  type="button"
                  @click="handleDeclineInvitation(invitation.token)"
              >
                <X class="h-4 w-4"/>
                {{ invitationPendingAction === 'decline' && invitationPendingToken === invitation.token ? 'Отклоняем...' : 'Отклонить' }}
              </button>
            </div>
          </article>
        </div>
      </section>

      <div>
        <div class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-xs uppercase text-amber-200/50">
                Герои
              </p>
              <h2 class="mt-3 font-display text-2xl text-amber-50">
                Список персонажей игрока
              </h2>
              <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                Здесь хранятся все твои персонажи. Их можно использовать в разных играх, но редактирование пока
                отключено.
              </p>
            </div>

            <button
                class="cta-primary"
                type="button"
                @click="isCreateCharacterOpen = true"
            >
              <Plus class="h-4 w-4"/>
              Создать героя
            </button>
          </div>

          <div
              v-if="characterError"
              class="mt-5 rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
          >
            {{ characterError }}
          </div>

          <div
              v-else-if="isCharactersLoading"
              class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
          >
            Загружаем героев...
          </div>

          <div
              v-else-if="playerCharacters.length === 0"
              class="mt-5 rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
          >
            У тебя пока нет созданных героев.
          </div>

          <div
              v-else
              class="mt-5 grid gap-4 xl:grid-cols-2"
          >
            <article
                v-for="character in playerCharacters"
                :key="character.id"
                class="overflow-hidden rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30"
            >
              <div class="grid min-h-56 md:grid-cols-[10rem_minmax(0,1fr)]">
                <div class="border-b border-amber-200/10 bg-slate-950/40 md:border-b-0 md:border-r">
                  <img
                      v-if="character.image_url"
                      :alt="character.name"
                      :src="character.image_url"
                      class="h-full w-full object-cover"
                  >
                  <div
                      v-else
                      class="flex h-full min-h-56 items-center justify-center text-3xl font-display text-amber-100/80"
                  >
                    {{ character.name.slice(0, 1) }}
                  </div>
                </div>

                <div class="p-5">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <p class="text-xs uppercase text-amber-200/50">
                        Герой #{{ character.id }}
                      </p>
                      <h3 class="mt-2 font-display text-3xl text-amber-50">
                        {{ character.name }}
                      </h3>
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-amber-200/10 bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-100 transition hover:border-amber-200/30">
                      <ImageUp class="h-4 w-4"/>
                      {{ isCharacterImageUploading === character.id ? 'Загрузка...' : 'Сменить фото' }}
                      <input
                          :disabled="isCharacterImageUploading === character.id"
                          accept="image/*"
                          class="hidden"
                          type="file"
                          @change="handleCharacterImageSelected($event, character.id)"
                          @click="openCharacterImagePicker(character.id)"
                      >
                    </label>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2 text-xs uppercase text-slate-300">
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      {{ character.race_name || resolveRaceLabel(character.race) }}
                      <template v-if="character.subrace_name">
                        · {{ character.subrace_name }}
                      </template>
                    </span>
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      {{ character.character_class_name || resolveCharacterClassLabel(character.character_class) }}
                    </span>
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      Ур. {{ character.level }}
                    </span>
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      XP {{ character.experience }}
                    </span>
                  </div>

                  <p class="mt-4 text-sm leading-7 text-slate-300">
                    {{ character.description || 'Описание героя пока не заполнено.' }}
                  </p>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </CabinetShell>
</template>
