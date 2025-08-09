<script lang="ts">
  import { fly, fade, slide } from 'svelte/transition';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import { enhance } from '$app/forms';
  import { invalidateAll } from '$app/navigation';
  import type { PageData, ActionData } from './$types';

  let { data, form }: { data: PageData; form: ActionData } = $props();

  interface Announcement {
    id: number;
    title: string;
    content: string | null;
    date_posted: Date | string | null;
    display_date: string | null;
    category: string;
    priority: string;
    attachment?: string | null;
    author: string;
    is_active: number;
    created_at: string | Date | null;
    updated_at: string | Date | null;
  }

  // --- STATE MANAGEMENT ---
  let announcements = $derived(data.announcements || []);
  let loaded = $state(false);
  let visible = $state(false);
  let isProcessing = $state(false);
  let selectedAnnouncementId = $state<number | undefined>(undefined);
  let isEditingTitle = $state(false);
  let isEditingDate = $state(false);
  let isEditingContent = $state(false);
  let tempTitle = $state('');
  let tempDate = $state('');
  let tempContent = $state('');
  let isAdminLoggedIn = $state(false);
  let cookieCheckInterval: NodeJS.Timeout | null = null;
  let currentAnnouncementIndex = $state(0);
  let currentAnnouncement = $derived(announcements[currentAnnouncementIndex]);
  let hasNextAnnouncement = $derived(currentAnnouncementIndex < announcements.length - 1);
  let hasPreviousAnnouncement = $derived(currentAnnouncementIndex > 0);
  let showNewAnnouncementModal = $state(false);
  let newAnnouncement = $state({ title: '', display_date: '', content: '', category: 'general' });
  let createEvent = $state(false);
  let selectedImageFile = $state<File | null>(null);
  let imagePreviewUrl = $state<string | null>(null);
  let editImageFile = $state<File | null>(null);
  let editImagePreviewUrl = $state<string | null>(null);

  const imageBaseUrl = 'http://localhost/back-ends/uploads/';

  // --- FUNCTIONS ---
  function checkAdminStatus() {
    if (browser) {
      const authCookie = document.cookie.split('; ').find(row => row.startsWith('auth='));
      isAdminLoggedIn = !!authCookie;
    }
  }
  function startCookieCheck() {
    if (browser && !cookieCheckInterval) {
      cookieCheckInterval = setInterval(checkAdminStatus, 500);
    }
  }
  function stopCookieCheck() {
    if (cookieCheckInterval) clearInterval(cookieCheckInterval);
  }
  function startEditingTitle(announcementId: number) {
    const announcement = announcements.find((a) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempTitle = announcement.title ?? '';
      isEditingTitle = true;
    }
  }
  function startEditingDate(announcementId: number) {
    const announcement = announcements.find((a) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempDate = announcement.display_date ?? '';
      isEditingDate = true;
    }
  }
  function startEditingContent(announcementId: number) {
    const announcement = announcements.find((a) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempContent = announcement.content ?? '';
      isEditingContent = true;
    }
  }
  function cancelEdit() {
    isEditingTitle = false;
    isEditingDate = false;
    isEditingContent = false;
    selectedAnnouncementId = undefined;
    editImageFile = null;
    if (editImagePreviewUrl) URL.revokeObjectURL(editImagePreviewUrl);
    editImagePreviewUrl = null;
  }
  function openNewAnnouncementModal() {
    showNewAnnouncementModal = true;
  }
  function closeNewAnnouncementModal() {
    showNewAnnouncementModal = false;
    newAnnouncement = { title: '', display_date: '', content: '', category: 'general' };
    removeSelectedImage();
  }
  function handleImageSelection(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    selectedImageFile = file || null;
    if (imagePreviewUrl) URL.revokeObjectURL(imagePreviewUrl);
    imagePreviewUrl = file ? URL.createObjectURL(file) : null;
  }
  function removeSelectedImage() {
    selectedImageFile = null;
    if (imagePreviewUrl) URL.revokeObjectURL(imagePreviewUrl);
    imagePreviewUrl = null;
    const uploader = document.getElementById('imageUpload') as HTMLInputElement | null;
    if (uploader) uploader.value = '';
  }
  function handleEditImageSelection(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    editImageFile = file || null;
    if (editImagePreviewUrl) URL.revokeObjectURL(editImagePreviewUrl);
    editImagePreviewUrl = file ? URL.createObjectURL(file) : null;
  }
  function removeEditImage() {
    editImageFile = null;
    if (editImagePreviewUrl) URL.revokeObjectURL(editImagePreviewUrl);
    editImagePreviewUrl = null;
    const uploader = document.getElementById('editImageUpload') as HTMLInputElement | null;
    if (uploader) uploader.value = '';
  }
  async function saveAnnouncementUpdate(announcementId: number, updates: { [key: string]: any }, imageFile: File | null = null) {
    if (isProcessing) {
      alert('An operation is already in progress. Please wait.');
      return;
    }
    isProcessing = true;
    
    const formData = new FormData();
    formData.append('id', announcementId.toString());
    for (const key in updates) {
      if (updates[key] !== null && updates[key] !== undefined) {
        formData.append(key, updates[key]);
      }
    }
    if (imageFile) formData.append('image', imageFile);
    
    try {
      const response = await fetch('http://localhost/back-ends/update_announcement.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || `Server responded with status ${response.status}`);
      }
      alert(result.message || 'Update successful!');
      cancelEdit();
      await invalidateAll();
    } catch (error) {
      console.error('Update Error:', error);
      alert(`Update failed: ${(error as Error).message}`);
    } finally {
      isProcessing = false;
    }
  }
  function saveTitle() {
    if (!selectedAnnouncementId || !tempTitle.trim() || !currentAnnouncement) return;
    const fullUpdatePayload = {
      title: tempTitle,
      content: currentAnnouncement.content ?? '',
      display_date: currentAnnouncement.display_date ?? '',
      category: currentAnnouncement.category,
    };
    saveAnnouncementUpdate(selectedAnnouncementId, fullUpdatePayload);
  }
  function saveDate() {
    if (!selectedAnnouncementId || !tempDate.trim() || !currentAnnouncement) return;
    const fullUpdatePayload = {
      title: currentAnnouncement.title,
      content: currentAnnouncement.content ?? '',
      display_date: tempDate,
      category: currentAnnouncement.category,
    };
    saveAnnouncementUpdate(selectedAnnouncementId, fullUpdatePayload);
  }
  function saveContentWithImage() {
    if (!selectedAnnouncementId || !tempContent.trim() || !currentAnnouncement) return;
    const fullUpdatePayload = {
      title: currentAnnouncement.title,
      content: tempContent,
      display_date: currentAnnouncement.display_date ?? '',
      category: currentAnnouncement.category,
    };
    saveAnnouncementUpdate(selectedAnnouncementId, fullUpdatePayload, editImageFile);
  }
  async function removeAnnouncementImage(announcementId: number) {
    if (!currentAnnouncement) return;
    if (!confirm('Are you sure you want to permanently remove the image from this announcement?')) {
      return;
    }
    const fullUpdatePayload = {
      title: currentAnnouncement.title,
      content: currentAnnouncement.content ?? '',
      display_date: currentAnnouncement.display_date ?? '',
      category: currentAnnouncement.category,
      remove_image: 'true'
    };
    await saveAnnouncementUpdate(announcementId, fullUpdatePayload);
  }
  function getCategoryIcon(category: string) {
    const icons: { [key: string]: string } = { academic: '🎓', event: '📅', maintenance: '🔧', general: '📢' };
    return icons[category] || '📢';
  }
  function goToNextAnnouncement() {
    if (hasNextAnnouncement) {
      currentAnnouncementIndex++;
      cancelEdit();
    }
  }
  function goToPreviousAnnouncement() {
    if (hasPreviousAnnouncement) {
      currentAnnouncementIndex--;
      cancelEdit();
    }
  }
  function goToAnnouncementIndex(index: number) {
    if (index >= 0 && index < announcements.length) {
      currentAnnouncementIndex = index;
      cancelEdit();
    }
  }
  function handleKeydown(event: KeyboardEvent) {
    if (isEditingTitle || isEditingDate || isEditingContent) return;
    if (event.key === 'ArrowLeft') goToPreviousAnnouncement();
    if (event.key === 'ArrowRight') goToNextAnnouncement();
  }
  onMount(() => {
    visible = true;
    checkAdminStatus();
    startCookieCheck();
    loaded = true;
    if (browser) document.addEventListener('keydown', handleKeydown);
  });
  $effect(() => {
    if (form?.success) {
      if (browser) {
        setTimeout(() => alert(form.message || 'Action successful!'), 100);
      }
      if ('action' in form && form.action === 'createAnnouncement') {
        closeNewAnnouncementModal();
      }
    }
    if (form?.error && browser) {
      alert(form.error);
    }
  });
  onDestroy(() => {
    stopCookieCheck();
    if (browser) document.removeEventListener('keydown', handleKeydown);
  });
</script>

<svelte:head>
  <title>Announcements - New Cabalan National High School</title>
</svelte:head>

<div class="relative min-h-screen font-sans">
  <div class="relative z-10 flex min-h-screen flex-col">
    <main class="flex flex-grow flex-col items-center">
      {#if visible}
        <div class="flex flex-col items-center justify-center text-center gap-2 mt-4 md:mt-6 mb-4" in:fade={{ duration: 1000 }}>
          <div class="flex justify-center">
            <img src="/ncnhs-logo-figma.png" alt="School Logo" class="logo" />
            <style>.logo { width: 200px; height: auto; margin-bottom: 20px; margin-top: -10px; display: block; }</style>
          </div>
        </div>
      {/if}
      
      {#if visible}
        <div class="w-full max-w-6xl mx-auto mb-8 flex flex-col px-4 sm:px-6 lg:px-8" in:fly={{ y: 100, duration: 800, delay: 600 }}>
          <div class="rounded-t-2xl bg-gradient-to-r from-green-600 to-green-700 text-white py-3 md:py-4 shadow-lg">
            <div class="flex justify-between items-center px-3 md:px-6 gap-2">
              <div class="flex-1 min-w-0"><h2 class="text-sm sm:text-base md:text-xl lg:text-2xl xl:text-3xl font-bold truncate">SCHOOL ANNOUNCEMENTS</h2></div>
              <div class="flex-shrink-0">
                {#if isAdminLoggedIn}
                  <button on:click={openNewAnnouncementModal} disabled={isProcessing} class="bg-white/20 hover:bg-white/30 text-white px-2 py-1 md:px-3 md:py-1 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-1 whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <span class="hidden sm:inline">Add</span>
                  </button>
                {/if}
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-green-600 to-green-700 px-2 md:px-3 pt-2 md:pt-3 pb-3 md:pb-5 shadow-2xl min-h-[70vh] rounded-b-xl">
            <div class="bg-white p-3 md:p-4 lg:p-6 rounded-xl h-full flex flex-col">
              <div class="flex justify-between items-center mb-4 md:mb-6 flex-shrink-0">
                <div class="w-8 md:w-16"></div>
                <h3 class="text-lg md:text-xl lg:text-2xl font-semibold text-green-800 text-center">Latest Updates</h3>
                <div class="text-xs md:text-sm text-green-600 font-medium">{announcements.length > 0 ? `${currentAnnouncementIndex + 1} of ${announcements.length}` : '0 announcements'}</div>
              </div>
              
              <div class="flex-1 flex flex-col">
                {#if !loaded}
                  <div class="flex items-center justify-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div></div>
                {:else if isProcessing && !isEditingTitle && !isEditingDate && !isEditingContent}
                  <div class="flex items-center justify-center py-12 h-full"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div><p class="ml-4 text-gray-600">Processing...</p></div>
                {:else if announcements.length === 0}
                  <div class="text-center py-12 flex flex-col items-center justify-center h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3v6m0 0l-3-3m3 3l3-3" /></svg>
                    <p class="text-gray-500 text-lg">No announcements available.</p>
                  </div>
                {:else}
                  <div class="flex-1 flex flex-col">
                    <div class="flex justify-between items-center mb-4 gap-2">
                      <button on:click={goToPreviousAnnouncement} disabled={!hasPreviousAnnouncement || isProcessing} class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg><span class="hidden sm:inline">Prev</span></button>
                      <div class="flex items-center gap-2">
                        {#each announcements as _, index}
                          <button on:click={() => goToAnnouncementIndex(index)} disabled={isProcessing} class="w-3 h-3 rounded-full transition-colors {index === currentAnnouncementIndex ? 'bg-green-600' : 'bg-gray-300 hover:bg-gray-400'} disabled:opacity-50"></button>
                        {/each}
                      </div>
                      <button on:click={goToNextAnnouncement} disabled={!hasNextAnnouncement || isProcessing} class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"><span class="hidden sm:inline">Next</span><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></button>
                    </div>

                    {#if currentAnnouncement}
                      <div class="bg-gray-50 rounded-xl p-4 lg:p-6 border flex-1 flex flex-col" in:fly={{ x: 50, duration: 300 }}>
                        <div class="flex justify-between items-start mb-4">
                          <div class="flex-1 mr-4">
                            <div class="flex items-center gap-2 mb-2">
                              <span class="text-xl">{getCategoryIcon(currentAnnouncement.category)}</span>
                              {#if isEditingTitle && selectedAnnouncementId === currentAnnouncement.id}
                                <input bind:value={tempTitle} on:keydown={(e) => { if (e.key === 'Enter') saveTitle(); if (e.key === 'Escape') cancelEdit(); }} class="text-xl font-bold text-green-800 bg-white border border-green-300 rounded px-2 py-1 flex-1"/>
                              {:else}
                                <h3 class="text-xl font-bold text-green-800 flex items-center">
                                  {currentAnnouncement.title}
                                  {#if isAdminLoggedIn} <button on:click={() => startEditingTitle(currentAnnouncement.id)} disabled={isProcessing} class="ml-2 text-blue-600 hover:text-blue-800 disabled:opacity-50">✏️</button>{/if}
                                </h3>
                              {/if}
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                              {#if isEditingDate && selectedAnnouncementId === currentAnnouncement.id}
                                <input type="date" bind:value={tempDate} on:keydown={(e) => { if (e.key === 'Enter') saveDate(); if (e.key === 'Escape') cancelEdit(); }} class="bg-white border border-green-300 rounded px-2 py-1"/>
                              {:else}
                                <span>{currentAnnouncement.display_date ?? 'No Date'}</span>
                                {#if isAdminLoggedIn} <button on:click={() => startEditingDate(currentAnnouncement.id)} disabled={isProcessing} class="text-blue-600 hover:text-blue-800 disabled:opacity-50">✏️</button>{/if}
                              {/if}
                            </div>
                          </div>
                          {#if isAdminLoggedIn}
                            <form method="POST" action="?/deleteAnnouncement" use:enhance={({ cancel }) => {
                                if (!confirm('Are you sure you want to delete this announcement?')) { cancel(); return; }
                                isProcessing = true;
                                return async ({ result }) => {
                                  if (result.type === 'success') { alert('Deleted successfully!'); currentAnnouncementIndex = 0; } 
                                  else if (result.type === 'failure') { alert(result.data?.error || 'Failed to delete.'); }
                                  await invalidateAll();
                                  isProcessing = false;
                                };
                              }}>
                              <input type="hidden" name="id" value={currentAnnouncement.id} />
                              <button type="submit" disabled={isProcessing} class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </form>
                          {/if}
                        </div>
                        
                        <div class="flex-grow">
                          {#if isEditingContent && selectedAnnouncementId === currentAnnouncement.id}
                            <div class="space-y-4">
                              <textarea bind:value={tempContent} rows="8" class="w-full text-gray-700 leading-relaxed border rounded-md p-2 prose prose-sm max-w-none"></textarea>
                              
                              <!-- --- THE FIX IS HERE --- -->
                              <div>
                                <label for="editImageUpload" class="block text-sm font-medium text-gray-700">Change Image (optional):</label>
                                <input type="file" id="editImageUpload" on:change={handleEditImageSelection} accept="image/*" class="mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                                
                                <!-- Show PREVIEW of NEW image -->
                                {#if editImagePreviewUrl}
                                  <div class="mt-2 relative inline-block">
                                    <p class="text-xs text-gray-500 mb-1">New image preview:</p>
                                    <img src={editImagePreviewUrl} alt="New preview" class="max-w-xs h-auto rounded shadow" />
                                    <button on:click={removeEditImage} class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 leading-none text-xs" aria-label="Remove new image">&#x2715;</button>
                                  </div>
                                <!-- Show CURRENT image if no new one is selected -->
                                {:else if currentAnnouncement.attachment}
                                    <div class="mt-2 relative inline-block">
                                        <p class="text-xs text-gray-500 mb-1">Current image:</p>
                                        <img src="{imageBaseUrl}{currentAnnouncement.attachment}" alt="Current Attachment" class="max-w-xs h-auto rounded-lg shadow-md"/>
                                        <button on:click={() => removeAnnouncementImage(currentAnnouncement.id)} class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full p-1.5 leading-none" title="Remove current image">&#x2715;</button>
                                    </div>
                                {/if}
                              </div>
                              <!-- --- END OF FIX --- -->

                              <div class="flex gap-2">
                                <button on:click={saveContentWithImage} disabled={isProcessing} class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">Save</button>
                                <button on:click={cancelEdit} class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">Cancel</button>
                              </div>
                            </div>
                          {:else}
                            <div class="text-gray-700 leading-relaxed prose prose-sm max-w-none">
                              {@html currentAnnouncement.content ?? ''}
                              {#if isAdminLoggedIn} <button on:click={() => startEditingContent(currentAnnouncement.id)} disabled={isProcessing} class="ml-2 text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">✏️</button>{/if}
                            </div>
                          {/if}
                        </div>

                        {#if !isEditingContent && currentAnnouncement.attachment}
                          <div class="mt-4 relative group">
                            <img src="{imageBaseUrl}{currentAnnouncement.attachment}" alt="Attachment" class="max-w-full h-auto rounded-lg shadow-md"/>
                            {#if isAdminLoggedIn}
                              <button on:click={() => removeAnnouncementImage(currentAnnouncement.id)} disabled={isProcessing} class="absolute top-2 right-2 bg-red-600/80 text-white rounded-full p-1.5 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-sm hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-110 active:scale-95" title="Remove Image">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                              </button>
                            {/if}
                          </div>
                        {/if}
                      </div>
                    {/if}
                  </div>
                {/if}
              </div>
            </div>
          </div>
        </div>
      {/if}
    </main>
  </div>
</div>

{#if showNewAnnouncementModal}
  <div class="fixed inset-0 bg-black/50 flex items-start justify-center p-10 z-50 pt-20" on:click={closeNewAnnouncementModal}>
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" on:click|stopPropagation>
      <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 flex justify-between items-center"><h3 class="text-xl font-bold">Create New Announcement</h3><button on:click={closeNewAnnouncementModal} class="p-1"><svg class="w-6 h-6" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button></div>
      <form method="POST" action="?/createAnnouncement" enctype="multipart/form-data" class="p-6 space-y-4" use:enhance>
         <div class="pt-4 border-t border-gray-200">
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="createEvent" bind:checked={createEvent} class="h-5 w-5 rounded border-gray-300 text-green-600 focus:ring-green-500" />
            <span class="font-medium text-gray-700">Also create a calendar event?</span>
          </label>
        </div>

        {#if createEvent}
          <div class="p-4 bg-green-50 border border-green-200 rounded-lg space-y-3" transition:slide>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-1">
                <label for="eventDate" class="block text-xs font-medium text-gray-600">Event Date *</label>
                <input type="date" name="eventDate" id="eventDate" bind:value={newAnnouncement.display_date} required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
              </div>
              <div class="sm:col-span-1">
                <label for="eventTimeStart" class="block text-xs font-medium text-gray-600">Start Time *</label>
                <input type="time" name="eventTimeStart" id="eventTimeStart" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
              </div>
              <div class="sm:col-span-1">
                <label for="eventTimeEnd" class="block text-xs font-medium text-gray-600">End Time *</label>
                <input type="time" name="eventTimeEnd" id="eventTimeEnd" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
              </div>
            </div>
            <div>
              <label for="eventLocation" class="block text-xs font-medium text-gray-600">Location *</label>
              <input type="text" name="eventLocation" id="eventLocation" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" placeholder="e.g., School Gymnasium">
            </div>
          </div>
        {/if}
        <div><label for="title" class="block text-sm font-medium text-gray-700">Title</label><input type="text" name="title" id="title" bind:value={newAnnouncement.title} required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></div>
        <div><label for="displayDate" class="block text-sm font-medium text-gray-700">Display Date</label><input type="date" name="displayDate" id="displayDate" bind:value={newAnnouncement.display_date} required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></div>
        <div><label for="category" class="block text-sm font-medium text-gray-700">Category</label><select name="category" id="category" bind:value={newAnnouncement.category} class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"><option value="general">📢 General</option><option value="academic">🎓 Academic</option><option value="event">📅 Event</option><option value="maintenance">🔧 Maintenance</option></select></div>
        <div><label for="content" class="block text-sm font-medium text-gray-700">Content</label><textarea name="content" id="content" rows="6" bind:value={newAnnouncement.content} required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Attachment Image (Optional)</label><div class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"><div class="space-y-1 text-center"><svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg><div class="flex text-sm text-gray-600"><label for="imageUpload" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500"><span>Upload a file</span><input id="imageUpload" name="image" type="file" class="sr-only" on:change={handleImageSelection} accept="image/png, image/jpeg, image/gif, image/webp"></label><p class="pl-1">or drag and drop</p></div><p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p></div></div></div>
        {#if imagePreviewUrl}<div class="text-center"><p class="text-sm font-medium text-gray-700">Image Preview:</p><div class="mt-2 relative inline-block"><img src={imagePreviewUrl} alt="Image Preview" class="h-48 w-auto rounded-lg shadow-md"><button type="button" on:click={removeSelectedImage} class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 leading-none text-xs" aria-label="Remove image">&#x2715;</button></div></div>{/if}
        <div class="flex justify-end gap-3 pt-4 border-t"><button type="button" on:click={closeNewAnnouncementModal} class="px-4 py-2 text-gray-600 bg-gray-100 border rounded-lg hover:bg-gray-200">Cancel</button><button type="submit" disabled={isProcessing} class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">Create Announcement</button></div>
      </form>
    </div>
  </div>
{/if}