<template>
  <div class="login-page">
    <!-- Ambient noir lighting -->
    <div class="login-page__backdrop" aria-hidden="true" />
    <div class="login-page__scanlines" aria-hidden="true" />

    <section class="login-page__section">
      <!-- Red glow behind card -->
      <div class="login-page__glow" aria-hidden="true" />

      <div class="login-page__card">
        <header class="login-page__card-header">
          <div class="login-page__badge">
            <span class="login-page__badge-letter">M</span>
          </div>
          <h1 class="login-page__title">
            <span>M<span class="login-page__title--accent">a</span>fia</span>
          </h1>
          <div class="login-page__divider" />
          <p class="login-page__subtitle">
            Step into the shadows. Sign in to take your seat at the table.
          </p>
        </header>

        <!-- Login Form -->
        <form class="login-page__form" @submit.prevent="handleSubmit" novalidate>
          <!-- Email -->
          <div class="login-page__field">
            <label for="email" class="login-page__label">Email</label>
            <div class="login-page__input-wrapper">
              <svg class="login-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="16" x="2" y="4" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
              <BaseInput id="email" v-model="form.email" type="email" autocomplete="email"
                placeholder="you@thefamily.com" :class="{ 'is-invalid': errors.email }"
                class="login-page__input login-page__input--with-icon" />
            </div>
            <span v-if="errors.email" class="login-page__error">{{ errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="login-page__field">
            <label for="password" class="login-page__label">Password</label>
            <div class="login-page__input-wrapper">
              <svg class="login-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <BaseInput id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password" placeholder="••••••••" :class="{ 'is-invalid': errors.password }"
                class="login-page__input login-page__input--with-icon login-page__input--with-toggle" />
              <button type="button" class="login-page__password-toggle"
                :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                <!-- Eye -->
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <!-- EyeOff -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                  <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                  <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                  <line x1="2" x2="22" y1="2" y2="22" />
                </svg>
              </button>
            </div>
            <span v-if="errors.password" class="login-page__error">{{ errors.password }}</span>
          </div>

          <!-- Remember me + Forgot password -->
          <div class="login-page__extras">
            <label class="login-page__remember">
              <input type="checkbox" v-model="form.remember" class="login-page__checkbox" />
              <span>Remember me</span>
            </label>
            <a href="/forgot-password" class="login-page__forgot">Forgot password?</a>
          </div>

          <!-- General API error -->
          <p v-if="errors.general" class="login-page__error login-page__error--general">
            {{ errors.general }}
          </p>

          <BaseButton type="submit" class="login-page__submit" :disabled="loading">
            {{ loading ? 'Signing in…' : 'Sign In' }}
          </BaseButton>
        </form>

        <p class="login-page__footer">
          Don't have an account?&nbsp;
          <RouterLink to="/register" class="login-page__link">Sign up</RouterLink>
        </p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const loading = ref(false)

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const errors = reactive({})

function validate() {
  Object.keys(errors).forEach((k) => delete errors[k])
  let valid = true

  if (!form.email) {
    errors.email = 'Email is required.'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Password is required.'
    valid = false
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters.'
    valid = false
  }

  return valid
}

async function handleSubmit() {
  if (!validate()) return

  loading.value = true
  try {
    await authStore.login({ email: form.email, password: form.password })
    router.push('/')
  } catch (err) {
    const serverErrors = err?.response?.data?.errors
    if (serverErrors) {
      Object.keys(serverErrors).forEach((field) => {
        errors[field] = Array.isArray(serverErrors[field])
          ? serverErrors[field][0]
          : serverErrors[field]
      })
    } else {
      errors.general = err?.response?.data?.message || 'Invalid credentials. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

// ─── Design tokens (mirror globals.css custom properties) ───────────────────
$color-bg: oklch(0.16 0.005 285);
$color-card: oklch(0.205 0.006 285);
$color-primary: oklch(0.55 0.21 25);
$color-foreground: oklch(0.92 0.004 285);
$color-muted-fg: oklch(0.65 0.005 285);
$color-input-bg: oklch(0.25 0.008 285);
$color-border: oklch(0.3 0.01 285);
$color-destructive: oklch(0.6 0.23 25);

// ─── Page ───────────────────────────────────────────────────────────────────
.login-page {
  position: relative;
  display: flex;
  min-height: 100svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: $color-bg;
  padding: 2.5rem 1rem;

  // Ambient noir radial glow from top
  &__backdrop {
    pointer-events: none;
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% -10%,
        oklch(0.55 0.21 25 / 0.18),
        transparent 55%);
  }

  // Subtle scanline grid
  &__scanlines {
    pointer-events: none;
    position: absolute;
    inset: 0;
    opacity: 0.04;
    background-image:
      linear-gradient(oklch(1 0 0) 1px, transparent 1px),
      linear-gradient(90deg, oklch(1 0 0) 1px, transparent 1px);
    background-size: 40px 40px;
  }

  // ─── Section ──────────────────────────────────────────────────────────────
  &__section {
    position: relative;
    width: 100%;
    max-width: 28rem;
  }

  // Red glow bleeding behind card
  &__glow {
    position: absolute;
    inset: -1px;
    border-radius: 1rem;
    background-color: oklch(0.55 0.21 25 / 0.3);
    opacity: 0.6;
    filter: blur(2rem);
    pointer-events: none;
  }

  // ─── Card ─────────────────────────────────────────────────────────────────
  &__card {
    position: relative;
    border-radius: 1rem;
    border: 1px solid oklch(0.55 0.21 25 / 0.3);
    background-color: oklch(0.205 0.006 285 / 0.8);
    padding: 2rem;
    backdrop-filter: blur(4px);
    box-shadow:
      0 0 40px -12px $color-primary,
      inset 0 1px 0 0 oklch(1 0 0 / 0.04);

    @media (min-width: 640px) {
      padding: 2.5rem;
    }

    &-header {
      margin-bottom: 2rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }

  // ─── Header ───────────────────────────────────────────────────────────────
  &__badge {
    margin-bottom: 1rem;
    display: flex;
    width: 3rem;
    height: 3rem;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid oklch(0.55 0.21 25 / 0.4);
    background-color: oklch(0.55 0.21 25 / 0.1);

    &-letter {
      font-family: var(--font-cinzel, serif);
      font-size: 1.25rem;
      font-weight: 900;
      color: $color-primary;
    }
  }

  &__title {
    font-family: var(--font-cinzel, serif);
    font-size: 2.5rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.35em;
    color: $color-foreground;
    padding-left: 0.35em; // optical compensation for tracking

    @media (min-width: 640px) {
      font-size: 3rem;
    }

    &--accent {
      color: $color-primary;
    }
  }

  &__divider {
    margin-top: 0.75rem;
    height: 1px;
    width: 4rem;
    background: linear-gradient(to right, transparent, $color-primary, transparent);
  }

  &__subtitle {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: $color-muted-fg;
    text-wrap: balance;
  }

  // ─── Form ─────────────────────────────────────────────────────────────────
  &__form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  &__field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  &__label {
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-muted-fg;
  }

  &__input-wrapper {
    position: relative;
  }

  &__input-icon {
    pointer-events: none;
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: $color-muted-fg;
    width: 1rem;
    height: 1rem;
  }

  &__input {
    height: 2.75rem;
    width: 100%;
    background-color: oklch(0.25 0.008 285 / 0.6);
    color: $color-foreground;
    border: 1px solid $color-border;
    border-radius: 0.5rem;
    padding: 0 0.75rem;
    font-size: 0.875rem;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;

    &::placeholder {
      color: oklch(0.65 0.005 285 / 0.6);
    }

    &:focus {
      border-color: $color-primary;
      box-shadow: 0 0 0 3px oklch(0.55 0.21 25 / 0.2);
    }

    &.is-invalid {
      border-color: $color-destructive;
      box-shadow: 0 0 0 3px oklch(0.6 0.23 25 / 0.2);
    }

    &--with-icon {
      padding-left: 2.5rem;
    }

    &--with-toggle {
      padding-right: 2.5rem;
    }
  }

  &__password-toggle {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    width: 1.75rem;
    height: 1.75rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    background: transparent;
    border: none;
    cursor: pointer;
    color: $color-muted-fg;
    transition: color 0.15s;

    &:hover {
      color: $color-foreground;
    }
  }

  // ─── Extras row ───────────────────────────────────────────────────────────
  &__extras {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
  }

  &__remember {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: $color-muted-fg;
    cursor: pointer;
    user-select: none;
  }

  &__checkbox {
    width: 0.9rem;
    height: 0.9rem;
    accent-color: $color-primary;
    cursor: pointer;
  }

  &__forgot {
    color: $color-muted-fg;
    text-decoration: none;
    transition: color 0.15s;

    &:hover {
      color: $color-foreground;
      text-decoration: underline;
      text-underline-offset: 4px;
    }
  }

  // ─── Submit ───────────────────────────────────────────────────────────────
  &__submit {
    margin-top: 0.25rem;
    height: 2.75rem;
    width: 100%;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    box-shadow: 0 0 20px -4px $color-primary;
    transition: box-shadow 0.2s;

    &:hover:not(:disabled) {
      box-shadow: 0 0 28px -2px $color-primary;
    }
  }

  // ─── Errors ───────────────────────────────────────────────────────────────
  &__error {
    font-size: 0.78rem;
    color: $color-destructive;

    &--general {
      text-align: center;
      padding: 0.5rem 0.75rem;
      border-radius: 0.375rem;
      background-color: oklch(0.6 0.23 25 / 0.1);
      border: 1px solid oklch(0.6 0.23 25 / 0.3);
    }
  }

  // ─── Footer ───────────────────────────────────────────────────────────────
  &__footer {
    margin-top: 1.75rem;
    text-align: center;
    font-size: 0.875rem;
    color: $color-muted-fg;
  }

  &__link {
    font-weight: 500;
    color: $color-primary;
    text-decoration: none;
    text-underline-offset: 4px;
    transition: color 0.15s;

    &:hover {
      color: $color-foreground;
      text-decoration: underline;
    }
  }
}
</style>