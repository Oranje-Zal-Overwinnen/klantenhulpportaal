<script setup lang="ts">
import { computed, onMounted } from 'vue';
import DeleteButton from '../components/DeleteButton.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { categoryStore } from '@/domains/categories/store';

const categories = computed(() =>
    Object.values(categoryStore.getters.all.value).sort((first, second) =>
        first.name.localeCompare(second.name)
    )
);

onMounted(async () => {
    try {
        await categoryStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});
</script>

<template>
    <main>
        <h1>Categorieën</h1>

        <p>
            <RouterLink :to="{ name: 'categories.create' }">Nieuwe categorie</RouterLink>
        </p>

        <ErrorMessage />

        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="category in categories" :key="category.id">
                    <td>{{ category.name }}</td>
                    <td>
                        <RouterLink :to="{ name: 'categories.edit', params: { id: category.id } }">Bewerk</RouterLink>
                        <DeleteButton :category="category" />
                    </td>
                </tr>
            </tbody>
        </table>
    </main>
</template>
