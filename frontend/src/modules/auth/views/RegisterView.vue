  <template>
  <main class="register-page">
    <!-- Atmospheric noir backdrop -->
    <div class="register-page__backdrop register-page__backdrop--glow" aria-hidden="true" />
    <div class="register-page__backdrop register-page__backdrop--scanlines" aria-hidden="true" />

    <section class="register-page__section">
      <div class="register-card">
        <!-- ── Logo / Heading ── -->
        <div class="register-card__header">
          <span class="register-card__badge">The Family</span>
          <h1 class="register-card__title">
            Maf<span class="register-card__title-accent">i</span>a
          </h1>
          <p class="register-card__subtitle">
            Create your account and earn your place at the table.
          </p>
        </div>

        <!-- ── Form ── -->
        <div class="register-form" @submit.prevent="handleSubmit">

          <!-- Nickname -->
          <div class="form-group">
            <label class="form-label" for="nickname">Nickname</label>
            <BaseInput
              id="nickname"
              v-model="form.nickname"
              type="text"
              autocomplete="nickname"
              placeholder="Don Corleone"
              :invalid="!!errors.nickname"
            />
            <span v-if="errors.nickname" class="form-error">{{ errors.nickname }}</span>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <BaseInput
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="you@famiglia.com"
              :invalid="!!errors.email"
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrapper">
              <BaseInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="••••••••"
                class="input-wrapper__input"
                :invalid="!!errors.password"
              />
              <button
                type="button"
                class="input-wrapper__toggle"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" :size="16" />
                <Eye v-else :size="16" />
              </button>
            </div>
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label class="form-label" for="confirmPassword">Confirm Password</label>
            <div class="input-wrapper">
              <BaseInput
                id="confirmPassword"
                v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="••••••••"
                class="input-wrapper__input"
                :invalid="!!errors.password_confirmation"
              />
              <button
                type="button"
                class="input-wrapper__toggle"
                :aria-label="showConfirm ? 'Hide password' : 'Show password'"
                @click="showConfirm = !showConfirm"
              >
                <EyeOff v-if="showConfirm" :size="16" />
                <Eye v-else :size="16" />
              </button>
            </div>
            <span v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation }}</span>
          </div>

          <!-- General API error -->
          <p v-if="apiError" class="form-error form-error--general">{{ apiError }}</p>

          <!-- Submit -->
          <BaseButton
            type="submit"
            class="submit-btn"
            :disabled="isLoading"
            @click.prevent="handleSubmit"
          >
            <span v-if="isLoading">Creating account…</span>
            <span v-else>Create Account</span>
          </BaseButton>
        </div>

        <!-- Sign in link -->
        <p class="register-card__footer">
          Already have an account?
          <RouterLink to="/login" class="register-card__link">Sign in</RouterLink>
        </p>
      </div>

      <p class="register-page__tagline">
        What happens in the family, stays in the family.
      </p>
    </section>
  </main>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff } from 'lucide-vue-next'
import { useAuthStore } from '../store'
import BaseInput  from '@/shared/components/BaseInput.vue'
import BaseButton from '@/shared/components/BaseButton.vue'

const router   = useRouter()
const authStore = useAuthStore()

// ── Form state ──────────────────────────────────────────────────────────────
const form = reactive({
  nickname:              '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

const errors   = reactive({})
const apiError = ref(null)
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirm  = ref(false)

// ── Client-side validation ──────────────────────────────────────────────────
function validate() {
  // Clear previous
  Object.keys(errors).forEach(k => delete errors[k])
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

// ── Submit ──────────────────────────────────────────────────────────────────
async function handleSubmit() {
  if (!validate()) return

  isLoading.value = true
  try {
    await authStore.register({ ...form })
    router.push('/')
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      // Laravel validation errors: { field: ['message', ...] }
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

// ── Page shell ───────────────────────────────────────────────────────────────
.register-page {
  position: relative;
  display: flex;
  min-height: 100svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: v.$color-bg;
  padding: 2.5rem 1rem;

  &__backdrop {
    pointer-events: none;
    position: absolute;
    inset: 0;

    &--glow {
      opacity: 0.6;
      background-image:
        radial-gradient(circle at 50% 0%,   oklch(0.3 0.04 25 / 0.5),  transparent 55%),
        radial-gradient(circle at 50% 100%, oklch(0.2 0.01 285 / 0.6), transparent 60%);
    }

    &--scanlines {
      opacity: 0.04;
      background-image: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        oklch(1 0 0) 2px,
        oklch(1 0 0) 3px
      );
    }
  }

  &__section {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 28rem;
  }

  &__tagline {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: rgba(v.$color-muted-fg, 0.7);
  }
}

// ── Card ─────────────────────────────────────────────────────────────────────
.register-card {
  border-radius: v.$radius-xl;
  border: 1px solid v.$color-border;
  background-color: rgba(v.$color-card, 0.9);
  padding: 2rem;
  box-shadow: v.$shadow-card;
  backdrop-filter: blur(4px);

  @media (min-width: 640px) {
    padding: 2.5rem;
  }

  &__header {
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    text-align: center;
  }

  &__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 1px solid rgba(v.$color-primary, 0.4);
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    color: v.$color-primary;
  }

  &__title {
    font-family: v.$font-heading;
    font-size: 3rem;
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1;
    letter-spacing: 0.15em;
    color: v.$color-fg;
  }

  &__title-accent {
    color: v.$color-primary;
  }

  &__subtitle {
    font-size: 0.875rem;
    color: v.$color-muted-fg;
    text-wrap: balance;
  }

  &__footer {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: v.$color-muted-fg;
  }

  &__link {
    font-weight: 500;
    color: v.$color-primary;
    text-underline-offset: 4px;
    transition: v.$transition-colors;

    &:hover {
      color: v.$color-fg;
      text-decoration: underline;
    }
  }
}

// ── Form ─────────────────────────────────────────────────────────────────────
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1;
  color: v.$color-fg;
  user-select: none;
}

.form-error {
  font-size: 0.75rem;
  color: v.$color-destructive;

  &--general {
    text-align: center;
    margin-top: -0.25rem;
  }
}

// ── Password toggle wrapper ───────────────────────────────────────────────────
.input-wrapper {
  position: relative;

  &__input {
    padding-right: 2.5rem;
  }

  &__toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: v.$color-muted-fg;
    display: flex;
    align-items: center;
    transition: color 150ms ease;
    outline: none;

    &:hover {
      color: v.$color-fg;
    }

    &:focus-visible {
      @include m.focus-ring;
      border-radius: v.$radius-sm;
    }
  }
}

// ── Submit button ─────────────────────────────────────────────────────────────
.submit-btn {
  margin-top: 0.5rem;
  width: 100%;
  height: 2.25rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  font-size: 0.875rem;
}
</style>
