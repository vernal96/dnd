<script lang="ts" setup>
import {Bell, CheckCircle2, CircleAlert, X} from 'lucide-vue-next';
import {useToastCenter} from '@/composables/useToastCenter';

const {items, removeToast} = useToastCenter();

/**
 * Возвращает иконку и оформление toast-уведомления по его типу.
 */
function resolveToastToneClasses(tone: 'error' | 'info' | 'success'): string {
  if (tone === 'success') {
    return 'border-emerald-300/25 bg-emerald-500/12 text-emerald-50';
  }

  if (tone === 'error') {
    return 'border-rose-300/25 bg-rose-500/12 text-rose-50';
  }

  return 'border-amber-300/20 bg-amber-400/10 text-amber-50';
}
</script>

<template>
  <div class="pointer-events-none fixed right-4 top-4 z-[80] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3">
    <transition-group
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
        move-class="transition duration-200 ease-out"
        tag="div"
    >
      <div
          v-for="toast in items"
          :key="toast.id"
          :class="resolveToastToneClasses(toast.tone)"
          class="pointer-events-auto rounded-[1.35rem] border px-4 py-3 shadow-[0_20px_45px_rgba(15,10,25,0.35)] backdrop-blur-md"
      >
        <div class="flex items-start gap-3">
          <div class="mt-0.5">
            <CheckCircle2
                v-if="toast.tone === 'success'"
                class="h-5 w-5"
            />
            <CircleAlert
                v-else-if="toast.tone === 'error'"
                class="h-5 w-5"
            />
            <Bell
                v-else
                class="h-5 w-5"
            />
          </div>

          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold">
              {{ toast.title }}
            </p>
            <p class="mt-1 text-sm leading-6 text-current/85">
              {{ toast.message }}
            </p>
          </div>

          <button
              class="rounded-full p-1 text-current/70 transition hover:bg-white/10 hover:text-current"
              type="button"
              @click="removeToast(toast.id)"
          >
            <X class="h-4 w-4"/>
          </button>
        </div>
      </div>
    </transition-group>
  </div>
</template>
