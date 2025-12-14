<script setup>
import { Loader2Icon } from "lucide-vue-next"

const props = defineProps()
</script>

<template>
  <Loader2Icon
    role="status"
    aria-label="Loading"
    class="size-4 animate-spin"
  />
</template>
