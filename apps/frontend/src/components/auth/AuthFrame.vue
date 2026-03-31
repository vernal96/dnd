<template>
  <div class="fantasy-panel mx-auto w-full max-w-[26.5rem]">
    <div class="ornament-corner -left-3 -top-3 h-10 w-10" />
    <div class="ornament-corner -bottom-3 -right-3 h-10 w-10" />
    <div class="ornament-corner -right-4 top-20 h-8 w-8 opacity-70" />
    <div class="ornament-corner bottom-20 -left-4 h-8 w-8 opacity-70" />

    <div class="relative space-y-4 px-4 py-4 sm:px-5 sm:py-5">
      <slot />
    </div>
  </div>
</template>
