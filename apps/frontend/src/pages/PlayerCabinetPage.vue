<script setup lang="ts">
import { Backpack, ImageUp, Plus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import CabinetShell from '@/components/cabinet/CabinetShell.vue';
import PlayerCharacterCreateModal from '@/components/player/PlayerCharacterCreateModal.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { uploadPlayerCharacterImage } from '@/services/playerCharacterImageApi';
import { fetchPlayerCharacters, updatePlayerCharacterImage } from '@/services/playerCharacterApi';
import type { PlayerCharacter } from '@/types/playerCharacter';

const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending, logoutUser } = useAuthSession();

const playerCharacters = ref<PlayerCharacter[]>([]);
const characterError = ref('');
const isCharactersLoading = ref(false);
const isCreateCharacterOpen = ref(false);
const imageInputCharacterId = ref<number | null>(null);
const isCharacterImageUploading = ref<number | null>(null);

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

onMounted(async () => {
  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');

    return;
  }

  await loadCharacters();
});
</script>

<template>
  <CabinetShell
    v-if="currentUser"
    current-section="player"
    :pending="isPending"
    :player-invitation-badge="0"
    :user="currentUser"
    @logout="handleLogout"
  >
    <div class="space-y-6">
      <PlayerCharacterCreateModal
        :open="isCreateCharacterOpen"
        @close="isCreateCharacterOpen = false"
        @created="playerCharacters = [$event, ...playerCharacters]; isCreateCharacterOpen = false"
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
          <Backpack class="h-5 w-5 text-amber-200" />
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
      </div>

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
                Здесь хранятся все твои персонажи. Их можно использовать в разных играх, но редактирование пока отключено.
              </p>
            </div>

            <button
              class="cta-primary"
              type="button"
              @click="isCreateCharacterOpen = true"
            >
              <Plus class="h-4 w-4" />
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
                    :src="character.image_url"
                    :alt="character.name"
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

                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-amber-200/10 bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-100 transition hover:border-amber-200/30">
                      <ImageUp class="h-4 w-4" />
                      {{ isCharacterImageUploading === character.id ? 'Загрузка...' : 'Сменить фото' }}
                      <input
                        class="hidden"
                        :disabled="isCharacterImageUploading === character.id"
                        type="file"
                        accept="image/*"
                        @click="openCharacterImagePicker(character.id)"
                        @change="handleCharacterImageSelected($event, character.id)"
                      >
                    </label>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2 text-xs uppercase text-slate-300">
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      {{ character.race_name || character.race || 'Без расы' }}
                      <template v-if="character.subrace_name">
                        · {{ character.subrace_name }}
                      </template>
                    </span>
                    <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-2">
                      {{ character.character_class_name || character.character_class || 'Без класса' }}
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
