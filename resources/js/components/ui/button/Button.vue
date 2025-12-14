<script setup>
import { Primitive } from 'reka-ui';

const props = defineProps({
  as: { type: String, default: 'button' },
  asChild: { type: Boolean, default: false },
});
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
  >
    <slot />
  </Primitive>
</template>
