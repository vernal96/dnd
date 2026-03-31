<template>
  <div class="fantasy-panel mx-auto w-full max-w-[34rem]">
    <div class="ornament-corner -left-4 -top-4" />
    <div class="ornament-corner -bottom-4 -right-4" />
    <div class="ornament-corner -right-5 top-24 h-10 w-10 opacity-70" />
    <div class="ornament-corner bottom-24 -left-5 h-10 w-10 opacity-70" />

    <div class="relative space-y-7 px-5 py-6 sm:px-8 sm:py-8">
      <slot />
    </div>
  </div>
</template>
