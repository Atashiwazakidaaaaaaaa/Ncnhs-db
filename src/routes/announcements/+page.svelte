<script lang="ts">
  import { fly, fade } from 'svelte/transition';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import { enhance } from '$app/forms';
  import type { PageData } from './$types';
  import type { Announcement } from '$lib/server/db/schema';

  export let data: PageData;

  // Get announcements from server data with proper typing
  $: announcements = data.announcements || [];

  let visible = false;
  let errorMessage: string | null = null;
  let selectedAnnouncementId: number | undefined;
  let isEditingTitle = false;
  let isEditingDate = false;
  let isEditingContent = false;
  let tempTitle = '';
  let tempDate = '';
  let tempContent = '';

  // Admin authentication state
  let isAdminLoggedIn = false;
  let cookieCheckInterval: NodeJS.Timeout | null = null;

  // Loading state
  let isLoading = false;

  // Pagination state
  let currentAnnouncementIndex = 0;
  $: currentAnnouncement = announcements[currentAnnouncementIndex];
  $: hasNextAnnouncement = currentAnnouncementIndex < announcements.length - 1;
  $: hasPreviousAnnouncement = currentAnnouncementIndex > 0;

  // New announcement state
  let showNewAnnouncementModal = false;
  let newAnnouncement = {
    title: '',
    display_date: '',
    content: '',
    category: 'general'
  };
  let selectedImageFile: File | null = null;
  let imagePreviewUrl: string | null = null;
  let editImageFile: File | null = null;
  let editImagePreviewUrl: string | null = null;

  // Function to check admin login status from cookies
  function checkAdminStatus() {
    if (browser) {
      const authCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('auth='));
      const newAdminStatus = !!authCookie;
      
      if (newAdminStatus !== isAdminLoggedIn) {
        isAdminLoggedIn = newAdminStatus;
        console.log('Admin status changed:', isAdminLoggedIn);
      }
    }
  }

  // Start checking for cookie changes periodically
  function startCookieCheck() {
    if (browser && !cookieCheckInterval) {
      cookieCheckInterval = setInterval(checkAdminStatus, 500);
    }
  }

  // Stop checking for cookie changes
  function stopCookieCheck() {
    if (cookieCheckInterval) {
      clearInterval(cookieCheckInterval);
      cookieCheckInterval = null;
    }
  }

  function startEditingTitle(announcementId: number) {
    const announcement = announcements.find((a: Announcement) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempTitle = announcement.title;
      isEditingTitle = true;
    }
  }

  function startEditingDate(announcementId: number) {
    const announcement = announcements.find((a: Announcement) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempDate = announcement.display_date;
      isEditingDate = true;
    }
  }

  function startEditingContent(announcementId: number) {
    const announcement = announcements.find((a: Announcement) => a.id === announcementId);
    if (announcement) {
      selectedAnnouncementId = announcementId;
      tempContent = announcement.content;
      isEditingContent = true;
    }
  }

  function cancelEdit() {
    isEditingTitle = false;
    isEditingDate = false;
    isEditingContent = false;
    selectedAnnouncementId = undefined;
    tempTitle = '';
    tempDate = '';
    tempContent = '';
    editImageFile = null;
    editImagePreviewUrl = null;
  }

  function openNewAnnouncementModal() {
    showNewAnnouncementModal = true;
    newAnnouncement = { 
      title: '', 
      display_date: '', 
      content: '',
      category: 'general'
    };
    selectedImageFile = null;
    imagePreviewUrl = null;
  }

  function closeNewAnnouncementModal() {
    showNewAnnouncementModal = false;
    newAnnouncement = { 
      title: '', 
      display_date: '', 
      content: '',
      category: 'general'
    };
    selectedImageFile = null;
    imagePreviewUrl = null;
  }

  function handleImageSelection(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
      selectedImageFile = file;
      
      // Create preview URL
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreviewUrl = e.target?.result as string;
      };
      reader.readAsDataURL(file);
    } else {
      selectedImageFile = null;
      imagePreviewUrl = null;
    }
  }

  function removeSelectedImage() {
    selectedImageFile = null;
    imagePreviewUrl = null;
    // Reset the file input
    const fileInput = document.getElementById('imageUpload') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  }

  function handleEditImageSelection(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
      editImageFile = file;
      
      // Create preview URL
      const reader = new FileReader();
      reader.onload = (e) => {
        editImagePreviewUrl = e.target?.result as string;
      };
      reader.readAsDataURL(file);
    } else {
      editImageFile = null;
      editImagePreviewUrl = null;
    }
  }

  function removeEditImage() {
    editImageFile = null;
    editImagePreviewUrl = null;
    // Reset the file input
    const fileInput = document.getElementById('editImageUpload') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  }

  function getCategoryIcon(category: string) {
    switch (category) {
      case 'academic': return '🎓';
      case 'event': return '📅';
      case 'maintenance': return '🔧';
      case 'general': return '📢';
      default: return '📢';
    }
  }

  // Navigation functions
  function goToNextAnnouncement() {
    if (hasNextAnnouncement) {
      currentAnnouncementIndex++;
      cancelEdit(); // Cancel any ongoing edits when navigating
    }
  }

  function goToPreviousAnnouncement() {
    if (hasPreviousAnnouncement) {
      currentAnnouncementIndex--;
      cancelEdit(); // Cancel any ongoing edits when navigating
    }
  }

  function goToAnnouncementIndex(index: number) {
    if (index >= 0 && index < announcements.length) {
      currentAnnouncementIndex = index;
      cancelEdit(); // Cancel any ongoing edits when navigating
    }
  }

  // Handle keyboard navigation
  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'ArrowLeft') {
      goToPreviousAnnouncement();
    } else if (event.key === 'ArrowRight') {
      goToNextAnnouncement();
    }
  }

  onMount(() => {
    visible = true;
    checkAdminStatus();
    startCookieCheck();
    
    // Add keyboard event listener for navigation
    if (browser) {
      document.addEventListener('keydown', handleKeydown);
    }
  });

  onDestroy(() => {
    stopCookieCheck();
    
    // Remove keyboard event listener
    if (browser) {
      document.removeEventListener('keydown', handleKeydown);
    }
  });
</script>

<svelte:head>
  <title>Announcements - New Cabalan National High School</title>
</svelte:head>

<!-- Main container with background image -->
<div class="relative min-h-screen bg-cover bg-center font-sans" style="background-image: url('/ncnhs.jpg'); background-attachment: scroll;">
  <!-- Modern Gradient Overlay -->
  <div class="absolute inset-0 bg-gradient-to-b from-green-900/40 via-green-800/20 to-yellow-500/30 mix-blend-overlay"></div>

  <!-- Page Content -->
  <div class="relative z-10 flex min-h-screen flex-col">
    
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center">
      <!-- School Logo -->
      {#if visible}
        <div class="flex flex-col items-center justify-center text-center gap-2 mt-6 mb-4" in:fade={{ duration: 1000 }}>
          <!-- Logo -->
          <div class="h-32 w-32 md:h-40 md:w-40">
            <img src="/logo.png" alt="School Logo" class="h-full w-full object-contain drop-shadow-lg" />
          </div>
        </div>
      {/if}
      
      <!-- Modern Announcements Container -->
      {#if visible}
        <div class="w-full max-w-6xl mx-auto mb-8 flex flex-col" in:fly={{ y: 100, duration: 800, delay: 600 }}>
          <!-- Header Section -->
          <div class="rounded-t-2xl bg-gradient-to-r from-green-600 to-green-700 text-white py-4 shadow-lg">
            <div class="flex justify-between items-center px-6">
              <!-- Left side: Empty spacer -->
              <div class="flex-1"></div>
              
              <!-- Center: Title -->
              <div class="flex items-center gap-2">
                <h2 class="text-xl md:text-2xl lg:text-3xl font-bold md:pl-3 pr-4 md:pr-0">SCHOOL ANNOUNCEMENTS</h2>
              </div>
              
              <!-- Right side: Admin controls -->
              <div class="flex-1 flex justify-end ml-2 sm:ml-4 md:ml-0">
                {#if isAdminLoggedIn}
                  <div class="flex items-center gap-2">
                    <button
                      on:click={openNewAnnouncementModal}
                      class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                      Add Announcement
                    </button>
                  </div>
                {/if}
              </div>
            </div>
          </div>
          
          <!-- Main Content Box -->
          <div class="bg-gradient-to-r from-green-600 to-green-700 px-3 pt-3 pb-5 shadow-2xl min-h-[70vh] rounded-b-xl">
            <div class="bg-white p-4 md:p-6 rounded-xl h-full flex flex-col">
              
              <!-- Announcements Header -->
              <div class="flex justify-between items-center mb-6 flex-shrink-0">
                <div class="w-16"></div> <!-- Spacer for balance -->
                <h3 class="text-lg md:text-xl lg:text-2xl font-semibold text-green-800">Latest Updates</h3>
                <div class="text-sm text-green-600 font-medium">
                  {#if announcements.length > 0}
                    {currentAnnouncementIndex + 1} of {announcements.length}
                  {:else}
                    0 announcements
                  {/if}
                </div>
              </div>
              
              <!-- Scrollable Content Area -->
              <div class="flex-1 flex flex-col">
                {#if isLoading}
                  <div class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
                  </div>
                {:else if errorMessage}
                  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {errorMessage}
                  </div>
                {:else if announcements.length === 0}
                  <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3v6m0 0l-3-3m3 3l3-3" />
                    </svg>
                    <p class="text-gray-500 text-lg">No announcements available at this time.</p>
                  </div>
                {:else}
                  <!-- Single Announcement Display -->
                  <div class="flex-1 flex flex-col">
                    <!-- Navigation Controls -->
                    <div class="flex justify-between items-center mb-4">
                      <!-- Previous Button -->
                      <button
                        on:click={goToPreviousAnnouncement}
                        disabled={!hasPreviousAnnouncement}
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        title="Previous announcement"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                      </button>

                      <!-- Page Indicators -->
                      {#if announcements.length > 1}
                        <div class="flex items-center gap-2">
                          {#each announcements as _, index}
                            <button
                              on:click={() => goToAnnouncementIndex(index)}
                              class="w-3 h-3 rounded-full transition-colors {index === currentAnnouncementIndex ? 'bg-green-600' : 'bg-gray-300 hover:bg-gray-400'}"
                              title="Go to announcement {index + 1}"
                            ></button>
                          {/each}
                        </div>
                      {/if}

                      <!-- Next Button -->
                      <button
                        on:click={goToNextAnnouncement}
                        disabled={!hasNextAnnouncement}
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        title="Next announcement"
                      >
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                      </button>
                    </div>

                    <!-- Current Announcement -->
                    {#if currentAnnouncement}
                      <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200 flex-1" in:fly={{ x: 50, duration: 300 }}>
                        <!-- Announcement Header -->
                        <div class="flex justify-between items-start mb-4">
                          <div class="flex-1">
                            <!-- Title with Category Icon -->
                            <div class="flex items-center gap-2 mb-2">
                              <span class="text-xl">{getCategoryIcon(currentAnnouncement.category)}</span>
                              {#if isEditingTitle && selectedAnnouncementId === currentAnnouncement.id}
                                <form method="POST" action="?/updateAnnouncement" use:enhance>
                                  <input type="hidden" name="id" value={currentAnnouncement.id} />
                                  <div class="flex items-center gap-2 flex-1">
                                    <input 
                                      name="title"
                                      bind:value={tempTitle}
                                      class="text-xl font-bold text-green-800 bg-white border border-green-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-green-500"
                                      on:keydown={(e) => {
                                        if (e.key === 'Escape') cancelEdit();
                                      }}
                                      autofocus
                                    />
                                    <input type="hidden" name="content" value={currentAnnouncement.content} />
                                    <input type="hidden" name="displayDate" value={currentAnnouncement.display_date} />
                                    <input type="hidden" name="category" value={currentAnnouncement.category} />
                                    <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Save">
                                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                      </svg>
                                    </button>
                                    <button type="button" on:click={cancelEdit} class="text-red-600 hover:text-red-800 p-1" title="Cancel">
                                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                      </svg>
                                    </button>
                                  </div>
                                </form>
                              {:else}
                                <h3 class="text-xl font-bold text-green-800 flex-1">
                                  {currentAnnouncement.title}
                                  {#if isAdminLoggedIn}
                                    <button 
                                      on:click={() => startEditingTitle(currentAnnouncement.id)} 
                                      class="ml-2 text-sm text-blue-600 hover:text-blue-800 opacity-70 hover:opacity-100"
                                      title="Edit title"
                                    >
                                      ✏️
                                    </button>
                                  {/if}
                                </h3>
                              {/if}
                            </div>
                            
                            <!-- Date -->
                            {#if isEditingDate && selectedAnnouncementId === currentAnnouncement.id}
                              <form method="POST" action="?/updateAnnouncement" use:enhance>
                                <input type="hidden" name="id" value={currentAnnouncement.id} />
                                <input type="hidden" name="title" value={currentAnnouncement.title} />
                                <input type="hidden" name="content" value={currentAnnouncement.content} />
                                <input type="hidden" name="category" value={currentAnnouncement.category} />
                                <div class="flex items-center gap-2">
                                  <input 
                                    name="displayDate"
                                    bind:value={tempDate}
                                    class="text-sm text-gray-600 bg-white border border-green-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    on:keydown={(e) => {
                                      if (e.key === 'Escape') cancelEdit();
                                    }}
                                    autofocus
                                  />
                                  <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Save">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                  </button>
                                  <button type="button" on:click={cancelEdit} class="text-red-600 hover:text-red-800 p-1" title="Cancel">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                  </button>
                                </div>
                              </form>
                            {:else}
                              <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V5a2 2 0 012-2v0a2 2 0 012 2v2m-6 0h6m-6 0l-1 12h8l-1-12" />
                                </svg>
                                <span>{currentAnnouncement.display_date}</span>
                                {#if isAdminLoggedIn}
                                  <button 
                                    on:click={() => startEditingDate(currentAnnouncement.id)} 
                                    class="text-xs text-blue-600 hover:text-blue-800 opacity-70 hover:opacity-100"
                                    title="Edit date"
                                  >
                                    ✏️
                                  </button>
                                {/if}
                              </div>
                            {/if}

                            <!-- Category Badge -->
                            <div class="mb-3">
                              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {currentAnnouncement.category.charAt(0).toUpperCase() + currentAnnouncement.category.slice(1)}
                              </span>
                            </div>
                          </div>
                          
                          {#if isAdminLoggedIn}
                            <div class="flex items-center gap-2">
                              <form method="POST" action="?/deleteAnnouncement" use:enhance>
                                <input type="hidden" name="id" value={currentAnnouncement.id} />
                                <button 
                                  type="submit"
                                  class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100 transition-colors"
                                  title="Delete announcement"
                                  on:click={(e) => {
                                    if (!confirm('Are you sure you want to delete this announcement?')) {
                                      e.preventDefault();
                                    }
                                  }}
                                >
                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                  </svg>
                                </button>
                              </form>
                            </div>
                          {/if}
                        </div>
                        
                        <!-- Content -->
                        {#if isEditingContent && selectedAnnouncementId === currentAnnouncement.id}
                          <form method="POST" action="?/updateAnnouncement" enctype="multipart/form-data" use:enhance={() => {
                            return async ({ formData, update }) => {
                              // Add the image file to the form data if selected
                              if (editImageFile) {
                                formData.append('image', editImageFile);
                              }
                              
                              // Call the default update function
                              await update();
                              cancelEdit();
                            };
                          }}>
                            <input type="hidden" name="id" value={currentAnnouncement.id} />
                            <input type="hidden" name="title" value={currentAnnouncement.title} />
                            <input type="hidden" name="displayDate" value={currentAnnouncement.display_date} />
                            <input type="hidden" name="category" value={currentAnnouncement.category} />
                            <div class="space-y-4">
                              <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                                <textarea 
                                  name="content"
                                  bind:value={tempContent}
                                  class="w-full h-32 p-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 resize-vertical"
                                  placeholder="Enter announcement content..."
                                ></textarea>
                              </div>
                              
                              <div>
                                <label for="editImageUpload" class="block text-sm font-medium text-gray-700 mb-2">Update Image (Optional)</label>
                                <input
                                  id="editImageUpload"
                                  type="file"
                                  accept="image/*"
                                  on:change={handleEditImageSelection}
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                />
                                {#if editImagePreviewUrl}
                                  <div class="mt-3 relative">
                                    <img src={editImagePreviewUrl} alt="Preview" class="max-w-full h-40 object-cover rounded-lg border" />
                                    <button
                                      type="button"
                                      on:click={removeEditImage}
                                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                      title="Remove image"
                                    >
                                      ×
                                    </button>
                                  </div>
                                {:else if currentAnnouncement.attachment}
                                  <div class="mt-3">
                                    <p class="text-sm text-gray-600 mb-2">Current image:</p>
                                    <img src={currentAnnouncement.attachment} alt="Current" class="max-w-full h-40 object-cover rounded-lg border" />
                                  </div>
                                {/if}
                              </div>
                              
                              <div class="flex justify-end gap-2">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition-colors">
                                  Save
                                </button>
                                <button type="button" on:click={cancelEdit} class="bg-gray-400 text-white px-3 py-1 rounded hover:bg-gray-500 transition-colors">
                                  Cancel
                                </button>
                              </div>
                            </div>
                          </form>
                        {:else}
                          <div class="text-gray-700 leading-relaxed prose prose-sm max-w-none">
                            {@html currentAnnouncement.content}
                            {#if isAdminLoggedIn}
                              <button 
                                on:click={() => startEditingContent(currentAnnouncement.id)} 
                                class="ml-2 text-sm text-blue-600 hover:text-blue-800 opacity-70 hover:opacity-100"
                                title="Edit content"
                              >
                                ✏️
                              </button>
                            {/if}
                          </div>
                          
                          <!-- Display image if available -->
                          {#if currentAnnouncement.attachment}
                            <div class="mt-4">
                              <img 
                                src={currentAnnouncement.attachment} 
                                alt="Related to {currentAnnouncement.title}" 
                                class="max-w-full h-auto rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200"
                                loading="lazy"
                              />
                            </div>
                          {/if}
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
    
    <!-- Modern Footer -->
    <footer class="bg-green-900/80 text-white py-3 md:py-4 backdrop-blur-md border-t border-green-700/30">
      <div class="container mx-auto text-center text-xs md:text-sm">
        <p>© {new Date().getFullYear()} New Cabalan National High School. All rights reserved.</p>
        <p class="mt-1 md:mt-2 text-yellow-300/80 text-[10px] md:text-xs">Empowering students through education since 1979</p>
      </div>
    </footer>
  </div>
</div>

<!-- New Announcement Modal -->
{#if showNewAnnouncementModal}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 pt-20 z-50" 
       transition:fade={{ duration: 200 }}
       on:click={closeNewAnnouncementModal}
       on:keydown={(e) => e.key === 'Escape' && closeNewAnnouncementModal()}>
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto" 
         transition:fly={{ y: 50, duration: 300 }}
         on:click={(e) => e.stopPropagation()}>
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-t-2xl">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-bold">Create New Announcement</h3>
          <button
            on:click={closeNewAnnouncementModal}
            class="text-white hover:text-green-200 transition-colors p-1"
            aria-label="Close modal"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Modal Body -->
      <form method="POST" action="?/createAnnouncement" enctype="multipart/form-data" use:enhance={() => {
        return async ({ update }) => {
          // Call the default update function and close modal
          await update();
          closeNewAnnouncementModal();
        };
      }}>
        <div class="p-6 space-y-4">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
            <input
              id="title"
              name="title"
              type="text"
              bind:value={newAnnouncement.title}
              placeholder="Enter announcement title"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              required
            />
          </div>
          
          <div>
            <label for="displayDate" class="block text-sm font-medium text-gray-700 mb-2">Event time</label>
            <input
              id="displayDate"
              name="displayDate"
              type="text"
              bind:value={newAnnouncement.display_date}
              placeholder="e.g., August 5th, 2025"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select
              id="category"
              name="category"
              bind:value={newAnnouncement.category}
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option value="general">General</option>
              <option value="academic">Academic</option>
              <option value="event">Event</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>

          <div>
            <label for="imageUpload" class="block text-sm font-medium text-gray-700 mb-2">Image (Optional)</label>
            <input
              id="imageUpload"
              name="image"
              type="file"
              accept="image/*"
              on:change={handleImageSelection}
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
            />
            {#if imagePreviewUrl}
              <div class="mt-3 relative">
                <img src={imagePreviewUrl} alt="Preview" class="max-w-full h-40 object-cover rounded-lg border" />
                <button
                  type="button"
                  on:click={removeSelectedImage}
                  class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                  title="Remove image"
                >
                  ×
                </button>
              </div>
            {/if}
          </div>
          
          <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
            <textarea
              id="content"
              name="content"
              bind:value={newAnnouncement.content}
              placeholder="Enter announcement content (HTML supported)"
              rows="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-vertical"
              required
            ></textarea>
          </div>
          
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              on:click={closeNewAnnouncementModal}
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              Create Announcement
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
{/if}
