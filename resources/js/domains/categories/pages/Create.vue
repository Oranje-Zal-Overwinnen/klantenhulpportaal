<script setup lang="ts">
import { useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { categoryStore } from '@/domains/categories/store';

const router = useRouter();

const handleSubmit = async (data: { name: string }) => {
    try {
        await categoryStore.actions.create(data);
        await router.push({ name: 'categories.overview' });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
};
</script>

<template>
    <main>
        <h1>Nieuwe categorie</h1>

        <Form @submit="handleSubmit" />
    </main>
</template>
