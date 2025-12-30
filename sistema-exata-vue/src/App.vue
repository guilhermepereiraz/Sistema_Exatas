<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView v-slot="{ Component }">
    <Transition>
       <!-- <Transition name="page-fade" mode="out-in"> -->
      <component :is="Component" />
    </Transition>
  </RouterView>
</template>

<style>

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}


.page-fade-enter-active,
.page-fade-leave-active {
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
 
}


.page-fade-enter-from,
.page-fade-leave-to {
  opacity: 0;
  transform: translateY(10px); 
  overflow: hidden;
}


.page-fade-enter-to,
.page-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
  overflow: hidden;
}
</style>
