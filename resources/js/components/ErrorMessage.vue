<script setup lang="ts">
import { getMessage, hasErrors } from '@/services/error';
</script>

<template>
    <div
        v-if="getMessage && !hasErrors"
        class="my-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
    >
        {{ getMessage }}
    </div>
</template>
