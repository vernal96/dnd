<script setup lang="ts">
import { DoorOpen, ScrollText, Shield } from 'lucide-vue-next';
import { RouterLink } from 'vue-router';
import type { SessionUser } from '@/types/auth';

defineProps<{
  currentSection: 'gm' | 'player';
  pending: boolean;
  playerInvitationBadge?: number;
  user: SessionUser;
}>();

const emit = defineEmits<{
  logout: [];
}>();
</script>

<template>
  <main class="relative isolate min-h-screen overflow-hidden px-4 py-6 sm:px-6">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,229,184,0.65),transparent_30%),linear-gradient(180deg,#f7ecd9_0%,#ead6b3_45%,#dcc29b_100%)]" />
    <div class="pointer-events-none absolute inset-0 opacity-40 [background-image:radial-gradient(circle_at_1px_1px,rgba(120,75,19,0.18)_1px,transparent_0)] [background-size:26px_26px]" />

    <div class="relative mx-auto max-w-6xl space-y-5">
      <header class="flex flex-col gap-4 rounded-[1.75rem] border border-amber-950/10 bg-white/40 px-5 py-4 shadow-[0_30px_70px_rgba(82,48,15,0.12)] backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
          <div class="flex h-14 w-14 items-center justify-center rounded-[1.2rem] border border-amber-900/15 bg-[radial-gradient(circle_at_top,rgba(248,216,155,0.75),rgba(78,43,20,0.12)_70%)]">
            <Shield class="h-6 w-6 text-amber-900" />
          </div>

          <div>
            <p class="text-xs uppercase text-amber-950/55">
              Своя Таверна
            </p>
            <p class="font-display text-[1.7rem] text-amber-950">
              Личный кабинет
            </p>
            <p class="text-sm text-amber-950/70">
              {{ user.name }} · {{ user.email }}
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <RouterLink
            :class="currentSection === 'player' ? 'border-amber-900/20 bg-amber-100/60 text-amber-950' : 'border-amber-950/10 bg-white/40 text-amber-950/75'"
            class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2 text-sm font-medium transition hover:bg-amber-100/50"
            to="/cabinet/player"
          >
            <ScrollText class="h-4 w-4" />
            Игрок
            <span
              v-if="typeof playerInvitationBadge === 'number' && playerInvitationBadge > 0"
              class="inline-flex min-w-6 items-center justify-center rounded-full border border-amber-900/15 bg-amber-900 px-1.5 py-0.5 text-[0.68rem] font-semibold leading-none text-amber-50"
            >
              {{ playerInvitationBadge }}
            </span>
          </RouterLink>

          <RouterLink
            v-if="user.canAccessGm"
            :class="currentSection === 'gm' ? 'border-amber-900/20 bg-amber-100/60 text-amber-950' : 'border-amber-950/10 bg-white/40 text-amber-950/75'"
            class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2 text-sm font-medium transition hover:bg-amber-100/50"
            to="/cabinet/gm"
          >
            <Shield class="h-4 w-4" />
            ГМ
          </RouterLink>

          <button
            class="inline-flex items-center gap-2 rounded-2xl border border-amber-950/10 bg-white/40 px-4 py-2 text-sm font-medium text-amber-950/80 transition hover:bg-rose-100/50"
            :disabled="pending"
            type="button"
            @click="emit('logout')"
          >
            <DoorOpen class="h-4 w-4" />
            Выйти
          </button>
        </div>
      </header>

      <section class="rounded-[1.9rem] border border-amber-950/10 bg-[linear-gradient(180deg,rgba(20,12,25,0.95),rgba(37,22,50,0.92))] p-5 text-slate-100 shadow-[0_30px_80px_rgba(37,20,49,0.34)] sm:p-6">
        <slot />
      </section>
    </div>
  </main>
</template>
