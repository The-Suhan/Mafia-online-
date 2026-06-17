<template>
  <main class="register-page">
    <!-- Ambient noir lighting -->
    <div class="register-page__backdrop" aria-hidden="true" />
    <div class="register-page__scanlines" aria-hidden="true" />

    <section class="register-page__section">
      <!-- Red glow behind card -->
      <div class="register-page__glow" aria-hidden="true" />

      <div class="register-page__card">
        <header class="register-page__card-header">
          <div class="register-page__badge">
            <span class="register-page__badge-letter">M</span>
          </div>
          <h1 class="register-page__title">
            <span>M<span class="register-page__title--accent">a</span>fia</span>
          </h1>
          <div class="register-page__divider" />
          <p class="register-page__subtitle">
            Create your account and earn your place at the table.
          </p>
        </header>

        <!-- Register Form -->
        <div class="register-page__form">

          <!-- Nickname -->
          <div class="register-page__field">
            <label for="nickname" class="register-page__label">Nickname</label>
            <div class="register-page__input-wrapper">
              <svg class="register-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5" />
                <path d="M20 21a8 8 0 1 0-16 0" />
              </svg>
              <BaseInput id="nickname" v-model="form.nickname" type="text" autocomplete="nickname"
                placeholder="Don Corleone" :class="{ 'is-invalid': errors.nickname }"
                class="register-page__input register-page__input--with-icon" />
            </div>
            <span v-if="errors.nickname" class="register-page__error">{{ errors.nickname }}</span>
          </div>

          <!-- Email -->
          <div class="register-page__field">
            <label for="email" class="register-page__label">Email</label>
            <div class="register-page__input-wrapper">
              <svg class="register-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="16" x="2" y="4" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
              <BaseInput id="email" v-model="form.email" type="email" autocomplete="email"
                placeholder="you@famiglia.com" :class="{ 'is-invalid': errors.email }"
                class="register-page__input register-page__input--with-icon" />
            </div>
            <span v-if="errors.email" class="register-page__error">{{ errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="register-page__field">
            <label for="password" class="register-page__label">Password</label>
            <div class="register-page__input-wrapper">
              <svg class="register-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <BaseInput id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password" placeholder="••••••••" :class="{ 'is-invalid': errors.password }"
                class="register-page__input register-page__input--with-icon register-page__input--with-toggle" />
              <button type="button" class="register-page__password-toggle"
                :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                  <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                  <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                  <line x1="2" x2="22" y1="2" y2="22" />
                </svg>
              </button>
            </div>
            <span v-if="errors.password" class="register-page__error">{{ errors.password }}</span>
          </div>

          <!-- Confirm Password -->
          <div class="register-page__field">
            <label for="confirmPassword" class="register-page__label">Confirm Password</label>
            <div class="register-page__input-wrapper">
              <svg class="register-page__input-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <BaseInput id="confirmPassword" v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'" autocomplete="new-password" placeholder="••••••••"
                :class="{ 'is-invalid': errors.password_confirmation }"
                class="register-page__input register-page__input--with-icon register-page__input--with-toggle" />
              <button type="button" class="register-page__password-toggle"
                :aria-label="showConfirm ? 'Hide password' : 'Show password'" @click="showConfirm = !showConfirm">
                <svg v-if="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                  <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                  <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                  <line x1="2" x2="22" y1="2" y2="22" />
                </svg>
              </button>
            </div>
            <span v-if="errors.password_confirmation" class="register-page__error">{{ errors.password_confirmation
              }}</span>
          </div>

          <!-- General API error -->
          <p v-if="apiError" class="register-page__error register-page__error--general">
            {{ apiError }}
          </p>

          <BaseButton type="button" class="register-page__submit" :disabled="isLoading" @click="handleSubmit">
            {{ isLoading ? 'Creating account…' : 'Create Account' }}
          </BaseButton>
        </div>

        <p class="register-page__footer">
          Already have an account?&nbsp;
          <RouterLink to="/login" class="register-page__link">Sign in</RouterLink>
        </p>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import BaseInput from '@/shared/components/BaseInput.vue'
import BaseButton from '@/shared/components/BaseButton.vue'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const showConfirm = ref(false)
const isLoading = ref(false)

const form = reactive({
  nickname: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive({})
const apiError = ref(null)

function validate() {
  Object.keys(errors).forEach((k) => delete errors[k])
  apiError.value = null
  let valid = true

  if (!form.nickname.trim()) {
    errors.nickname = 'Nickname is required.'
    valid = false
  }

  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email.trim()) {
    errors.email = 'Email is required.'
    valid = false
  } else if (!emailRe.test(form.email)) {
    errors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Password is required.'
    valid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters.'
    valid = false
  }

  if (!form.password_confirmation) {
    errors.password_confirmation = 'Please confirm your password.'
    valid = false
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match.'
    valid = false
  }

  return valid
}

async function handleSubmit() {
  if (!validate()) return

  isLoading.value = true
  try {
    await authStore.register({ ...form })
    router.push('/')
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      Object.entries(data.errors).forEach(([field, messages]) => {
        errors[field] = Array.isArray(messages) ? messages[0] : messages
      })
    } else {
      apiError.value = data?.message || 'Something went wrong. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

$color-bg: oklch(0.16 0.005 285);
$color-card: oklch(0.205 0.006 285);
$color-primary: oklch(0.55 0.21 25);
$color-foreground: oklch(0.92 0.004 285);
$color-muted-fg: oklch(0.65 0.005 285);
$color-input-bg: oklch(0.25 0.008 285);
$color-border: oklch(0.3 0.01 285);
$color-destructive: oklch(0.6 0.23 25);

.register-page {
  position: relative;
  display: flex;
  min-height: 100svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: $color-bg;
  padding: 2.5rem 1rem;

  &__backdrop {
    pointer-events: none;
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% -10%,
        oklch(0.55 0.21 25 / 0.18),
        transparent 55%);
  }

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

  &__section {
    position: relative;
    width: 100%;
    max-width: 28rem;
  }

  &__glow {
    position: absolute;
    inset: -1px;
    border-radius: 1rem;
    background-color: oklch(0.55 0.21 25 / 0.3);
    opacity: 0.6;
    filter: blur(2rem);
    pointer-events: none;
  }

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
    padding-left: 0.35em;

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