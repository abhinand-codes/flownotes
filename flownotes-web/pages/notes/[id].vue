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

    <!-- Wikilinks Preview Area -->
    <div style="margin-top: 2rem; border-top: 1px solid #ccc; padding-top: 1rem;">
      <h3>Preview (Clickable Links)</h3>
      <div v-html="processedContent" @click="handleLinkClick"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
// ... imports ... (reusing existing script logic but adding computed and handler)

const processedContent = computed(() => {
  if (!note.value?.content) return '';
  return note.value.content.replace(/\[\[(.*?)\]\]/g, (match, title) => {
    return `<a href="#" data-title="${title}" style="color: blue; text-decoration: underline;">${match}</a>`;
  });
});

const handleLinkClick = async (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (target.tagName === 'A' && target.dataset.title) {
    event.preventDefault();
    const title = target.dataset.title;
    
    // Find note by title via API or just creating it by navigating to new? 
    // Optimization: The backend NoteController creates it on save. 
    // Here we need to find the ID of that note.
    // Ideally we'd have an endpoint to find note by title, or fetch all notes to find it.
    // For now, let's fetch all notes (inefficient but fits current capabilities) or try to find it.
    // Actually, Step 4 requirements say "If link target does not exist yet, backend auto-creates it" (on save).
    // So if we just saved, it should exist.
    // We need to resolve title -> id.
    
    // Let's implement a quick lookup.
    const notes = await api('/notes');
    const targetNote = notes.find((n: any) => n.title === title);
    
    if (targetNote) {
       await navigateTo(`/notes/${targetNote.id}`);
    } else {
       // Should trigger a save to ensure it exists, but user might be clicking before autosave finishes?
       // For now, if not found, we can force a save then retry? 
       // Or easier: navigate to a "new" note with that title? 
       // Requirement: "If link target note does not exist yet, backend auto-creates it" (implied on parsing).
       // So if the user typed it and autosave ran, it exists.
       alert('Note not found (try waiting for autosave)');
    }
  }
};
</script>
