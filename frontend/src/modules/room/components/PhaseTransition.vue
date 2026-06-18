<template>
  <Teleport to="body">
    <Transition name="phase-fade">
      <div v-if="show" class="phase-transition" :style="overlayStyle">

        <!-- ROLE ASSIGNMENT -->
        <div v-if="phase === 'role_assignment'" class="phase-transition__content">
          <!-- Blood drips for mafia -->
          <template v-if="isMafia">
            <span
              v-for="drip in bloodDrips"
              :key="drip.id"
              class="phase-transition__drip"
              :style="{ left: drip.left, animationDelay: drip.delay, animationDuration: drip.duration }"
            />
          </template>

          <div class="phase-transition__role-icon" :style="{ background: roleColor + '22', border: `2px solid ${roleColor}44` }">
            <span style="font-size: 48px;">{{ roleEmoji }}</span>
          </div>
          <p class="phase-transition__label">YOUR ROLE</p>
          <h1 class="phase-transition__role-name" :style="{ color: roleColor }">
            {{ roleDisplayName }}
          </h1>
          <div class="phase-transition__divider" :style="{ background: roleColor }" />
          <p class="phase-transition__role-desc">{{ roleDescription }}</p>
        </div>

        <!-- NIGHT -->
        <div v-else-if="phase === 'night'" class="phase-transition__content phase-transition__content--night">
          <!-- Stars -->
          <span
            v-for="star in stars"
            :key="star.id"
            class="phase-transition__star"
            :style="{
              left: star.x + '%',
              top: star.y + '%',
              width: star.size + 'px',
              height: star.size + 'px',
              animationDelay: star.delay,
              animationDuration: star.dur,
            }"
          />

          <!-- Moon -->
          <div class="phase-transition__moon" />

          <p class="phase-transition__label">NIGHT PHASE</p>
          <div class="phase-transition__countdown" :class="{ 'phase-transition__countdown--urgent': timeLeft <= 10 }">
            {{ formattedTime }}
          </div>
          <p class="phase-transition__role-desc">Make your move</p>
        </div>

        <!-- DAY ANNOUNCEMENT -->
        <div v-else-if="phase === 'day_announcement'" class="phase-transition__content">
          <div class="phase-transition__sun">☀️</div>
          <p class="phase-transition__label">DAY BREAKS</p>
          <h1 class="phase-transition__role-name" style="color: #fbbf24;">Rise & Shine</h1>
          <div class="phase-transition__divider" style="background: #fbbf24;" />
          <p class="phase-transition__role-desc">The village awakens...</p>
        </div>

        <!-- GAME OVER -->
        <div v-else-if="phase === 'finished'" class="phase-transition__content">
          <span
            v-for="particle in particles"
            :key="particle.id"
            class="phase-transition__particle"
            :style="{
              left: particle.x + '%',
              top: particle.y + '%',
              background: winnerColor,
              '--drift': particle.drift + 'px',
              animationDelay: particle.delay,
              animationDuration: particle.dur,
            }"
          />

          <p class="phase-transition__label">GAME OVER</p>
          <h1 class="phase-transition__role-name" :style="{ color: winnerColor }">
            {{ winner === 'mafia' ? 'MAFIA WINS' : 'VILLAGE WINS' }}
          </h1>
          <div class="phase-transition__divider" :style="{ background: winnerColor }" />
          <p class="phase-transition__role-desc">
            {{ winner === 'mafia' ? 'The shadows have claimed the village.' : 'Justice prevails! The mafia has been defeated.' }}
          </p>
          <button class="phase-transition__close-btn" @click="$emit('done')">
            View Results
          </button>
        </div>

      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  phase: { type: String, default: 'role_assignment' },
  role: { type: String, default: 'villager' },
  winner: { type: String, default: null },
  nightDuration: { type: Number, default: 45 },
})

const emit = defineEmits(['done'])

// ─── Role config ────────────────────────────────────────────────────────────
const ROLE_CONFIG = {
  mafia:      { color: '#dc2626', emoji: '🔪', name: 'MAFIA',      desc: 'Eliminate the villagers one by one.' },
  godfather:  { color: '#dc2626', emoji: '💀', name: 'GODFATHER',  desc: 'Lead the syndicate to victory.' },
  sheriff:    { color: '#fbbf24', emoji: '🔍', name: 'SHERIFF',    desc: 'Investigate and expose the mafia.' },
  doctor:     { color: '#34d399', emoji: '💊', name: 'DOCTOR',     desc: 'Protect the innocent from harm.' },
  vigilante:  { color: '#fb923c', emoji: '🎯', name: 'VIGILANTE',  desc: 'You work alone. Trust no one.' },
  bodyguard:  { color: '#60a5fa', emoji: '🛡️', name: 'BODYGUARD',  desc: 'Guard a player with your life.' },
  mayor:      { color: '#c084fc', emoji: '🏛️', name: 'MAYOR',      desc: 'Your vote counts double.' },
  jester:     { color: '#f472b6', emoji: '🃏', name: 'JESTER',     desc: 'Trick the village into eliminating you.' },
  villager:   { color: '#e5e7eb', emoji: '🏘️', name: 'VILLAGER',   desc: 'Find the mafia before it\'s too late.' },
}

const roleInfo = computed(() => ROLE_CONFIG[props.role] ?? ROLE_CONFIG.villager)
const roleColor = computed(() => roleInfo.value.color)
const roleEmoji = computed(() => roleInfo.value.emoji)
const roleDisplayName = computed(() => roleInfo.value.name)
const roleDescription = computed(() => roleInfo.value.desc)
const isMafia = computed(() => ['mafia', 'godfather'].includes(props.role))

const overlayStyle = computed(() => ({
  background: `radial-gradient(ellipse at 50% 0%, ${roleColor.value}20 0%, #050505 60%)`,
}))

const winnerColor = computed(() => props.winner === 'mafia' ? '#dc2626' : '#34d399')

// ─── Blood drips ─────────────────────────────────────────────────────────────
const bloodDrips = Array.from({ length: 10 }, (_, i) => ({
  id: i,
  left: `${5 + i * 9}%`,
  delay: `${i * 0.3}s`,
  duration: `${1.8 + Math.random() * 1.2}s`,
}))

// ─── Stars ───────────────────────────────────────────────────────────────────
const stars = Array.from({ length: 65 }, (_, i) => ({
  id: i,
  x: Math.random() * 100,
  y: Math.random() * 100,
  size: 1 + Math.random() * 2.5,
  delay: `${Math.random() * 4}s`,
  dur: `${2 + Math.random() * 3}s`,
}))

// ─── Particles ───────────────────────────────────────────────────────────────
const particles = Array.from({ length: 30 }, (_, i) => ({
  id: i,
  x: Math.random() * 100,
  y: -10 + Math.random() * 30,
  drift: -60 + Math.random() * 120,
  delay: `${Math.random() * 2}s`,
  dur: `${2 + Math.random() * 2}s`,
}))

// ─── Countdown timer ─────────────────────────────────────────────────────────
const timeLeft = ref(props.nightDuration)
let timerId = null

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

function startTimer() {
  clearInterval(timerId)
  timeLeft.value = props.nightDuration
  timerId = setInterval(() => {
    timeLeft.value--
    if (timeLeft.value <= 0) {
      clearInterval(timerId)
      emit('done')
    }
  }, 1000)
}

function stopTimer() {
  clearInterval(timerId)
}

// ─── Auto-close logic ────────────────────────────────────────────────────────
let autoTimer = null

watch(
  () => props.show,
  (val) => {
    if (!val) {
      stopTimer()
      clearTimeout(autoTimer)
      return
    }

    if (props.phase === 'role_assignment') {
      autoTimer = setTimeout(() => emit('done'), 4000)
    } else if (props.phase === 'night') {
      startTimer()
    } else if (props.phase === 'day_announcement') {
      autoTimer = setTimeout(() => emit('done'), 3000)
    }
    // 'finished' doesn't auto-close
  },
  { immediate: true }
)

onUnmounted(() => {
  stopTimer()
  clearTimeout(autoTimer)
})
</script>

<style lang="scss" scoped>
.phase-transition {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  &__content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    text-align: center;
    z-index: 2;
    padding: 40px 24px;
    max-width: 400px;
    width: 100%;

    &--night {
      background: transparent;
    }
  }

  // ── Role icon ──
  &__role-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: zoomIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  }

  &__label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
    animation: fadeUp 0.5s 0.3s both;
  }

  &__role-name {
    font-size: 42px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0;
    line-height: 1;
    animation: zoomIn 0.6s 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  }

  &__divider {
    width: 0;
    height: 2px;
    border-radius: 2px;
    animation: expandW 0.5s 0.5s ease both;
  }

  &__role-desc {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
    margin: 0;
    animation: fadeUp 0.5s 0.6s both;
  }

  // ── Blood drips ──
  &__drip {
    position: absolute;
    top: -10px;
    width: 6px;
    border-radius: 0 0 4px 4px;
    background: #dc2626;
    animation: drip linear both infinite;
    z-index: 1;
  }

  // ── Stars ──
  &__star {
    position: absolute;
    border-radius: 50%;
    background: #ffffff;
    animation: twinkle ease-in-out infinite;
  }

  // ── Moon ──
  &__moon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow:
      0 0 0 4px rgba(255, 255, 255, 0.15),
      0 0 40px rgba(255, 255, 255, 0.35),
      0 0 80px rgba(220, 220, 255, 0.2);
    animation: moonArc 6s ease-in-out infinite;
    position: relative;

    &::before {
      content: '';
      position: absolute;
      width: 18px;
      height: 18px;
      background: rgba(180, 180, 200, 0.25);
      border-radius: 50%;
      top: 16px;
      left: 12px;
      box-shadow: 22px 10px 0 rgba(180, 180, 200, 0.15);
    }
  }

  // ── Countdown ──
  &__countdown {
    font-size: 56px;
    font-weight: 900;
    font-family: 'Courier New', monospace;
    color: #ffffff;
    letter-spacing: 0.05em;
    line-height: 1;
    animation: fadeUp 0.5s 0.2s both;
    transition: color 0.3s;

    &--urgent {
      color: #ef4444;
      animation: fadeUp 0.5s 0.2s both, urgentPulse 0.5s ease-in-out infinite;
    }
  }

  // ── Sun ──
  &__sun {
    font-size: 64px;
    animation: zoomIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  }

  // ── Particles ──
  &__particle {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    animation: fall linear both infinite;
  }

  // ── Close button ──
  &__close-btn {
    margin-top: 16px;
    padding: 12px 32px;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
    animation: fadeUp 0.5s 0.8s both;

    &:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  }
}

// ── Transition ──
.phase-fade-enter-active,
.phase-fade-leave-active {
  transition: opacity 0.4s ease;
}
.phase-fade-enter-from,
.phase-fade-leave-to {
  opacity: 0;
}

// ── Keyframes ──
@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.5); }
  to   { opacity: 1; transform: scale(1); }
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

@keyframes expandW {
  from { width: 0; }
  to   { width: 60px; }
}

@keyframes twinkle {
  0%, 100% { opacity: 0.15; }
  50%       { opacity: 1; }
}

@keyframes moonArc {
  0%   { transform: translateX(-130px) translateY(50px); opacity: 0; }
  12%  { opacity: 1; }
  50%  { transform: translateX(0) translateY(-20px); opacity: 1; }
  88%  { opacity: 1; }
  100% { transform: translateX(130px) translateY(50px); opacity: 0; }
}

@keyframes drip {
  0%   { opacity: 0; height: 0; top: -10px; }
  20%  { opacity: 1; }
  70%  { opacity: 1; height: 60px; }
  85%  { opacity: 1; height: 80px; top: 0; }
  100% { opacity: 0; height: 80px; top: 0; }
}

@keyframes fall {
  0%   { opacity: 1; transform: translateY(-20px) translateX(0); }
  100% { opacity: 0; transform: translateY(500px) translateX(var(--drift, 0px)); }
}

@keyframes urgentPulse {
  0%, 100% { transform: scale(1); }
  50%       { transform: scale(1.04); }
}
</style>
