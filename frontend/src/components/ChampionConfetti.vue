<script setup lang="ts">
const prefersReducedMotion =
  typeof window !== 'undefined' &&
  typeof window.matchMedia === 'function' &&
  window.matchMedia('(prefers-reduced-motion: reduce)').matches

const colors = ['#fbbf24', '#34d399', '#38bdf8', '#f472b6', '#a78bfa', '#ffffff']

interface Piece {
  left: number
  delay: number
  duration: number
  color: string
  rotate: number
  size: number
  drift: number
}

const pieces: Piece[] = prefersReducedMotion
  ? []
  : Array.from({ length: 64 }, (_, i) => ({
      left: Math.random() * 100,
      delay: Math.random() * 0.6,
      duration: 2.4 + Math.random() * 1.6,
      color: colors[i % colors.length],
      rotate: 120 + Math.random() * 480,
      size: 6 + Math.random() * 6,
      drift: (Math.random() - 0.5) * 90,
    }))

function pieceStyle(piece: Piece): Record<string, string> {
  return {
    left: `${piece.left}%`,
    width: `${piece.size}px`,
    height: `${piece.size * 1.6}px`,
    backgroundColor: piece.color,
    animationDelay: `${piece.delay}s`,
    animationDuration: `${piece.duration}s`,
    '--drift': `${piece.drift}px`,
    '--rot': `${piece.rotate}deg`,
  }
}
</script>

<template>
  <div class="pointer-events-none fixed inset-0 z-40 overflow-hidden" aria-hidden="true">
    <span
      v-for="(piece, index) in pieces"
      :key="index"
      class="confetti-piece absolute top-0 rounded-[2px]"
      :style="pieceStyle(piece)"
    />
  </div>
</template>
