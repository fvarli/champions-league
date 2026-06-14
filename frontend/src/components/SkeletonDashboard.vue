<template>
  <div class="space-y-6" aria-hidden="true">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div
        v-for="n in 4"
        :key="n"
        class="skeleton h-24 rounded-2xl border border-white/5 bg-slate-900/50"
      />
    </div>

    <div class="skeleton h-16 rounded-2xl border border-white/5 bg-slate-900/50" />

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="space-y-6 lg:col-span-2">
        <div class="rounded-2xl border border-white/5 bg-slate-900/50 p-5">
          <div class="skeleton h-4 w-32 rounded bg-white/10" />
          <div class="mt-5 space-y-3">
            <div v-for="n in 4" :key="n" class="skeleton h-8 rounded-lg bg-white/5" />
          </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="n in 3"
            :key="n"
            class="skeleton h-32 rounded-2xl border border-white/5 bg-slate-900/50"
          />
        </div>
      </div>

      <div class="rounded-2xl border border-white/5 bg-slate-900/50 p-5">
        <div class="skeleton h-4 w-40 rounded bg-white/10" />
        <div class="mt-5 space-y-4">
          <div v-for="n in 4" :key="n" class="space-y-2">
            <div class="skeleton h-3 w-24 rounded bg-white/10" />
            <div class="skeleton h-2 rounded-full bg-white/5" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
