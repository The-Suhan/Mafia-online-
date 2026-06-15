<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="['base-button', `base-button--${variant}`, `base-button--${size}`, { 'base-button--disabled': disabled }]"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type:     { type: String, default: 'button' },
  variant:  { type: String, default: 'default' }, // default | outline | secondary | ghost | destructive | link
  size:     { type: String, default: 'default' }, // default | sm | lg | icon
  disabled: { type: Boolean, default: false },
})
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

.base-button {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  border-radius: v.$radius-lg;
  border: 1px solid transparent;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
  transition: v.$transition-colors, transform 100ms ease, box-shadow 150ms ease;
  outline: none;
  user-select: none;

  &:focus-visible {
    @include m.focus-ring;
  }

  &:active:not([disabled]) {
    transform: translateY(1px);
  }

  // ── Sizes ──────────────────────────────────────────
  &--default { height: 2rem;   padding: 0 0.625rem; }
  &--sm      { height: 1.75rem; padding: 0 0.625rem; font-size: 0.8rem; border-radius: v.$radius-md; }
  &--lg      { height: 2.25rem; padding: 0 0.625rem; }
  &--icon    { width: 2rem; height: 2rem; padding: 0; }

  // ── Variants ───────────────────────────────────────
  &--default {
    background-color: v.$color-primary;
    color: v.$color-primary-fg;
    &:hover:not([disabled]) { background-color: darken(v.$color-primary, 8%); }
  }

  &--outline {
    border-color: v.$color-border;
    background-color: v.$color-bg;
    color: v.$color-fg;
    &:hover:not([disabled]) { background-color: v.$color-secondary; }
  }

  &--secondary {
    background-color: v.$color-secondary;
    color: v.$color-fg;
    &:hover:not([disabled]) { background-color: darken(v.$color-secondary, 5%); }
  }

  &--ghost {
    background-color: transparent;
    color: v.$color-fg;
    &:hover:not([disabled]) { background-color: v.$color-secondary; }
  }

  &--destructive {
    background-color: rgba(v.$color-destructive, 0.1);
    color: v.$color-destructive;
    &:hover:not([disabled]) { background-color: rgba(v.$color-destructive, 0.2); }
  }

  &--link {
    background: none;
    border: none;
    color: v.$color-primary;
    text-decoration: underline;
    text-underline-offset: 4px;
    padding: 0;
    height: auto;
  }

  // ── Disabled ───────────────────────────────────────
  &--disabled,
  &[disabled] {
    pointer-events: none;
    opacity: 0.5;
  }

  // ── SVG children ───────────────────────────────────
  :deep(svg) {
    pointer-events: none;
    flex-shrink: 0;
    width: 1rem;
    height: 1rem;
  }
}
</style>
