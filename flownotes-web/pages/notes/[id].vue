<script setup lang="ts">
const { api } = useAuth();
const route = useRoute();
const note = ref<any>(null);
const saveStatus = ref<'idle' | 'saving' | 'saved'>('idle');
let saveTimeout: any = null;

onMounted(async () => {
  note.value = await api(`/notes/${route.params.id}`);
});

const saveTitle = async () => {
  await api(`/notes/${route.params.id}`, {
    method: 'PUT',
    body: {
      title: note.value.title,
    }
  });
};

const autosaveContent = () => {
  saveStatus.value = 'saving';
  clearTimeout(saveTimeout);
  
  saveTimeout = setTimeout(async () => {
    try {
      await api(`/notes/${route.params.id}/autosave`, {
        method: 'PATCH',
        body: { content: note.value.content }
      });
      saveStatus.value = 'saved';
      // Reset to idle after a moment (optional, but requested "Saved" state)
      setTimeout(() => { if(saveStatus.value === 'saved') saveStatus.value = 'idle'; }, 2000); 
    } catch (e) {
      saveStatus.value = 'idle'; // Or error state
      console.error(e);
    }
  }, 800);
};

const remove = async () => {
  await api(`/notes/${route.params.id}`, { method: 'DELETE' });
  await navigateTo('/');
};
</script>

<template>
  <div v-if="note">
    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
       <input v-model="note.title" style="flex: 1; padding: 0.5rem;" />
       <button @click="saveTitle">Save Title</button>
       <button @click="remove" style="color: red;">Delete</button>
    </div>

    <div style="margin-bottom: 0.5rem; font-size: 0.8rem; color: gray; height: 1.2em;">
      <span v-if="saveStatus === 'saving'">Saving...</span>
      <span v-if="saveStatus === 'saved'">Saved</span>
    </div>

    <textarea 
      v-model="note.content" 
      @input="autosaveContent"
      style="width: 100%; height: 60vh; padding: 1rem; font-family: monospace;"
    ></textarea>
  </div>
</template>
