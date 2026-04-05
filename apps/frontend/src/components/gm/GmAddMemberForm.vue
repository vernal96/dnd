<script lang="ts" setup>
import {UserPlus} from 'lucide-vue-next';
import {computed, ref} from 'vue';
import FormTextInput from '@/components/form/FormTextInput.vue';

const props = defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  submit: [login: string];
}>();

const login = ref('');
const localError = ref('');

const isDisabled = computed<boolean>(() => login.value.trim().length < 3 || props.pending);

/**
 * Валидирует форму и отправляет логин или email для приглашения игрока.
 */
function submitForm(): void {
  localError.value = '';

  if (login.value.trim().length < 3) {
    localError.value = 'Укажи логин или email пользователя.';

    return;
  }

  emit('submit', login.value.trim());
  login.value = '';
}
</script>

<template>
  <form
      class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5"
      @submit.prevent="submitForm"
  >
    <p class="text-xs uppercase text-amber-200/50">
      Приглашение в игру
    </p>
    <h2 class="mt-3 font-display text-2xl text-amber-50">
      Пригласить игрока
    </h2>
    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
      Введи логин или email существующего пользователя. Он увидит приглашение в своем личном кабинете и сможет принять
      его вручную.
    </p>

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
      <FormTextInput
          v-model="login"
          autocomplete="username"
          label="Логин или email"
          name="member_login"
          placeholder="Например, denis или denis@mail.ru"
      />

      <button
          :disabled="isDisabled"
          class="cta-primary lg:min-w-[13rem]"
          type="submit"
      >
        <UserPlus class="h-4 w-4"/>
        {{ pending ? 'Отправляем...' : 'Отправить приглашение' }}
      </button>
    </div>

    <p
        v-if="localError"
        class="mt-4 rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>
  </form>
</template>
