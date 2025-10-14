import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const isAuthed = ref(false);

    function setToAuthed() {
        isAuthed.value = true;
    }

    function setToUnauthed() {
        isAuthed.value = false;
    }

    function checkWhetherAuthed() {
        // TODO hit the api and return true or false based on what you get back
        return true;
    }

    return { isAuthed, setToAuthed, setToUnauthed, checkWhetherAuthed };
});
