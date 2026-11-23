<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { categoryStore, type Category } from '@/domains/categories/store';

const categories = computed(() =>
    Object.values(categoryStore.getters.all.value).sort((first, second) =>
        first.name.localeCompare(second.name)
    )
);

const pendingDelete = ref<Readonly<Category> | null>(null);
const deleting = ref(false);

onMounted(async () => {
    try {
        await categoryStore.actions.getAll();
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
});

const requestDelete = (category: Readonly<Category>) => {
    pendingDelete.value = category;
};

const cancelDelete = () => {
    pendingDelete.value = null;
};

const deleteCategory = async () => {
    if (!pendingDelete.value) return;

    deleting.value = true;
    const deleted = await categoryStore.actions.delete(pendingDelete.value.id);
    deleting.value = false;

    if (deleted) {
        pendingDelete.value = null;
    }
};
</script>

<template>
    <main>
        <h1>Categorieën</h1>

        <p>
            <RouterLink :to="{ name: 'categories.create' }">Nieuwe categorie</RouterLink>
        </p>

        <ErrorMessage v-if="pendingDelete === null" />

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
                        <button type="button" @click="requestDelete(category)">Verwijder</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <ConfirmDialog
            :open="pendingDelete !== null"
            :busy="deleting"
            title="Categorie verwijderen"
            confirm-label="Verwijderen"
            @cancel="cancelDelete"
            @confirm="deleteCategory"
        >
            <p v-if="pendingDelete">
                Weet je zeker dat je {{ pendingDelete.name }} wilt verwijderen?
            </p>

            <ErrorMessage />
        </ConfirmDialog>
    </main>
</template>
