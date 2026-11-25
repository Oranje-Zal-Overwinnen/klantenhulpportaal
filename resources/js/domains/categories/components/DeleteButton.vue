<script setup lang="ts">
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { categoryStore, type Category } from '@/domains/categories/store';

const props = defineProps<{ category: Readonly<Category> }>();
const emit = defineEmits<{ deleted: [] }>();

const open = ref(false);
const deleting = ref(false);

const close = () => {
    if (deleting.value) return;
    open.value = false;
};

const deleteCategory = async () => {
    deleting.value = true;
    const deleted = await categoryStore.actions.delete(props.category.id);
    deleting.value = false;

    if (deleted) {
        open.value = false;
        emit('deleted');
    }
};
</script>

<template>
    <button type="button" @click="open = true">Verwijder</button>

    <ConfirmDialog
        :open="open"
        :busy="deleting"
        title="Categorie verwijderen"
        confirm-label="Verwijderen"
        @cancel="close"
        @confirm="deleteCategory"
    >
        <p>Weet je zeker dat je {{ category.name }} wilt verwijderen?</p>

        <ErrorMessage />
    </ConfirmDialog>
</template>
