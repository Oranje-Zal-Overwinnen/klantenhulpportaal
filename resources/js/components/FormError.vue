<script setup lang="ts">
import { computed } from 'vue';
import { getErrorBag } from '@/services/error';

const props = defineProps<{ name: string }>();

const errors = computed(() =>
    Object.entries(getErrorBag.value)
        .filter(([key]) => key === props.name || key.startsWith(`${props.name}.`))
        .flatMap(([, messages]) => messages)
);
</script>

<template>
    <div v-for="error in errors" :key="error" class="mt-1 text-sm text-red-600">
        {{ error }}
    </div>
</template>
