<script setup lang="ts">
import { Shield, Sparkles } from 'lucide-vue-next';
</script>

<template>
  <div class="relative flex items-start gap-4">
    <div class="relative flex h-16 w-16 items-center justify-center rounded-[1.4rem] border border-amber-300/20 bg-[radial-gradient(circle_at_top,rgba(248,216,155,0.35),rgba(29,16,38,0.98)_70%)] shadow-glow">
      <div class="absolute inset-2 rounded-[1rem] border border-amber-100/15" />
      <Shield class="relative h-7 w-7 text-amber-200" />
      <Sparkles class="absolute -right-1 -top-1 h-4 w-4 text-amber-100" />
    </div>

    <div class="space-y-1.5">
      <div class="inline-flex items-center gap-2 rounded-full border border-amber-200/10 bg-white/5 px-3 py-1 text-[0.65rem] uppercase tracking-[0.35em] text-amber-100/70">
        <span class="h-1.5 w-1.5 rounded-full bg-amber-300 shadow-[0_0_12px_rgba(248,216,155,0.7)]" />
        Guild Access
      </div>
      <p class="font-display text-[1.9rem] leading-none text-amber-50 sm:text-[2.2rem]">
        Table of Adventures
      </p>
      <p class="max-w-sm text-sm leading-6 text-slate-300">
        Доступ к хроникам, персонажам и залу мастера начинается здесь.
      </p>
    </div>
  </div>
</template>
