<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { categoryStore } from '@/domains/categories/store';

const route = useRoute();
const router = useRouter();

const categoryId = Number(route.params.id);
const category = categoryStore.getters.getById(categoryId);
const ready = ref(false);

onMounted(async () => {
    try {
        await categoryStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        ready.value = true;
    }
});

const handleSubmit = async (data: { name: string }) => {
    try {
        await categoryStore.actions.update(categoryId, data);
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
