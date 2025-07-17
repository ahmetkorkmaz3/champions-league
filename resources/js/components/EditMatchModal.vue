<template>
  <div v-if="show" class="fixed inset-0 bg-transparent overflow-hidden h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-900">⚽ Maç Sonucunu Düzenle</h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="px-6 py-6">
        <!-- Match Teams Display -->
        <div class="mb-6">
          <div class="flex items-center justify-center space-x-4 mb-4">
            <!-- Home Team -->
            <div class="flex flex-col items-center space-y-2">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                <img 
                  :src="match?.home_team?.logo" 
                  :alt="match?.home_team?.name"
                  class="w-8 h-8 object-contain"
                />
              </div>
              <span class="text-sm font-medium text-gray-900 text-center max-w-24">
                {{ match?.home_team?.name }}
              </span>
            </div>

            <!-- VS -->
            <div class="flex flex-col items-center">
              <span class="text-2xl font-bold text-gray-400">VS</span>
            </div>

            <!-- Away Team -->
            <div class="flex flex-col items-center space-y-2">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                <img 
                  :src="match?.away_team?.logo" 
                  :alt="match?.away_team?.name"
                  class="w-8 h-8 object-contain"
                />
              </div>
              <span class="text-sm font-medium text-gray-900 text-center max-w-24">
                {{ match?.away_team?.name }}
              </span>
            </div>
          </div>
        </div>

        <!-- Score Inputs -->
        <div class="space-y-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ match?.home_team?.name }} Skoru
            </label>
            <div class="relative">
              <input
                v-model="form.home_score"
                type="number"
                min="0"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                placeholder="0"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="text-gray-400 text-sm">gol</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ match?.away_team?.name }} Skoru
            </label>
            <div class="relative">
              <input
                v-model="form.away_score"
                type="number"
                min="0"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                placeholder="0"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="text-gray-400 text-sm">gol</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview Score -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
          <div class="text-center">
            <span class="text-sm text-blue-600 font-medium">Önizleme:</span>
            <div class="text-2xl font-bold text-blue-800 mt-1">
              {{ form.home_score }} - {{ form.away_score }}
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
        <div class="flex justify-end space-x-3">
          <button
            @click="$emit('close')"
            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
          >
            İptal
          </button>
          <button
            @click="handleSave"
            class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            💾 Kaydet
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Match, MatchForm } from '@/types/champions-league'

const props = defineProps<{
  show: boolean
  match: Match | null
}>()

const emit = defineEmits<{
  close: []
  save: [form: MatchForm]
}>()

const form = ref<MatchForm>({
  home_score: 0,
  away_score: 0,
})

// Watch for changes in the match prop and update form
watch(() => props.match, (newMatch) => {
  if (newMatch) {
    form.value = {
      home_score: newMatch.home_score || 0,
      away_score: newMatch.away_score || 0,
    }
  }
}, { immediate: true })

const handleSave = () => {
  emit('save', form.value)
}
</script> 