<script setup lang="ts">
defineProps<{
  title: string
  id: number
}>()
</script>

<template>
  <div
    class="bg-[#e1f0fc] rounded-md p-3 shadow-sm cursor-pointer hover:bg-blue-100 transition"
  >
    <p class="font-semibold">{{ title }}</p>
  </div>
</template>
