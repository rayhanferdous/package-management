<script setup>
import { reactiveOmit } from "@vueuse/core"
import { Label } from "reka-ui"

const props = defineProps()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <Label data-slot="label" v-bind="delegatedProps" class="
    flex items-center gap-2 text-sm leading-none font-medium select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50">
    <slot />
  </Label>
</template>
