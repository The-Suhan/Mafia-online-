<template>
  <input
    v-bind="$attrs"
    :type="type"
    :class="['base-input', { 'base-input--invalid': invalid }]"
  />
</template>

<script setup>
defineProps({
  type:    { type: String, default: 'text' },
  invalid: { type: Boolean, default: false },
})
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

.base-input {
  height: 2rem;
  width: 100%;
  min-width: 0;
  border-radius: v.$radius-lg;
  border: 1px solid v.$color-border;
  background-color: rgba(v.$color-input, 0.3);
  padding: 0.25rem 0.625rem;
  font-size: 0.875rem;
  color: v.$color-fg;
  transition: v.$transition-colors, box-shadow 150ms ease;
  outline: none;
  font-family: inherit;

  &::placeholder {
    color: v.$color-muted-fg;
  }

  &:focus-visible {
    @include m.focus-ring;
  }

  &:disabled {
    pointer-events: none;
    cursor: not-allowed;
    background-color: rgba(v.$color-input, 0.5);
    opacity: 0.5;
  }

  &--invalid {
    border-color: v.$color-destructive;
    box-shadow: 0 0 0 3px rgba(v.$color-destructive, 0.2);
  }
}
</style>
