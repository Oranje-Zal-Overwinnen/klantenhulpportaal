<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { categoryStore } from '@/domains/categories/store';

const route = useRoute();
const router = useRouter();

const categoryId = computed(() => Number(route.params.id));
const category = computed(() => categoryStore.getters.getById(categoryId.value).value);
const ready = ref(false);

const loadCategories = async () => {
    ready.value = false;

    try {
        await categoryStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        ready.value = true;
    }
};

watch(categoryId, loadCategories, { immediate: true });

const handleSubmit = async (data: { name: string }) => {
    try {
        await categoryStore.actions.update(categoryId.value, data);
        await router.push({ name: 'categories.overview' });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
};
</script>

<template>
    <main>
        <h1>Categorie bewerken</h1>

        <Form v-if="ready && category" :category="category" @submit="handleSubmit" />

        <p v-else-if="ready">Categorie niet gevonden.</p>
    </main>
</template>
