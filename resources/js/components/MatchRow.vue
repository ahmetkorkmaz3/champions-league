<template>
  <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
    <div class="flex items-center space-x-4 flex-1">
      <!-- Home Team -->
      <div class="flex items-center space-x-3 flex-1 justify-end">
        <span class="text-sm font-medium text-gray-900">
          {{ match.home_team.name }}
        </span>
        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
          <img :src="match.home_team.logo" :alt="match.home_team.name" />
        </div>
      </div>

      <!-- Score -->
      <div class="flex items-center space-x-2">
        <span class="text-lg font-bold text-gray-900">
          {{ match.is_played ? match.home_score : '-' }}
        </span>
        <span class="text-gray-500">-</span>
        <span class="text-lg font-bold text-gray-900">
          {{ match.is_played ? match.away_score : '-' }}
        </span>
      </div>

      <!-- Away Team -->
      <div class="flex items-center space-x-3 flex-1 justify-start">
        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
          <img :src="match.away_team.logo" :alt="match.away_team.name" />
        </div>
        <span class="text-sm font-medium text-gray-900">
          {{ match.away_team.name }}
        </span>
      </div>
    </div>

    <!-- Match Actions -->
    <div class="ml-4 flex space-x-2">
      <button
        v-if="match.is_played"
        @click="$emit('edit', match)"
        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
      >
        Düzenle
      </button>
      <button
        v-if="match.is_played"
        @click="$emit('reset', match.id)"
        class="text-red-600 hover:text-red-800 text-sm font-medium"
      >
        Sıfırla
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Match } from '@/types/champions-league'

defineProps<{
  match: Match
}>()

defineEmits<{
  edit: [match: Match]
  reset: [matchId: number]
}>()
</script> 