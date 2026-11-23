<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { categoryStore } from '@/domains/categories/store';
import type { Ticket, TicketFormData } from '@/domains/tickets/store';

const props = defineProps<{ ticket?: Ticket }>();
const emit = defineEmits<{ submit: [data: TicketFormData] }>();

const form = ref<TicketFormData>({
    title: props.ticket?.title ?? '',
    description: props.ticket?.description ?? '',
    categories: props.ticket?.categories?.map(category => category.id) ?? []
});

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

const handleSubmit = () => {
    emit('submit', form.value);
};
</script>

<template>
    <ErrorMessage />

    <form @submit.prevent="handleSubmit">
        <p>
            <label for="title">Titel</label>
            <input id="title" v-model="form.title" type="text" required />
        </p>
        <FormError name="title" />

        <p>
            <label for="description">Omschrijving</label>
            <textarea id="description" v-model="form.description" rows="5" required></textarea>
        </p>
        <FormError name="description" />

        <p>
            <label for="categories">Categorieën</label>
            <select id="categories" v-model="form.categories" multiple>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
        </p>
        <FormError name="categories" />

        <button type="submit">Opslaan</button>
    </form>
</template>
