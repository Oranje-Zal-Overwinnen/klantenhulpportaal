<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { authStore, type RegisterData } from '@/domains/auth/store';

const router = useRouter();

const form = ref<RegisterData>({
    first_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    password: '',
    password_confirmation: ''
});
const submitting = ref(false);

const handleSubmit = async () => {
    submitting.value = true;

    try {
        await authStore.actions.register({
            ...form.value,
            phone_number: form.value.phone_number || null
        });

        await router.push({ name: 'auth.login', query: { registered: '1' } });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <main>
        <h1>Registreren</h1>

        <ErrorMessage />

        <form @submit.prevent="handleSubmit">
            <p>
                <label for="first_name">Voornaam</label>
                <input id="first_name" v-model="form.first_name" type="text" autocomplete="given-name" required />
            </p>
            <FormError name="first_name" />

            <p>
                <label for="last_name">Achternaam</label>
                <input id="last_name" v-model="form.last_name" type="text" autocomplete="family-name" required />
            </p>
            <FormError name="last_name" />

            <p>
                <label for="email">E-mailadres</label>
                <input id="email" v-model="form.email" type="email" autocomplete="email" required />
            </p>
            <FormError name="email" />

            <p>
                <label for="phone_number">Telefoonnummer</label>
                <input id="phone_number" v-model="form.phone_number" type="tel" autocomplete="tel" />
            </p>
            <FormError name="phone_number" />

            <p>
                <label for="password">Wachtwoord</label>
                <input id="password" v-model="form.password" type="password" autocomplete="new-password" minlength="8" required />
            </p>
            <FormError name="password" />

            <p>
                <label for="password_confirmation">Wachtwoord bevestigen</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                />
            </p>

            <button type="submit" :disabled="submitting">Registreren</button>
        </form>

        <p>
            <RouterLink :to="{ name: 'auth.login' }">Al een account? Inloggen</RouterLink>
        </p>
    </main>
</template>
