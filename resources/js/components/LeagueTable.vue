<template>
  <div class="bg-white rounded-lg shadow-md mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-xl font-semibold text-gray-900">{{ title }}</h2>
      <p v-if="subtitle" class="text-sm text-gray-600 mt-1">
        {{ subtitle }}
      </p>
    </div>

    <!-- Info Section (optional) -->
    <div v-if="infoContent" class="px-6 py-4 bg-blue-50 border-b border-blue-200">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-blue-800">
            {{ infoTitle }}
          </h3>
          <div class="mt-2 text-sm text-blue-700">
            <p>{{ infoContent }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- League Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Sıra
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Takım
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              O
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              G
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              B
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              M
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              A
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Y
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Av
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Puan
            </th>
            <th v-if="showPowerLevel" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Güç
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(standing, index) in standings" :key="standing.id || index" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              <div class="flex items-center">
                <span class="mr-2">{{ standing.position }}</span>
                <!-- Trophy for winner -->
                <span v-if="standing.position === 1" class="text-yellow-500">🏆</span>
                <!-- Medal for runner-up -->
                <span v-else-if="standing.position === 2" class="text-gray-400">🥈</span>
                <!-- Bronze for 3rd -->
                <span v-else-if="standing.position === 3" class="text-amber-600">🥉</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                    <img :src="standing.team.logo" :alt="standing.team.name" class="w-full h-full object-cover" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ standing.team.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ standing.team.city }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ getMatchesPlayed(standing) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ standing.wins }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ standing.draws }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ standing.losses }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ standing.goals_for }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              {{ standing.goals_against }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              <span :class="standing.goal_difference > 0 ? 'text-green-600' : standing.goal_difference < 0 ? 'text-red-600' : 'text-gray-900'">
                {{ standing.goal_difference > 0 ? '+' : '' }}{{ standing.goal_difference }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
              {{ standing.points }}
            </td>
            <td v-if="showPowerLevel" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
              <div class="flex items-center justify-center">
                <div class="w-16 bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full"
                    :style="{ width: ('power_level' in standing.team ? standing.team.power_level : 0) / 100 * 100 + '%' }"
                  ></div>
                </div>
                <span class="ml-2 text-xs text-gray-500">{{ 'power_level' in standing.team ? standing.team.power_level : 0 }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Statistics Section (optional) -->
    <div v-if="showStatistics" class="px-6 py-4 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">📈 İstatistikler</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">En Çok Gol Atan:</span>
              <span class="text-sm font-medium text-gray-900">
                {{ getTopScorer() }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">En Az Gol Yiyen:</span>
              <span class="text-sm font-medium text-gray-900">
                {{ getBestDefense() }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">En Yüksek Puan:</span>
              <span class="text-sm font-medium text-gray-900">
                {{ getHighestPoints() }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="showChampionshipProbability" class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">🎯 Şampiyonluk Olasılığı</h3>
          <div class="space-y-2">
            <div v-for="(standing, index) in standings.slice(0, 3)" :key="index" class="flex justify-between items-center">
              <span class="text-sm text-gray-600">{{ standing.team.name }}:</span>
              <div class="flex items-center">
                <div class="w-12 bg-gray-200 rounded-full h-2 mr-2">
                  <div
                    class="bg-green-600 h-2 rounded-full"
                    :style="{ width: championshipProbabilities[index] + '%' }"
                  ></div>
                </div>
                <span class="text-xs text-gray-500">{{ championshipProbabilities[index] }}%</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="showMatchAnalysis" class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">⚽ Maç Analizi</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Toplam Maç:</span>
              <span class="text-sm font-medium text-gray-900">6</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Oynanan Maç:</span>
              <span class="text-sm font-medium text-gray-900">{{ currentWeek }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Kalan Maç:</span>
              <span class="text-sm font-medium text-gray-900">{{ 6 - currentWeek }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Standing, PredictedStanding } from '@/types/champions-league'

interface Props {
  title: string
  subtitle?: string
  standings: (Standing | PredictedStanding)[]
  showPowerLevel?: boolean
  showStatistics?: boolean
  showChampionshipProbability?: boolean
  showMatchAnalysis?: boolean
  currentWeek?: number
  infoTitle?: string
  infoContent?: string
  championshipProbabilities?: number[]
}

const props = withDefaults(defineProps<Props>(), {
  showPowerLevel: false,
  showStatistics: false,
  showChampionshipProbability: false,
  showMatchAnalysis: false,
  currentWeek: 0
})

// Helper function to get matches played
const getMatchesPlayed = (standing: Standing | PredictedStanding) => {
  if ('matches_played' in standing) {
    return standing.matches_played
  }
  return standing.wins + standing.draws + standing.losses
}

// Statistics helper functions
const getTopScorer = () => {
  if (!props.standings?.length) return '-'
  const topScorer = props.standings.reduce((prev, current) =>
    prev.goals_for > current.goals_for ? prev : current
  )
  return `${topScorer.team.name} (${topScorer.goals_for})`
}

const getBestDefense = () => {
  if (!props.standings?.length) return '-'
  const bestDefense = props.standings.reduce((prev, current) =>
    prev.goals_against < current.goals_against ? prev : current
  )
  return `${bestDefense.team.name} (${bestDefense.goals_against})`
}

const getHighestPoints = () => {
  if (!props.standings?.length) return '-'
  const highestPoints = props.standings.reduce((prev, current) =>
    prev.points > current.points ? prev : current
  )
  return `${highestPoints.team.name} (${highestPoints.points})`
}

const championshipProbabilities = computed(() => {
  return props.championshipProbabilities || []
})
</script> 