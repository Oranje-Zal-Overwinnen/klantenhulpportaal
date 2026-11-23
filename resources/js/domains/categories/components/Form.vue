<script setup lang="ts">
import { ref } from 'vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import type { Category } from '@/domains/categories/store';

const props = defineProps<{ category?: Category }>();
const emit = defineEmits<{ submit: [data: { name: string }] }>();

const name = ref(props.category?.name ?? '');

const handleSubmit = () => {
    emit('submit', { name: name.value });
};
</script>

<template>
    <ErrorMessage />

    <form @submit.prevent="handleSubmit">
        <p>
            <label for="name">Naam</label>
            <input id="name" v-model="name" type="text" required />
        </p>
        <FormError name="name" />

        <button type="submit">Opslaan</button>
    </form>
</template>
