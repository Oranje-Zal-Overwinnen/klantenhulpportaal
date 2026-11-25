<script setup lang="ts">
import { onMounted, ref, useId, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        confirmLabel?: string;
        busy?: boolean;
    }>(),
    {
        title: 'Bevestigen',
        confirmLabel: 'Bevestigen',
        busy: false
    }
);

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

const titleId = useId();
const dialog = ref<HTMLDialogElement | null>(null);

const sync = () => {
    if (!dialog.value) return;

    if (props.open && !dialog.value.open) {
        dialog.value.showModal();
    }

    if (!props.open && dialog.value.open) {
        dialog.value.close();
    }
};

onMounted(sync);
watch(() => props.open, sync, { flush: 'post' });
</script>

<template>
    <dialog ref="dialog" :aria-labelledby="titleId" @cancel.prevent="emit('cancel')">
        <h2 :id="titleId">{{ title }}</h2>
        <slot />
        <menu>
            <button type="button" :disabled="busy" @click="emit('cancel')">Annuleren</button>
            <button type="button" :disabled="busy" @click="emit('confirm')">{{ confirmLabel }}</button>
        </menu>
    </dialog>
</template>
