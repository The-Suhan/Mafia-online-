<template>
  <div class="night-panel">
    <!-- Passive roles -->
    <template v-if="isPassiveRole">
      <div class="night-panel__passive">
        <span class="night-panel__passive-icon">🌙</span>
        <p class="night-panel__passive-text">You have no action tonight.</p>
        <p class="night-panel__passive-sub">Wait for dawn...</p>
      </div>
    </template>

    <!-- Active roles -->
    <template v-else>
      <div class="night-panel__header">
        <h3 class="night-panel__title">{{ panelTitle }}</h3>
      </div>

      <!-- Submitted state -->
      <div v-if="submitted" class="night-panel__submitted">
        <span class="night-panel__submitted-icon">✓</span>
        <p>Action submitted. Waiting for others...</p>
      </div>

      <!-- Target selection -->
      <template v-else>
        <ul class="night-panel__targets">
          <li
            v-for="player in targetList"
            :key="player.user_id"
            class="night-panel__target"
            :class="{ 'night-panel__target--selected': selectedId === player.user_id }"
            @click="selectedId = player.user_id"
          >
            <img
              :src="avatarUrl(player.nickname)"
              :alt="player.nickname"
              class="night-panel__target-avatar"
            />
            <span class="night-panel__target-name">{{ player.nickname }}</span>
            <span v-if="selectedId === player.user_id" class="night-panel__target-check">✓</span>
          </li>
        </ul>

        <div class="night-panel__footer">
          <button
            class="night-panel__confirm"
            :disabled="!selectedId"
            @click="confirmAction"
          >
            {{ confirmLabel }}
          </button>
        </div>
      </template>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/modules/auth/store'

const props = defineProps({
  myRole: { type: String, default: 'villager' },
  alivePlayers: { type: Array, default: () => [] },
  myMafiaTeam: { type: Array, default: () => [] },
  sessionId: { type: Number, required: true },
  round: { type: Number, default: 1 },
})

const emit = defineEmits(['actionSubmitted'])

const auth = useAuthStore()
const selectedId = ref(null)
const submitted = ref(false)

const PASSIVE_ROLES = ['villager', 'mayor', 'bodyguard', 'vigilante', 'jester']

const isPassiveRole = computed(() => PASSIVE_ROLES.includes(props.myRole))

const panelTitle = computed(() => {
  switch (props.myRole) {
    case 'mafia':
    case 'godfather': return 'Choose your target'
    case 'doctor': return 'Who will you protect tonight?'
    case 'sheriff': return 'Investigate a player'
    default: return ''
  }
})

const confirmLabel = computed(() => {
  switch (props.myRole) {
    case 'mafia':
    case 'godfather': return 'Confirm Kill'
    case 'doctor': return 'Protect'
    case 'sheriff': return 'Investigate'
    default: return 'Confirm'
  }
})

const targetList = computed(() => {
  const myId = auth.user?.id
  switch (props.myRole) {
    case 'mafia':
    case 'godfather':
      // Exclude mafia teammates
      return props.alivePlayers.filter(
        (p) => p.user_id !== myId && !props.myMafiaTeam.includes(p.user_id)
      )
    case 'doctor':
      // Can protect self
      return props.alivePlayers.filter((p) => p.user_id !== myId || true)
    case 'sheriff':
      // Exclude self
      return props.alivePlayers.filter((p) => p.user_id !== myId)
    default:
      return []
  }
})

const actionType = computed(() => {
  switch (props.myRole) {
    case 'mafia':
    case 'godfather': return 'kill'
    case 'doctor': return 'heal'
    case 'sheriff': return 'inspect'
    default: return null
  }
})

async function confirmAction() {
  if (!selectedId.value || !actionType.value) return
  emit('actionSubmitted', {
    action_type: actionType.value,
    target_id: selectedId.value,
    round: props.round,
  })
  submitted.value = true
}

function avatarUrl(nickname) {
  return `https://api.dicebear.com/7.x/bottts-neutral/svg?seed=${encodeURIComponent(nickname)}`
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;

.night-panel {
  background: rgba(5, 5, 20, 0.6);
  border-top: 1px solid $color-border;
  display: flex;
  flex-direction: column;
  max-height: 260px;

  &__passive {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    gap: 6px;
    text-align: center;
  }

  &__passive-icon {
    font-size: 32px;
    margin-bottom: 4px;
  }

  &__passive-text {
    font-size: 14px;
    font-weight: 500;
    color: $color-foreground;
    margin: 0;
  }

  &__passive-sub {
    font-size: 12px;
    color: $color-muted-foreground;
    margin: 0;
    font-style: italic;
  }

  &__header {
    padding: 12px 16px 8px;
    flex-shrink: 0;
  }

  &__title {
    font-size: 13px;
    font-weight: 600;
    color: $color-muted-foreground;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0;
  }

  &__submitted {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    color: #10b981;
    font-size: 14px;
    font-weight: 500;
  }

  &__submitted-icon {
    font-size: 18px;
  }

  &__targets {
    list-style: none;
    margin: 0;
    padding: 0 8px;
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: $color-border transparent;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__target {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s;

    &:hover {
      background: rgba(255, 255, 255, 0.04);
    }

    &--selected {
      background: rgba($color-primary, 0.12);
      border-color: rgba($color-primary, 0.4);
    }
  }

  &__target-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: $color-secondary;
    border: 1px solid $color-border;
  }

  &__target-name {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: $color-foreground;
  }

  &__target-check {
    font-size: 12px;
    color: $color-primary;
    font-weight: 700;
  }

  &__footer {
    padding: 10px 16px 14px;
    flex-shrink: 0;
  }

  &__confirm {
    width: 100%;
    padding: 8px;
    border-radius: 8px;
    border: none;
    background: $color-primary;
    color: $color-primary-foreground;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s;

    &:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }

    &:not(:disabled):hover {
      opacity: 0.85;
    }
  }
}
</style>
