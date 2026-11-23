<script setup lang="ts">
import { ref } from 'vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import type { User, UserFormData } from '@/domains/users/store';

const props = defineProps<{ user: User }>();
const emit = defineEmits<{ submit: [data: UserFormData] }>();

const form = ref<UserFormData>({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    role: props.user.role,
    phone_number: props.user.phone_number
});

const handleSubmit = () => {
    emit('submit', {
        ...form.value,
        phone_number: form.value.phone_number || null
    });
};
</script>

<template>
    <ErrorMessage />

    <form @submit.prevent="handleSubmit">
        <p>
            <label for="first_name">Voornaam</label>
            <input id="first_name" v-model="form.first_name" type="text" required />
        </p>
        <FormError name="first_name" />

        <p>
            <label for="last_name">Achternaam</label>
            <input id="last_name" v-model="form.last_name" type="text" required />
        </p>
        <FormError name="last_name" />

        <p>
            <label for="email">E-mailadres</label>
            <input id="email" v-model="form.email" type="email" required />
        </p>
        <FormError name="email" />

        <p>
            <label for="role">Rol</label>
            <select id="role" v-model="form.role" required>
                <option value="user">Gebruiker</option>
                <option value="admin">Administrator</option>
            </select>
        </p>
        <FormError name="role" />

        <p>
            <label for="phone_number">Telefoonnummer</label>
            <input id="phone_number" v-model="form.phone_number" type="tel" />
        </p>
        <FormError name="phone_number" />

        <button type="submit">Opslaan</button>
    </form>
</template>
